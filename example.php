<?php

/*
	example.php
*/

//__ Adds Zend Framwork directory in your include path
$zfpath = dirname(__FILE__).'/../';
set_include_path(get_include_path() . PATH_SEPARATOR . $zfpath);


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