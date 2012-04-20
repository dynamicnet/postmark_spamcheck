<?php

require_once "Zend/Http/Client.php";

class Postmark_SpamCheck {

	static  $_end_point = "http://spamcheck.postmarkapp.com/filter";

	protected $_RawEmail = null;
	protected $_mode = "long";


	protected $_HttpClient = null;



	public function __construct() {

		$this->_HttpClient = new Zend_Http_Client( self::$_end_point );

	}


	/**
	 *	@param string $RawEmail
	 */
	public function setRawEmail( $RawEmail ) {

		$this->_RawEmail = $RawEmail;

		return $this;
	}

	/**
	 *	@param string $mode
	 */
	public function setMode( $mode ) {
		$mode = strtolower($mode);

		if( ! in_array( $mode , array( "long" , "short" ) ) ) {
			throw new Postmark_SpamCheck_Exception("setMode() only accepts \"long\" or \"short\"");
		}

		$this->_mode = $mode;

		return $this;
	}


	/**
	 *	@return Postmark_SpamCheck_Response
	 */
	public function check() {
		if( is_null( $this->_RawEmail ) ) {
			throw new Postmark_SpamCheck_Exception("Raw email message must be set, please use setRawEmail() before check()");
		}


		$this->_HttpClient->setHeaders('Accept', 'application/json');
		$this->_HttpClient->setHeaders('Content-Type', 'application/json');


		$this->_HttpClient->setRawData( json_encode(array(
			"email"		=> ltrim($this->_RawEmail), // sometime leading whitespace make spamassassin trigger bad rules (missing headers, etc)
			"options"	=> $this->_mode
		)) );


		$this->_HttpClient->setMethod(Zend_Http_Client::POST);
		$Response = $this->_HttpClient->request();

		if( $Response->isError() ) {
			throw new Postmark_SpamCheck_Exception("End Point return an HTTP error (Status: {$Response->getStatus()} {$Response->getMessage()})");
		}

		return new Postmark_SpamCheck_Response($Response->getBody());
	}

}


class Postmark_SpamCheck_Response {
	/**
	 *	@var float
	 */
	public $score = 0;

	/**
	 *	@var string
	 */
	public $RawReport = null;

	/**
	 *	@var array
	 */
	public $ReportArray = null;

	/**
	 *	@var boolean
	 */
	protected $_success;

	/**
	 *	@var string
	 */
	protected $_error_msg;



	public function __construct( $ResponseJson ) {
		$Response = json_decode($ResponseJson);

		// Erroneous JSON data ?
		if( is_null( $Response ) ) {
			throw new Postmark_SpamCheck_Exception("Webservice return an invalid JSON string");
		}

		$this->_success = (bool)$Response->success;

		if( ! $this->_success ) {
			$this->_error_msg = $Response->message;
		}


		if( isset( $Response->report ) ) {
			$this->RawReport = $Response->report;

			$this->_parseReport();
		}


		$this->score = $Response->score;
	}


	/**
	 *	Sometime webservice return an error if spamassasin fail.
	 *
	 *	@return boolean
	 */
	public function isError(){
		return !$this->_success;
	}


	/**
	 *	Return the last error message
	 *
	 *	@return string
	 */
	public function getError() {
		return !$this->_error_msg;
	}


	/**
	 *	Convert the text report in a beautiful usable array version
	 */
	protected function _parseReport() {

		$tmp = explode("\n" , $this->RawReport);

		for( $i = 0,$PreviousComplete = null ; $line = current($tmp) ; $i++,next($tmp) ) {
			//__ Ignore the first 2 lines eand empty lines
			if( 0 == $i || 1 == $i || "" == trim($line) ) {
				continue;
			}

			//__ match a partial line
			if( preg_match("/^\s{3,}/" , $line) ) {
				//__ appending to the previous complete line
				if( ! is_null($PreviousComplete) ) {
					$PreviousComplete->description .= " ".trim($line);
				}
			}

			//__ match and parse a complete line
			if( preg_match("/^(\-?[0-9]+\.[0-9]+)\s([A-Z_]+)\s+(.*)/" , trim($line) , $matches) ) {

				$oTmp = new stdClass;
				$oTmp->points = (float)$matches[1];
				$oTmp->rule_name = $matches[2];
				$oTmp->description = $matches[3];

				$this->ReportArray[] = $oTmp;

				$PreviousComplete = &$oTmp;
			}
		}
	}

}


class Postmark_SpamCheck_Exception extends Exception {
}