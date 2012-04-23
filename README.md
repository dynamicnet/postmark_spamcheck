# PHP class to use Postmark Spamcheck webservice


Documentation of the webservice can be found [here](http://spamcheck.postmarkapp.com/doc).





Requirements
----------------

 * This class relies on the Zend Framework classes Zend_Http_Client / Zend_Http_Response


## Example usage

```php
<?php
	require_once "Postmark/SpamCheck.php";

	$RawEmail = <<<EOT
	Here come the raw email, including headers
	EOT;

	$SpamCheck = new Postmark_SpamCheck();

	$Response = $SpamCheck->setMode("long")->setRawEmail($RawEmail)->check();

	print "Total score: {$Response->score} points\n";

	foreach( $Response->ReportArray as $ReportLine ) {
		print "- Rule {$ReportLine->rule_name} matches with {$ReportLine->points} points\n";
	}
	
	/*
	Total score: 7.9 points
	- Rule NO_RELAYS matches with -0 points
	- Rule MISSING_HEADERS matches with 1.2 points
	- Rule MISSING_MID matches with 0.1 points
	- Rule MISSING_SUBJECT matches with 1.8 points
	- Rule EMPTY_MESSAGE matches with 2.3 points
	- Rule MISSING_FROM matches with 1 points
	- Rule NO_RECEIVED matches with -0 points
	- Rule MISSING_DATE matches with 1.4 points
	- Rule NO_HEADERS_MESSAGE matches with 0 points
	*/
```