<?php
	
namespace AppBundle\Classes;

class CurlWrapper {
	
	// Disable instances of this class
	private function __construct() {}
	
	public static function downloadToFile($url, $filepath) {
		if( ! self::cURLcheckBasicFunctions() ) {
			throw new \Exception('Could not call curl module functions');
		}
		
		$fh = @fopen($filepath, 'w');
		if(! $fh) {
			throw new \Exception('Could not open file for writing: [' . $filepath . ']');
		}
		
		$options = array(
		  CURLOPT_FILE    => $fh,
		  CURLOPT_TIMEOUT =>  28800, 
		  CURLOPT_URL     => $url
		);

		$ch = curl_init();
		if( ! curl_setopt_array($ch, $options) ) {
			throw new \Exception('Could not set curl options');			
		}
		
		curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// Return true if status code start with 2
		// TODO: We should test if CURL is following 30X redirects. If it does '3' should be accepted as well.
		if( substr($http_status, 0, 1) == '2' ) {
			return true;
		} else {
			unlink($filepath);
			return false;
		}
	}
	
	public static function downloadToVar($url) {
		if( ! self::cURLcheckBasicFunctions() ) {
			throw new \Exception('Could not call curl module functions');
		}
		
		$options = array(
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_TIMEOUT =>  28800,
		  CURLOPT_URL     => $url
		);

		$ch = curl_init();
		if( ! curl_setopt_array($ch, $options) ) {
			throw new \Exception('Could not set curl options');			
		}
		
		$ret = curl_exec($ch);
		curl_close($ch);

		return $ret;
	}

	private static function cURLcheckBasicFunctions()
	{
	  if( !function_exists("curl_init") && !function_exists("curl_setopt") &&
	      !function_exists("curl_exec") && !function_exists("curl_close") ) 
	    return false;
	  else 
	    return true;
	}
}

?>