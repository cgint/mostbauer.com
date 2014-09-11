<?php

$DBHost="mysqlsvr29.world4you.com";
$DBUser="mostbauercom";
$DBPass="r3c2a7h";
$DBName="mostbauercomdb1";
$tablePrefix="most_";

$webServerURL="http://Mostbauer.com";
//$webServerURL="http://gewerbeweb.com/mostbauer/";
// $webServerURL="http://mostbauer.com/ma";

// with / at the end

//$imgBaseDir="http://resurge.hypermart.net/mostbauer/images/";
//$imgBaseDir="http://g02.wist.uni-linz.ac.at/mostbauer-neu/images/";
//$imgBaseDir="http://gewerbeweb.com/mostbauer/images/";
$imgBaseDir="images/";

$timeZoneCorrection=0; // gewerbeweb -> CET

/*
$imgBaseDirs = array( // with / at the end
 "http://resurge.hypermart.net/mostbauer/images/",
 "http://g02.wist.uni-linz.ac.at/mostbauer-neu/images/",
 "http://gewerbeweb.com/mostbauer/images/",
 "../images/"
);

$imgBaseDirCount=4;
*/

global $tablePrefix, $DBHost,$DBUser,$DBPass,$DBName,$imgBaseDir;

mysql_connect($DBHost,$DBUser,$DBPass) or die ("config.php; unable to connect to database (".mysql_error().")");
mysql_select_db ($DBName);

?>
