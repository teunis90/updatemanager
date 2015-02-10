<?php
	
namespace AppBundle\Classes;

use AppBundle\Classes\PdoBulk;
use AppBundle\Classes\PdoBulkSubquery;
	
abstract class PacketManagerStrategy {
	
	private $host = null;
	private $repositoryList = null;
	private $packagesList = null;
	private $doctrine = null;
	
	public function __construct($host, $doctrine) {
		$this->setHost($host);
		$this->setDoctrine($doctrine);
		$this->ensureRepositoryZero();
	}
	
	// Abastract fuction: Should be implemented with a SSH connector and RemoteRepository retriever.
	abstract public function getRepositoryList();
	// Abastract fuction: Should be implemented with a SSH connector and RemotePackages retriever.
	abstract public function getPackagesList();
	// Abstract function: Should return machines default package architecture.
	abstract public function getArchitecture();	
	
	// Save repositories to the database and link the host to them.
	public function saveRepository($repository) {
		$em = $this->getDoctrine()->getManager();
		
		// Check if repository is already in the database, if not persist repository.
		if( !$repositoryObj = $this->getDoctrine()->getRepository('AppBundle:Repository')->findOneBy(
			array('type' => $repository->getType(), 'url' => $repository->getUrl(), 'branch' => $repository->getBranch(), 'component' => $repository->getComponent(), 'architecture' => $repository->getArchitecture() )
		)) {
			$repository->addHost($this->getHost() );
			$em->persist($repository);
		} else {
			// Check if host is already related, if not persist relation in the database.
			$addhost = true;
			foreach($repositoryObj->getHosts() as $h) {
				if($h == $this->getHost()) $addhost = false;
			}
			if($addhost) {
				$repositoryObj->addHost($this->getHost() );
				$em->persist($repositoryObj);
			}
		}
		return $em->flush();
	}
	
	public function addRemoteRepositories() {
		$addRemoteRepositoriesDebugText = '';
		
		foreach($this->getRepositoryList() as $r) {
			$addRemoteRepositoriesDebugText .= " * Found: " . $r->getType()
					. " " . $r->getUrl() . " " . $r->getBranch() . " " . $r->getComponent() . "\n";
			$this->saveRepository($r);
		}
		
		return rtrim($addRemoteRepositoriesDebugText);
	}

	public function clearRemovedRemoteRepositories() {
		$em = $this->getDoctrine()->getManager();
		$clearRemovedRemoteRepositoriesDebugText = '';
		
		foreach($this->getHost()->getRepositories() as $dbRepository) {
			$delete = true;
			foreach($this->getRepositoryList() as $r) {
				if( 
					( $dbRepository->getType() == $r->getType() ) && 
					( $dbRepository->getUrl() == $r->getUrl() ) && 
					( $dbRepository->getBranch() == $r->getBranch() ) && 
					( $dbRepository->getComponent() == $r->getComponent() )
				) {
					$delete = false;
				}
			}
			if($delete) {
				$clearRemovedRemoteRepositoriesDebugText .= " * Unlink relation: " . $dbRepository->getType()
					. " " . $dbRepository->getUrl() . " " . $dbRepository->getBranch() . " " . $dbRepository->getComponent() . "\n";
				$dbRepository->removeHost($this->getHost() );
				$em->flush();
			}
		}
		return rtrim($clearRemovedRemoteRepositoriesDebugText);
	}
	
	public function addRemotePackages($run) {
		$pdoBulk = new PdoBulk($this->getPdo());
		
		$i = 0;
		foreach($this->getPackagesList() as $p) {
			$i++;

			if(! $packageid = $this->getPackageId($p)) {
				$packageEntry['name'] = $p['package'];
				$packageEntry['description'] = $p['description'];
				$packageEntry['architecture'] = $p['architecture'];
				$pdoBulk->persist('Package', $packageEntry);
			}
			// Add `Packageversion` for `Hostpackageversion` relation
			$packageversionEntry['repositoryid'] = 0;
			$packageversionEntry['packageid'] = 
				new PdoBulkSubquery("(SELECT id FROM `Package` WHERE name = '" . $p['package'] . "' AND architecture = '" . $p['architecture'] . "')");
			$packageversionEntry['version'] = $p['version'];
			$packageversionEntry['hexversion'] = $p['hexversion'];
			$packageversionEntry['hexrevision'] = $p['hexrevision'];
			$packageversionEntry['runid'] = $run->getId();
			$pdoBulk->persist('Packageversion', $packageversionEntry);
			
			// Add Hostpackageversion
			$hostpackageversionEntry['hostid'] = $this->getHost()->getId();
			$hostpackageversionEntry['packageversionid'] = 
				new PdoBulkSubquery("SELECT id FROM `Packageversion` WHERE version = '" . $p['version'] . "' AND repositoryid = 0 AND packageid = 
					(SELECT id FROM `Package` WHERE name = '" . $p['package'] . "' AND architecture = '" . $p['architecture'] . "')");
			$hostpackageversionEntry['runid'] = $run->getId();
			$pdoBulk->persist('Hostpackageversion', $hostpackageversionEntry);
		}
		$pdoBulk->flushQueue('Package');
		$pdoBulk->flushQueue('Packageversion', ' ON DUPLICATE KEY UPDATE `runid` = '.$run->getId().';');
		$pdoBulk->flushQueue('Hostpackageversion', ' ON DUPLICATE KEY UPDATE `runid` = '.$run->getId().';');
		
		$addRemotePackagesDebugText = " * Packages found at host: " . $i;
		return rtrim($addRemotePackagesDebugText);
	}
	
	// Return package id, or false if not exists
	public function getPackageId($package) {
		$query = 'SELECT id FROM `Package` WHERE name = :name AND architecture = :architecture';
		$stmt = $this->getPdo()->prepare($query);
		$stmt->bindValue(':name', $package['package'], \PDO::PARAM_STR);
		$stmt->bindValue(':architecture', $package['architecture'], \PDO::PARAM_STR);
		$stmt->execute();
		if($result = $stmt->fetch()) {
			return $result['id'];
		}
		return false;
	}
	
	// Return packageversion id, or false if not exists
	public function getPackageversionId($id, $version) {
		$query = 'SELECT id FROM `Packageversion` WHERE repositoryid IS NULL AND packageid = :packageid AND version = :version;';
		$stmt = $this->getPdo()->prepare($query);
		$stmt->bindValue(':packageid', $id, \PDO::PARAM_INT);
		$stmt->bindValue(':version', $version, \PDO::PARAM_STR);
		$stmt->execute();
		if($result = $stmt->fetch()) {
			return $result['id'];
		}
		return false;
	}
	
	// Ensure there is a repository with id 0 in the database.
	public function ensureRepositoryZero() {
		$query = "INSERT IGNORE INTO `Repository` (`id`, `type`) VALUES (0, 'zero')";
		$stmt = $this->getPdo()->prepare($query);
		$stmt->execute();
		// Workaround due to strange SQL bug
		$query = "UPDATE `Repository` SET `id` = 0 WHERE `type` = 'zero'";
		$stmt = $this->getPdo()->prepare($query);
		$stmt->execute();
	}
		
	public function getHost() {
		return $this->host;
	}

	public function setHost($host) {
		$this->host = $host;
	}
	
	public function getDoctrine() {
		return $this->doctrine;
	}
	
	public function getPdo() {
		return $this->getDoctrine()->getManager()->getConnection();
	}

	public function setDoctrine($doctrine) {
		$this->doctrine = $doctrine;
	}
}