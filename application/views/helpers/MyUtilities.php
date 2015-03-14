<?php

set_time_limit ( 0 );
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
   *
   * Helper functions
   *
   * @package    Controller
   * @author     Arul Kumar N <arul.hiriyur@gmail.com>
*/

class Zend_View_Helper_MyUtilities extends Zend_View_Helper_Abstract
{
	private $_handle = NULL;

	private $_uploadsBasePath = APPLICATION_UPLOADS_DIR;

	private $_downloadsDIR = APPLICATION_DOWNLOADS_DIR;

	private $_randomNumber = null;

	private $_currDate = NULL;

	/**
       * 
       * Constructor 
       * 
    */
	public function MyUtilities()
	{
		return $this;
	}

	/**
       * 
       * Print Exception Details
       *
       * @param Exception Object
       * @return Print Exception Details
    */
	public function printExceptionDetails($e)
	{
		echo $e->getCode()."<br/><pre>";
		echo $e->getMessage()."<br/><pre>";
		//echo $e->getFile()."<br/><pre>";
		//echo $e->getLine()."<br/><pre>";
		//exit;
	}


	/**
       * 
       * Validate URL
       *
       * @param String URL
       * @return Validated URL
    */
	public function addHttpToURL($url)
	{
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}
		return $url;
	}

	/**
       * 
       * Extract Plain Text from HTML Code
       *
       * @param HTML
       * @return String Plain Text
    */
	public function getPlainTextFromhtml($urlText) {

		$urlText = preg_replace('/(<script.*?>.*?<\/script>|<style.*?>.*?<\/style>|<.*?>|\r|\n|\t)/ms', '', $urlText);
		$urlText = preg_replace('~<[^>]*>~', '', $urlText);
		$urlText = preg_replace('/\s+/', ' ', trim($urlText));

		return $urlText;
	}

	/**
       * 
       * Create Directories
       *
       * @param String Directory Path
       * @return Boolean True
    */
	public function mkdirs ($dir)
	{
		$oldmask = umask(0);
		if (!is_dir ($dir))
		{
			if (!$this->mkdirs(dirname ($dir))) {
				return false;
			}
			if (!mkdir ($dir, 0777, true)) {
				return false;
			}
		}
		umask($oldmask);
		
		return true;
	}

	/**
       * 
       * Convert Array to JSON
       *
       * @param Array Values
       * @return String JSON
    */
	public function getArrayToJsonString($myArray)
	{
		return Zend_Json::encode($myArray);
	}

	/**
       * 
       * Get content of URL using CURL
       *
       * @param String URL
       * @return Array URL Data
    */
	public function getCURLContent($url) {
		$url= TRIM($url);
		$CURLInfo = array();

		$headers = array( "User-Agent:MyAgent/1.0\r\n");

		try	{
			
			$options = array(
	        CURLOPT_RETURNTRANSFER 	=> true,     				 // return web page
	        CURLOPT_REFERER 		=> $url,     				 // return web page
	        CURLOPT_HEADER         	=> false,    				 // don't return headers
	        CURLOPT_FOLLOWLOCATION 	=> true,     				 // follow redirects
	        CURLOPT_ENCODING       	=> "",       				 // handle all encodings
	        CURLOPT_USERAGENT      	=> "User-Agent:MyAgent/1.0", // who am i
	        CURLOPT_AUTOREFERER    	=> true,     				 // set referer on redirect
	        CURLOPT_CONNECTTIMEOUT 	=> 60,      				 // timeout on connect
	        CURLOPT_TIMEOUT        	=> 60,      				 // timeout on response
	        CURLOPT_MAXREDIRS      	=> 60,       				 // stop after 10 redirects
	        CURLOPT_SSL_VERIFYPEER 	=> false,     				 // Disabled SSL Cert checks
	        CURLOPT_SSLVERSION		=> 3,     					 // Disabled SSL Cert checks
	        );

			$this->_handle = curl_init($url);
			curl_setopt_array( $this->_handle, $options );
			$content = curl_exec(  $this->_handle );
			$err     = curl_errno(  $this->_handle );
			$errmsg  = curl_error(  $this->_handle );
			$header  = curl_getinfo(  $this->_handle );
			curl_close(  $this->_handle );

			$header['errno']   = $err;
			$header['errmsg']  = $errmsg;
			$header['content'] = $content;

			return $header;

		} catch( Exception $e) {
			
			curl_close($this->_handle);
			
			return FALSE;
		}
	}

	/**
       * 
       * Write JSON Response to JSON File
       *
       * @param String JSON
       * @return Boolean TRUE/FALSE
    */
	public function writeJSONResultData($jsonText)
	{
		$filePath = $this->_uploadsBasePath.'/resultData.json';

		return file_put_contents($filePath, $jsonText);

	}

	/**
       * 
       * Filter Keywords
       *
       * @param Array Keywords
       * @return Array Filtered Keywords
    */
	public function filterStopWords($kwArray,$notKeywords) {
		
		$filteredWords = $filteredKeywords = array();

		foreach ($kwArray as $pos => $word) {
			if (!in_array(strtolower($word), $notKeywords, TRUE)) {
				$filteredWords[$pos] = $word;
			}
		}

		$filteredKeywords = array_values(array_filter(preg_replace("/[^a-zA-Z0-9]/", "", $filteredWords)));

		return $filteredKeywords;

	}

	/**
       * 
       * Calculate Keyword Frequencies
       *
       * @param Array Keywords
       * @return Array Keywords with Frequencies
    */
	function getKeywordFrequency($filteredKeywords) {

		$frequencyList = array();

		foreach ($filteredKeywords as $pos => $word) {

			$word = strtolower($word);

			if(isset($frequencyList[$word]))  {
				++$frequencyList[$word];
			} else {
				$frequencyList[$word] = 1;
			}
		}

		arsort($frequencyList);

		return $frequencyList;

	}
}