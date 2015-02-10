<?php
	
namespace AppBundle\Classes;

use AppBundle\Entity\Run;
	
class PacketManagerService {
	
	private $packagemanagerList = array('Apt'); // Register extra packagemanagers here.
	private $hostList = null;
	private $clioutput = null;
	private $doctrine = null;
	private $run = null;
	
	public function __construct($hostList, $doctrine, $clioutput = null) {
		$this->setHostList($hostList);
		$this->setClioutput($clioutput);
		$this->setDoctrine($doctrine);
		
		// Start new run and get run->id
		$run = new Run();
		$em = $this->getDoctrine()->getManager();
	    $em->persist($run);
	    $em->flush();
	    $this->setRun($run);
	}
	
	public function executeCron() {
		if($this->getClioutput()) $this->getClioutput()->writeln("<info>Add new repositories to database</info>");
		$this->updateHostRepositories();
		
		if($this->getClioutput()) $this->getClioutput()->writeln("<info>Updating all linked repositories</info>");
		$this->updateActiveRepositories();
		
		if($this->getClioutput()) $this->getClioutput()->writeln("<info>Get installed packages</info>");
		$this->updateHostPackages();

		if($this->getClioutput()) $this->getClioutput()->writeln("<info>Clear removed repositories from database</info>");
		$this->clearRemovedHostRepositories();
	}
	
	// TODO: Create object via Factory?
	private function updateHostRepositories() {
		foreach($this->getHostList() as $host) {
			
			if($this->getClioutput()) $this->getClioutput()->writeln('Hostname: ' . $host->getHostname() );
			foreach($this->getPackagemanagerList() as $pm) {
				
				$class = '\AppBundle\Classes\PacketManagerStrategy'.$pm;
				if(class_exists($class)) {
					$host->setPackageManagerStrategy(new $class($host, $this->getDoctrine()) );
				} else {
					if($this->getClioutput()) $this->getClioutput()->writeln("Class missing: \AppBundle\Classes\PacketManagerStrategy".$pm);						
					continue;
				}
				
				// getRemoteRepositories
				if($addRemoteRepositoriesDebugText = $host->getPackageManagerStrategy()->addRemoteRepositories() ) {
					if($this->getClioutput()) $this->getClioutput()->writeln( $addRemoteRepositoriesDebugText );	
				}
			}
		}
	}
	
	// TODO: Create object via Factory?
	private function clearRemovedHostRepositories() {
		foreach($this->getHostList() as $host) {
			
			if($this->getClioutput()) $this->getClioutput()->writeln('Hostname: ' . $host->getHostname() );
			foreach($this->getPackagemanagerList() as $pm) {
				
				$class = '\AppBundle\Classes\PacketManagerStrategy'.$pm;
				if(class_exists($class)) {
					$host->setPackageManagerStrategy(new $class($host, $this->getDoctrine()) );
				} else {
					if($this->getClioutput()) $this->getClioutput()->writeln("Class missing: \AppBundle\Classes\PacketManagerStrategy".$pm);						
					continue;
				}
				
				// clearRemovedRemoteRepositories
				if($clearRemovedRemoteRepositoriesDebugText = $host->getPackageManagerStrategy()->clearRemovedRemoteRepositories() ) {
					if($this->getClioutput()) $this->getClioutput()->writeln( $clearRemovedRemoteRepositoriesDebugText );
				}
			}
		}
	}
	
	// TODO: Create object via Factory?
	private function updateHostPackages() {
		foreach($this->getHostList() as $host) {
			
			if($this->getClioutput()) $this->getClioutput()->writeln('Hostname: ' . $host->getHostname() );
			foreach($this->getPackagemanagerList() as $pm) {
				
				$class = '\AppBundle\Classes\PacketManagerStrategy'.$pm;
				if(class_exists($class)) {
					$host->setPackageManagerStrategy(new $class($host, $this->getDoctrine()) );
				} else {
					if($this->getClioutput()) $this->getClioutput()->writeln("Class missing: \AppBundle\Classes\PacketManagerStrategy".$pm);						
					continue;
				}
				
				// getRemotePackages
				if($addRemotePackagesDebugText = $host->getPackageManagerStrategy()->addRemotePackages($this->getRun()) ) {
					if($this->getClioutput()) $this->getClioutput()->writeln( $addRemotePackagesDebugText );	
				}
			}
		}
	}
	
	// Download repository update file here, and process it afterwards
	private function updateActiveRepositories() {
		$em = $this->getDoctrine()->getManager();
		
		// Only return active repositories
		$query = $em->createQuery('SELECT r FROM AppBundle:Repository r INNER JOIN r.hosts');
		$repositories = $query->getResult();
		
		foreach($repositories as $r) {
			if($this->getClioutput()) $this->getClioutput()->writeln(' * Update repository: [' . $r->getId() . '] ' . '[url='.$r->getUrl().',branch='.$r->getBranch().',component='.$r->getComponent().',architecture='.$r->getArchitecture().']' );
			$r->setRunId($this->getRun()->getId());
			$r->injectPdo($this->getDoctrine()->getManager()->getConnection());
			$numOfPackages = $r->processPackages();
			
			if($this->getClioutput()) $this->getClioutput()->writeln(" * Total packages found: " . $numOfPackages);
		}
	}
	
	public function getPackagemanagerList() {
		return $this->packagemanagerList;
	}

	public function setPackagemanagerList($packagemanagerList) {
		$this->packagemanagerList = $packagemanagerList;
	}
	
	public function getHostList() {
		return $this->hostList;
	}

	public function setHostList($hostList) {
		$this->hostList = $hostList;
	}
	
	public function getClioutput() {
		return $this->clioutput;
	}

	public function setClioutput($clioutput) {
		$this->clioutput = $clioutput;
	}
	
	public function getDoctrine() {
		return $this->doctrine;
	}

	public function setDoctrine($doctrine) {
		$this->doctrine = $doctrine;
	}
	
	public function getRun() {
		return $this->run;
	}

	public function setRun($run) {
		$this->run = $run;
	}
}