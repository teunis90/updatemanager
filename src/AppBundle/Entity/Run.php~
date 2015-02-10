<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Run
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Run
{
    public function __construct()
    {
        $this->setRundate(new \DateTime()); 
    }
	
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rundate", type="datetime")
     */
    private $rundate;


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
     * Set rundate
     *
     * @param \DateTime $rundate
     * @return Run
     */
    public function setRundate($rundate)
    {
        $this->rundate = $rundate;

        return $this;
    }

    /**
     * Get rundate
     *
     * @return \DateTime 
     */
    public function getRundate()
    {
        return $this->rundate;
    }
}
