<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use AppBundle\Classes\PdoBulk;
use AppBundle\Classes\PdoBulkSubquery;

/**
 * Repository
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_repository", columns={"type", "url", "branch", "component", "architecture"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="packagemanager", type="string")
 * @ORM\Entity
 * @UniqueEntity({"type", "url", "branch", "component"})
 */
abstract class Repository extends PdoBulk
{
	private $packagemanager;
	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hosts = new \Doctrine\Common\Collections\ArrayCollection();
    }
	
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Host", inversedBy="repositories")
     * @ORM\JoinTable(name="Hostrepositories")
     */
	private $hosts;
	
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=30)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="branch", type="string", length=30)
     */
    private $branch;

    /**
     * @var string
     *
     * @ORM\Column(name="component", type="string", length=255)
     */
    private $component;
    
    /**
     * @var string
     *
     * @ORM\Column(name="architecture", type="string", length=20)
     */
    private $architecture;
    
	// No ORM Field
	private $runId = null;
	
    /**
     * Get runId
     * No ORM Field
     * @return integer 
     */
	public function getRunId() {
		return $this->runId;
	}

    /**
     * Set runId
     * No ORM Field
     * @return integer 
     */
	public function setRunId($runId) {
		$this->runId = $runId;
	}

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Repository
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Repository
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set branch
     *
     * @param string $branch
     * @return Repository
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * Get branch
     *
     * @return string 
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Set component
     *
     * @param string $component
     * @return Repository
     */
    public function setComponent($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component
     *
     * @return string 
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set packagemanager
     *
     * @param string $packagemanager
     * @return Repository
     */
    public function setPackagemanager($packagemanager)
    {
        $this->packagemanager = $packagemanager;

        return $this;
    }

    /**
     * Get packagemanager
     *
     * @return string 
     */
    public function getPackagemanager()
    {
        return $this->packagemanager;
    }
    
    /**
     * Set architecture
     *
     * @param string $architecture
     * @return Repository
     */
    public function setArchitecture($architecture)
    {
        $this->architecture = $architecture;

        return $this;
    }

    /**
     * Get architecture
     *
     * @return string 
     */
    public function getArchitecture()
    {
        return $this->architecture;
    }
    
    /**
     * Add hosts
     *
     * @param \AppBundle\Entity\Host $hosts
     * @return Repository
     */
    public function addHost(\AppBundle\Entity\Host $hosts)
    {
        $this->hosts[] = $hosts;

        return $this;
    }

    /**
     * Remove hosts
     *
     * @param \AppBundle\Entity\Host $hosts
     */
    public function removeHost(\AppBundle\Entity\Host $hosts)
    {
        $this->hosts->removeElement($hosts);
    }

    /**
     * Get hosts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHosts()
    {
        return $this->hosts;
    }
    
    /**
     * abstract function: processPackages
     * @description: Implement function with process repository packages logic
     * @return boolean
     */
	abstract public function processPackages();
	
	public function persistPackage($package) {
		if(!$this->getPackageId($package)) {
			
			$entry['name'] = $package['package'];
			$entry['description'] = $package['description'];
			$entry['architecture'] = $package['architecture'];
			$this->persist('Package', $entry);
		}
		$this->persistPackageversion($package, $this->getRunId() );
	}
	
	public function persistPackageversion($package, $run = NULL) {
		$entry['repositoryid'] = $this->getId();
		$entry['packageid'] = new PdoBulkSubquery("SELECT id FROM `Package` WHERE name = '" . $package['package'] . "' AND architecture = '" . $package['architecture'] . "'");
		$entry['version'] = $package['version'];
		$entry['hexversion'] = $package['hexversion'];
		$entry['hexrevision'] = $package['hexrevision'];
		$entry['runid'] = $run;
		$this->persist('Packageversion', $entry);
		
		if($this->getQueueLength('Packageversion') && $this->getQueueLength('Packageversion') > 399) {
			$this->flushQueue('Package');
			$this->flushQueue('Packageversion', ' ON DUPLICATE KEY UPDATE `runid` = '.$this->getRunId().';');
		}
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
		$query = 'SELECT id FROM `Packageversion` WHERE repositoryid = :repositoryid AND packageid = :packageid AND version = :version;';
		$stmt = $this->getPdo()->prepare($query);
		$stmt->bindValue(':repositoryid', $this->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':packageid', $id, \PDO::PARAM_INT);
		$stmt->bindValue(':version', $version, \PDO::PARAM_STR);
		$stmt->execute();
		if($result = $stmt->fetch()) {
			return $result['id'];
		}
		return false;
	}
}
