<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../Classes/Phpseclib');
include_once('Crypt/RSA.php');
include_once('Net/SSH2.php');
include_once('Net/SFTP.php');

/**
 * Host
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("hostname")
 */
class Host
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->repositories = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @var string
     *
     * @ORM\Column(name="hostname", type="string", length=255, unique=true)
     */
    private $hostname;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=45, nullable=true)
     * @Assert\Ip(version="all_no_res")
     */
    private $ip;

    /**
     * @var boolean
     *
     * @ORM\Column(name="useip", type="boolean")
     */
    private $useip;

    /**
     * @var string
     *
     * @ORM\Column(name="sshuser", type="string", length=32, options={"default": "root"})
     */
    private $sshuser;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sshkeychain")
     * @ORM\JoinColumn(name="sshkeychainid", referencedColumnName="id")
     * @Assert\Type(type="AppBundle\Entity\Sshkeychain")
     * @Assert\Valid()
     */
    private $sshkeychainid;

    /**
     * @var integer
     *
     * @ORM\Column(name="sshstatus", type="integer")
     */
    private $sshstatus;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sshsudo", type="boolean")
     */
    private $sshsudo;
    
    /**
	 * $var PacketManagerStrategy
	 */
	private $packetmanagerstrategy;
	
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Repository", mappedBy="hosts")
     */
    private $repositories;

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
     * Set hostname
     *
     * @param string $hostname
     * @return Host
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string 
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return Host
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set useip
     *
     * @param integer $useip
     * @return Host
     */
    public function setUseip($useip)
    {
        $this->useip = $useip;

        return $this;
    }

    /**
     * Get useip
     *
     * @return integer 
     */
    public function getUseip()
    {
        return $this->useip;
    }

    /**
     * Set sshuser
     *
     * @param string $sshuser
     * @return Host
     */
    public function setSshuser($sshuser)
    {
        $this->sshuser = $sshuser;

        return $this;
    }

    /**
     * Get sshuser
     *
     * @return string 
     */
    public function getSshuser()
    {
        return $this->sshuser;
    }

    /**
     * Get sshkeychainid
     *
     * @return Sshkeychain 
     */
    public function getSshkeychainid()
    {
        return $this->sshkeychainid;
    }
    
    /**
     * Set sshkeychainid
     *
     * @param \AppBundle\Entity\Sshkeychain $sshkeychainid
     * @return Host
     */
    public function setSshkeychainid(\AppBundle\Entity\Sshkeychain $sshkeychainid = null)
    {
        $this->sshkeychainid = $sshkeychainid;

        return $this;
    }

    /**
     * Set sshstatus
     *
     * @param integer $sshstatus
     * @return Host
     */
    public function setSshstatus($sshstatus)
    {
        $this->sshstatus = $sshstatus;

        return $this;
    }

    /**
     * Get sshstatus
     *
     * @return integer 
     */
    public function getSshstatus()
    {
        return $this->sshstatus;
    }

    /**
     * Set sshsudo
     *
     * @param integer $sshsudo
     * @return Host
     */
    public function setSshsudo($sshsudo)
    {
        $this->sshsudo = $sshsudo;

        return $this;
    }

    /**
     * Get sshsudo
     *
     * @return integer 
     */
    public function getSshsudo()
    {
        return $this->sshsudo;
    }

    /**
     * Set packetmanagerstrategy
     *
     * @return Host
     */
    public function setPackageManagerStrategy($pm) {
	    // TODO: Type checking instanceof PacketManagerStrategy
	    $this->packetmanagerstrategy = $pm;

        return $this;
    }
    
    /**
     * Get packetmanagerstrategy
     *
     * @return packetmanagerstrategy 
     */
    public function getPackageManagerStrategy() {
	    return $this->packetmanagerstrategy;
    }
    
    /**
     * Add repositories
     *
     * @param \AppBundle\Entity\Repository $repositories
     * @return Host
     */
    public function addRepository(\AppBundle\Entity\Repository $repositories)
    {
        $this->repositories[] = $repositories;

        return $this;
    }

    /**
     * Remove repositories
     *
     * @param \AppBundle\Entity\Repository $repositories
     */
    public function removeRepository(\AppBundle\Entity\Repository $repositories)
    {
        $this->repositories->removeElement($repositories);
    }

    /**
     * Get repositories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRepositories()
    {
        return $this->repositories;
    }
    
    public function getSftpConnection() {
		$sftp = new \Net_SFTP($this->getHostname(), 22);
		$key = new \Crypt_RSA();
		$key->loadKey(file_get_contents( $this->getPrivatekeypath() ));
		if (!$sftp->login($this->getSshuser(), $key)) {
		    throw new \Exception('SSH key login failed for ('.$this->getSshuser().'@'.$this->getHostname().')');
		}
		return $sftp;
    }
    
	public function getPrivatekeypath() {
		return __DIR__ . '/../../../app/sshkeys/' . $this->getSshkeychainid()->getSshprivatekeyfilepath();
	}
	
	public function getPublickeypath() {
		return __DIR__ . '/../../../app/sshkeys/' . $this->getSshkeychainid()->getSshpublickeyfilepath();
	}
    
	public function testKeyLogin() { 
		$sftp = $this->getSftpConnection();
		
		$ret = true;
		$homedir = $sftp->exec('cd ~; pwd');
		$sftp->chdir($homedir);
		if( trim($homedir) != trim($sftp->pwd()) ) {
		    $ret = false;
		}
		if(strlen($homedir) == 0) {
			$ret = false;
		}		
		return $ret;
	}
	
	public function uploadPublickey($password) {
		$sftp = new \Net_SFTP($this->getHostname(), 22);
		if (!$sftp->login($this->getSshuser(), $password)) {
		    throw new \Exception('SSH login failed for ('.$this->getSshuser().'@'.$this->getHostname().')');
		}
		$homedir = $sftp->exec('cd ~; pwd');
		$sftp->chdir($homedir);
		if( trim($homedir) != trim($sftp->pwd()) ) {
		    throw new \Exception('Could not change dir to home directory.');
		}
		if(!$sftp->stat('.ssh')) {
			$sftp->mkdir('.ssh');
		}
		$sftp->chdir('.ssh');
		if( trim($homedir) == trim($sftp->pwd()) ) {
		    throw new \Exception('Could not change dir to ~/.ssh directory.');
		}
		if(!$sftp->stat('authorized_keys')) {
			$sftp->touch('authorized_keys');
		}
		if(!$sftp->stat('authorized_keys')) {
		    throw new \Exception('Could not create ~/.ssh/authorized_keys for ('.$this->getSshuser().'@'.$this->getHostname().')');
		}
		if(!file_exists( $this->getPublickeypath() )) {
		    throw new \Exception('Could not find publickeyfile ('.$this->getPublickeypath().')');			
		}
		
		$key = preg_replace('~[\r\n]+~', '', trim(file_get_contents($this->getPublickeypath() ) ) );
		$sftp->exec("cd ~/.ssh/; grep -q -F '".$key."' authorized_keys || echo '".$key."' >> authorized_keys");
		$sftp->exec("cd ~/.ssh/; grep -q -F '".$key."' authorized_keys");

		return $sftp->getExitStatus() == 0 ? true : false;
	}
}
