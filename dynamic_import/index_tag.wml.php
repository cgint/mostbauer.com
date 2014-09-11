<?php 
  require("itemView.php");
  require("config.php");
  
  header("Content-type: text/vnd.wap.wml");
  
  $WML= <<< EOB
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>
 <card id="home" title="MostbauernEckn" newcontext="true">
  <p align="center">
   <small>MostbauernEckn fuer Stodleit von Linz</small><br/>
   ---------<br/>
  </p>
  <p align="left">
  <a href="index.wml.php">Neue Suche</a><br/>

EOB;
  
  if(isset($_GET["tag"]) && $_GET["tag"] != "" ) {
    $WML.="Offen am ".$weekdayStringArray[$_GET["tag"]%7].":<br/>\n";
    $dayField=$weekdayShortStringArray[$_GET["tag"]%7];
	 $queryString="SELECT b.ID, r.name FROM {$tablePrefix}bauer as b, {$tablePrefix}tage as t, {$tablePrefix}region as r WHERE b.regionID=r.ID AND b.ID=t.bauerID AND NOT ISNULL(t.{$dayField}) ORDER BY r.ID, b.ID DESC";
  }  else {
    $WML.="--------------<br/>\n";
	 $queryString="SELECT b.ID, r.name FROM {$tablePrefix}bauer as b, {$tablePrefix}region as r WHERE b.regionID=r.ID ORDER BY r.ID, b.ID DESC;";
  }  
  $itemsListResult=mysql_query($queryString) or die ("index_tag.wml.php: Database error (".mysql_error().")");
  $oldRegionName="";
  while(list($ID, $regionName)=mysql_fetch_row($itemsListResult)) {
    if($regionName != $oldRegionName) {
	   $WML.=" - {$regionName}<br/>\n";
	   $oldRegionName=$regionName;
	 }
    $WML.=viewItemDetailWML($ID, "list");
  }
  $WML.= <<< EOB
  </p>
 </card>
</wml>
EOB;
  echo $WML;
?>
