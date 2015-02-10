<?php
	
namespace AppBundle\Classes;

use AppBundle\Entity\RepositoryApt;
	
class PacketManagerStrategyApt extends PacketManagerStrategy {
	
	public function __construct($host, $doctrine) {
		parent::__construct($host, $doctrine);
	}

	private function getRemoteRepositoriesAsString() {
		$sftp = $this->getHost()->getSftpConnection();
		
		$repositorystring = '';
		$repositorystring .= $sftp->exec('ls /etc/apt/sources.list &>/dev/null && cat /etc/apt/sources.list');
		$repositorystring .= $sftp->exec('ls /etc/apt/sources.list.d/*.list &>/dev/null && cat /etc/apt/sources.list.d/*.list');
		return $repositorystring;
	}
	
	private function getRemotePackagesAsString() {
		$sftp = $this->getHost()->getSftpConnection();
		
		$packagesstring = '';
		$packagesstring .= $sftp->exec('cat /var/lib/dpkg/status | egrep "^Package:|^Version:|^Description:|^Architecture:|Status:|^$"');
		return $packagesstring;
	}
	
	public function getArchitecture() {
		$sftp = $this->getHost()->getSftpConnection();
		
		$architecture = trim( $sftp->exec('dpkg --print-architecture') );
		return $architecture;
	}
	
	public function getRepositoryList() {
		$repositoryArr = explode("\n", $this->getRemoteRepositoriesAsString() );
		$list = array();
		
		foreach($repositoryArr as $r) {
			if(preg_match_all('/^\s?(deb)\s(\S+)\s(\S+)\s(.*)\s?/', $r, $matches)) {
				$componentArr = preg_split('/\s+/', $matches[4][0]);
				foreach($componentArr as $c) {
					$repositoryObj = new RepositoryApt();
					$repositoryObj->setType($matches[1][0]);
					$repositoryObj->setUrl($matches[2][0]);
					$repositoryObj->setBranch($matches[3][0]);
					$repositoryObj->setComponent($c);
					$repositoryObj->setArchitecture( $this->getArchitecture() );
					$list[] = $repositoryObj;
				}
			}
		}
		return $list;
	}
	
	public function getPackagesList() {
		$packagesArr = explode("\n", $this->getRemotePackagesAsString() );
		$packagesList = array();
		
		foreach($packagesArr as $p) {
			    if(preg_match_all('/^(.+?):\s+(.+)$/', $p, $match)) {
				    $package[strtolower($match[1][0])] = $match[2][0];
			    }
			    // Process package at and of block (eob) or end of file (eof)
			    if(isset($package) && $p == "") {
				    // Add to packagelist here
				    $package = RepositoryApt::setVersionFields($package);
					if(isset($package['status']) && $package['status'] == 'install ok installed') {
						$packagesList[] = $package;
					}
				    unset($package);
			    }
		}
		return $packagesList;
	}
}