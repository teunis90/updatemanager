<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Sshkeychain
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("sshprivatekeyfilepath")
 * @UniqueEntity("sshpublickeyfilepath")
 */
class Sshkeychain
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
     * @ORM\Column(name="sshprivatekeyfilepath", type="string", length=255, unique=true)
     */
    private $sshprivatekeyfilepath;

    /**
     * @var string
     *
     * @ORM\Column(name="sshpublickeyfilepath", type="string", length=255, unique=true)
     */
    private $sshpublickeyfilepath;

    /**
     * @var integer
     *
     * @ORM\Column(name="defaultkey", type="integer", options={"default": 0})
     */
    private $defaultkey;
    
    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string")
     */
    private $comment;


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
     * Set sshprivatekeyfilepath
     *
     * @param string $sshprivatekeyfilepath
     * @return sshkeychain
     */
    public function setSshprivatekeyfilepath($sshprivatekeyfilepath)
    {
        $this->sshprivatekeyfilepath = $sshprivatekeyfilepath;

        return $this;
    }

    /**
     * Get sshprivatekeyfilepath
     *
     * @return string 
     */
    public function getSshprivatekeyfilepath()
    {
        return $this->sshprivatekeyfilepath;
    }

    /**
     * Set sshpublickeyfilepath
     *
     * @param string $sshpublickeyfilepath
     * @return sshkeychain
     */
    public function setSshpublickeyfilepath($sshpublickeyfilepath)
    {
        $this->sshpublickeyfilepath = $sshpublickeyfilepath;

        return $this;
    }

    /**
     * Get sshpublickeyfilepath
     *
     * @return string 
     */
    public function getSshpublickeyfilepath()
    {
        return $this->sshpublickeyfilepath;
    }

    /**
     * Set defaultkey
     *
     * @param integer $defaultkey
     * @return sshkeychain
     */
    public function setDefaultkey($defaultkey)
    {
        $this->defaultkey = $defaultkey;

        return $this;
    }

    /**
     * Get defaultkey
     *
     * @return integer 
     */
    public function getDefaultkey()
    {
        return $this->defaultkey;
    }
    
    /**
     * Set comment
     *
     * @param string $comment
     * @return sshkeychain
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }
}
