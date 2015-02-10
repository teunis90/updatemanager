<?php

namespace AppBundle\Classes;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/Phpseclib');
include('Crypt/RSA.php');
include('Net/SSH2.php');
include('Net/SFTP.php');

class Sshlib {
	public function test() {		
		
		$ssh = new \Net_SSH2('testansible.nextpertise.nl');
		if (!$ssh->login('root', 'Sj@@k123')) {
		    exit('Login Failed');
		}
		
		echo $ssh->exec('pwd');
		echo $ssh->exec('ls -la');
				
	}
	
	public function testKeyLogin($hostname, $user, $privatekeyfile) {
		$sftp = new \Net_SFTP($hostname, 22);
		$key = new \Crypt_RSA();
		$key->loadKey(file_get_contents($privatekeyfile));
		if (!$sftp->login($user, $key)) {
		    throw new \Exception('SSH key login failed for ('.$user.'@'.$hostname.')');
		}
		
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
	
	public function uploadPublickey($hostname, $user, $password, $publickeyfile) {
		$sftp = new \Net_SFTP($hostname, 22);
		if (!$sftp->login($user, $password)) {
		    throw new \Exception('2SSH login failed for ('.$user.'@'.$hostname.')');
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
		    throw new \Exception('Could not create ~/.ssh/authorized_keys for ('.$user.'@'.$hostname.')');
		}
		if(!file_exists($publickeyfile)) {
		    throw new \Exception('Could not find privatekeyfile ('.$publickeyfile.')');			
		}
		
		$key = preg_replace('~[\r\n]+~', '', trim(file_get_contents($publickeyfile)));
		$sftp->exec("cd ~/.ssh/; grep -q -F '".$key."' authorized_keys || echo '".$key."' >> authorized_keys");
		$sftp->exec("cd ~/.ssh/; grep -q -F '".$key."' authorized_keys");

		return $sftp->getExitStatus() == 0 ? true : false;
	}
}

?>



