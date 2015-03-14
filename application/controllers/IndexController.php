<?php

set_time_limit ( 0 );
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
   * Determine the strength of the keyword on a web page
   * Index the keyword and strength against the url
   * Store Result Data in JSON File
   * @package    Controller
   * @author     Arul Kumar N <arul.hiriyur@gmail.com>
*/

class IndexController extends Zend_Controller_Action
{
	private $_url = NULL;

	private $_kw = NULL;

	public $_notKeywords = NULL;

	public $_helperObj = NULL;

	private $_uploadsBasePath = APPLICATION_UPLOADS_DIR;

	private $_downloadsDIR = APPLICATION_DOWNLOADS_DIR;

	private $_registry = NULL;

	/**
       * 
       * Constructor 
       * 
    */

	public function init()
	{
		/* Initialize action controller here */

		$this->_helperObj = $this->view->getHelper('MyUtilities');

		$this->_registry = Zend_Registry::getInstance();

		$this->_notKeywords = explode(',',$this->_registry->notkeywords);

	}

	/**
       * 
       * Index Action 
       * 
    */

	public function indexAction()
	{
		$this->_helper->viewRenderer->setNoRender(true);

		try {

			$request = $this->getRequest()->getParams();

			if(isset($request['url']) && $request['url']) {

				$this->_url = $this->_helperObj->addHttpToURL($request['url']);

				$this->crawlWebsite();

			} else if (isset($request['kw']) && $request['kw']) {
				
				$this->_kw = $request['kw']; 

				$this->searchKeyword();

			} else {
				throw new Exception("Error Processing Request", 0);
			}

		} catch (Exception $e) {
			$this->_helperObj->printExceptionDetails($e);
		}
	}

	/**
       * 
       * Search Keyword in JSON File 
       * 
    */
	public function searchKeyword()
	{
		try {
			
			$resultArray = json_decode(file_get_contents($this->_uploadsBasePath.'/resultData.json'),true);

			if($resultArray) {

				$result = isset($resultArray[$this->_kw]) ? $resultArray[$this->_kw] : array();

				if($result) {
					arsort($result);
					$ouptut = "<h1>Result for Keyword : ".$this->_kw."</h1>";
					$ouptut.= "------------------------------------------------------";

					foreach ($result as $key => $value) {
						$ouptut.= "<br/> Url :".$key."<br/>";
						$ouptut.= "<br/> Count :".$value."<br/>";
						$ouptut.= "------------------------------------------------------";
					}

					echo $ouptut;

				} else {
					$keys = array_keys($resultArray);
					
					echo "Available Keywords are : <br/>";

					echo implode(',', $keys),'<br/><br/>';

					throw new Exception("Data for Keyword '<b>".$this->_kw."</b>' Not Available", 0);
				}
			} else {
				throw new Exception("Data for Keyword '<b>".$this->_kw."</b>' Not Available", 0);
			}

		} catch (Exception $e) {
			$this->_helperObj->printExceptionDetails($e);
		}

	}

	/**
       * 
       * Crawl Webiste and store data in JSON File 
       * 
    */
	public function crawlWebsite()
	{
		try {
			
			$urlContent = $this->_helperObj->getCURLContent($this->_url);

			if($urlContent && isset($urlContent['http_code']) && $urlContent['http_code'] == 200) {

				$urlText = $this->_helperObj->getPlainTextFromHTML($urlContent['content']);

				$kwArray = explode(" ", $urlText);

				$kwArray = $this->_helperObj->filterStopWords($kwArray,$this->_notKeywords);
				$kwFrequencyList = $this->_helperObj->getKeywordFrequency($kwArray,$this->_url);

				if($kwFrequencyList) {

					$resultArray = json_decode(file_get_contents($this->_uploadsBasePath.'/resultData.json'),true);

					if($resultArray) {

						foreach ($kwFrequencyList as $key => $value) {
							if($value > 1) {
								$resultArray[$key][$this->_url] = $value; 
							}
						}
					} else {

						$resultArray = array();

						foreach ($kwFrequencyList as $key => $value) {
							if($value > 1) {
								$resultArray[$key][$this->_url] = $value; 
							}
						}
					}

					$resultArrayJson = $this->_helperObj->getArrayToJsonString($resultArray);

					$isWritten = $this->_helperObj->writeJSONResultData($resultArrayJson);

					if($isWritten) {

						echo "Data Loaded to Result File";

						echo "<pre>";
						print_r($resultArray);
						exit;
						
					} else {
						throw new Exception("Error Processing Result Data", 1);
					}
				} else {
					echo "No Meta Tags Available";
					exit;
				}
			} else {
				throw new Exception("Error Processing CURL Request", $urlContent['http_code']);
			}
		} catch (Exception $e) {
			$this->_helperObj->printExceptionDetails($e);
		}
	}
}