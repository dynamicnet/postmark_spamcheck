# PHP class to use Postmark Spamcheck webservice


Documentation of the webservice can be found [here](http://spamcheck.postmarkapp.com/doc).





Requirements
----------------

 * This class relies on the Zend Framework classes Zend_Http_Client / Zend_Http_Response
 

## Usage

<?php

require_once "Postmark_SpamCheck.php";

$RawEmail = <<<EOT
Here is the raw email, including headers
EOT;

$SpamCheck = new Postmark_SpamCheck();

$Result = $SpamCheck->setMode("long")->setRawEmail($RawEmail)->check();