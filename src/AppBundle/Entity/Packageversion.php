<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Packageversion
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_packageversion", columns={"repositoryid", "packageid", "version"})})
 * @ORM\Entity
 */
class Packageversion
{
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
     * @ORM\Column(name="version", type="string", length=64)
     */
    private $version;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Repository")
     * @ORM\JoinColumn(name="repositoryid", referencedColumnName="id")
     */
    private $repository;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Package")
     * @ORM\JoinColumn(name="packageid", referencedColumnName="id")
     */
    private $package;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Run")
     * @ORM\JoinColumn(name="runid", referencedColumnName="id")
     */
    private $run;


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
     * Set version
     *
     * @param string $version
     * @return Packageversion
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set repository
     *
     * @param \AppBundle\Entity\Repository $repository
     * @return Packageversion
     */
    public function setRepository(\AppBundle\Entity\Repository $repository = null)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Get repository
     *
     * @return \AppBundle\Entity\Repository 
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set package
     *
     * @param \AppBundle\Entity\Package $package
     * @return Packageversion
     */
    public function setPackage(\AppBundle\Entity\Package $package = null)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * Get package
     *
     * @return \AppBundle\Entity\Package 
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Set run
     *
     * @param \AppBundle\Entity\Run $run
     * @return Packageversion
     */
    public function setRun(\AppBundle\Entity\Run $run = null)
    {
        $this->run = $run;

        return $this;
    }

    /**
     * Get run
     *
     * @return \AppBundle\Entity\Run 
     */
    public function getRun()
    {
        return $this->run;
    }

}
