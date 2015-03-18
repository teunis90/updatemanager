<?php
	
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as Orm;
use AppBundle\Entity\Host;
use AppBundle\Classes\CurlWrapper;

/**
 * @Orm\Entity
 */
class RepositoryApt extends Repository {
	
	public function processPackages() {
		try {
			$packagesGzUrl = $this->getPackagesGzUrl();	
		} catch (\Exception $e) {
			// TODO: Log exception to file.
			return false;
		}
		
		$tmpfname = $this->downloadPackageGz($packagesGzUrl);
		$numOfPackages = $this->readPackageInfoFromPackageGzFile($tmpfname);
		
		return $numOfPackages;
	}
	
	private function readPackageInfoFromPackageGzFile($uncompressedPackagesFile) {
		$numOfPackages = 0;
		$fh = @fopen($uncompressedPackagesFile, "r");
		$maxlinesizebytes = 1024;
		$linenumber = 1;
	
		if ($fh) {
		    while (($buffer = fgets($fh, $maxlinesizebytes)) !== false) {
			    // If buffer didn't read to end of line (eol), contcat buffer
			    if("\n" != substr($buffer, -1) && !feof($fh)) {
				    $buffer .= $buffer;
				    continue;
			    }
			    // Throw error if buffer does not end with newline or end of file (eof)
			    if("\n" != substr($buffer, -1) && !feof($fh)) {
					throw new \Exception('Line size exceeded ' . $maxlinesizebytes . ' bytes, linenumber: ' . $linenumber . ' in file: ' . $uncompressedPackagesFile);
			    }
			    if(preg_match_all('/^(.+?):\s+(.+)$/', $buffer, $match)) {
				    $package[strtolower($match[1][0])] = $match[2][0];
			    }
			    // Process package at and of block (eob) or end of file (eof)
			    if($buffer == "\n" || feof($fh)) {
				    if(isset($package)) {
					    // Add to packagelist here
						$this->persistPackage($package);
					    unset($package);
						$numOfPackages++;   
				    }
			    }
			    $linenumber++;
		    }
		    fclose($fh);
		}
		$this->flushQueue('Package', ' ON DUPLICATE KEY UPDATE name = name');
		$this->flushQueue('Packageversion', ' ON DUPLICATE KEY UPDATE `runid` = '.$this->getRunId().';');
		unlink($uncompressedPackagesFile);
		
		return $numOfPackages;
	}
	
	private function downloadPackageGz($packagesGzUrl) {
		$tmpBinFilename = tempnam(sys_get_temp_dir(), "rep-bin-");
		// TODO: Implement hash check ($packagesGzUrl->hash) in 'CurlWrapper::downloadToFile'
		$downloadComplete = CurlWrapper::downloadToFile($packagesGzUrl->url, $tmpBinFilename);
		if($downloadComplete) {
			$tmpTxtFilename = tempnam(sys_get_temp_dir(), "rep-txt-");
			$this->uncompress($tmpBinFilename, $tmpTxtFilename);
			unlink($tmpBinFilename);
		}
		
		return $tmpTxtFilename;
	}
	
	// Todo: Maybe to move this function to separate class
	private function uncompress($srcName, $dstName) {
	    $sfp = gzopen($srcName, "rb");
	    $fp = fopen($dstName, "w");
	
	    while (!gzeof($sfp)) {
	        $string = gzread($sfp, 4096);
	        fwrite($fp, $string, strlen($string));
	    }
	    gzclose($sfp);
	    fclose($fp);
	}
	
	private function getPackagesGzUrl() {
		$releaseFile = CurlWrapper::downloadToVar($this->getUrl() . '/dists/' . $this->getBranch() . '/Release');
		$releaseFileLines = explode("\n", $releaseFile);
		
		$i = 0;
		foreach($releaseFileLines as $line) {
			// Find 'Packages.gz' entry, example: ' 2647f1eed177f7bdf20c97875c6da39d  7625173 main/binary-amd64/Packages.gz'
			if(preg_match_all('/^\s*?([a-z0-9]{32})\s+(\d+)\s+('.$this->getComponent().')\/(binary-('.$this->getArchitecture().')\/Packages.gz$)/', $line, $match)) 			{
				$i++;
				$packagesGz = new \stdClass();
				$packagesGz->url = $this->getUrl() . '/dists/' . $this->getBranch() . '/' . $match[3][0] . '/' .$match[4][0];
				$packagesGz->hash = $match[1][0];
			}
		}
		// Throw error if Packages.gz file isn't found
		if($i != 1) {
			throw new \Exception('Could not determine Packages.gz file for repositoryid: ' . $this->getId() );
		}
		return $packagesGz;
	}
	
}
