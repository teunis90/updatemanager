<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hostpackageversion
 *
 * @ORM\Table()
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_hostpackageversion", columns={"hostid", "packageversionid"})})
 * @ORM\Entity
 */
class Hostpackageversion
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Host")
     * @ORM\JoinColumn(name="hostid", referencedColumnName="id")
     */
    private $host;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Packageversion")
     * @ORM\JoinColumn(name="packageversionid", referencedColumnName="id")
     */
    private $packageversion;
    
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
     * Set host
     *
     * @param \AppBundle\Entity\Host $host
     * @return Hostpackageversion
     */
    public function setHost(\AppBundle\Entity\Host $host = null)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return \AppBundle\Entity\Host 
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set packageversion
     *
     * @param \AppBundle\Entity\Packageversion $packageversion
     * @return Hostpackageversion
     */
    public function setPackageversion(\AppBundle\Entity\Packageversion $packageversion = null)
    {
        $this->packageversion = $packageversion;

        return $this;
    }

    /**
     * Get packageversion
     *
     * @return \AppBundle\Entity\Packageversion 
     */
    public function getPackageversion()
    {
        return $this->packageversion;
    }

    /**
     * Set run
     *
     * @param \AppBundle\Entity\Run $run
     * @return Hostpackageversion
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
