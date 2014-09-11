<?php

require_once ("server_config.php");

$maxSBONUploadFileSize=120000;

$weekdayShortStringArray=array("so", "mo", "di", "mi", "do", "fr", "sa");

$weekdayStringArray=array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
  
$sugElements=array(
	 					 "sugHausname" => "0",
	 					 "sugFamName" => "0",
	 					 "sugAdresse" => "0",
	 					 "sugOrt" => "0",
	 					 "sugWegBeschr" => "0",
	 					 "sugZeit" => "0",
	 					 "sugTelNr" => "0",
	 					 "sugWebAdresse" => "0",
	 					 "sugAussicht" => "0",
	 					 "sugBedienung" => "0",
	 					 "sugParken" => "0",
	 					 "sugBeschreibung" => "0"
						 );
/********************************
**         CMS - LINKS         **
********************************/

$CmsBaseLink = "http://www.mostbauer.com/";
				 
$CmsKarteLinks=array(
	 					 "overview" => "{$CmsBaseLink}index.php?option=com_content&task=view&id=19&Itemid=23",
	 					 "nw" => "{$CmsBaseLink}index.php?option=com_content&task=view&id=20&Itemid=24",
	 					 "no" => "{$CmsBaseLink}index.php?option=com_content&task=view&id=21&Itemid=25",
	 					 "so" => "{$CmsBaseLink}index.php?option=com_content&task=view&id=23&Itemid=27",
	 					 "sw" => "{$CmsBaseLink}index.php?option=com_content&task=view&id=22&Itemid=26",
	 					 "c"  => "{$CmsBaseLink}index.php?option=com_content&task=view&id=24&Itemid=28",
						 );

$CmsLinkOverview    = "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=2";
$CmsLinkDetailBase  = "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=2&bauerPageType=schmalzbrotdetail&bauerID=";
$CmsLinkBewBaseNoId = "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=2&bauerPageType=schmalzbrotbew";
$CmsLinkBewParamOption = "com_wrapper";
$CmsLinkBewParamItemId = "2";
$CmsLinkBewBase     = "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=2&bauerPageType=schmalzbrotbew&bauerID=";
$CmsLinkGalerieBase = "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=2&bauerPageType=schmalzbrotgalerie&bauerID=";
$CmsLinkMapBase  		= "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=46&bauerPageType=mb_gmaps&bauerID=";
$CmsLinkMapWTagBase	= "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=46&bauerPageType=mb_gmaps&wochentag=";
$CmsLinkHaftung     = "{$CmsBaseLink}index.php?option=com_content&task=view&id=1&Itemid=6";
$CmsLinkInfo        = "{$CmsBaseLink}index.php?option=com_content&task=view&id=7&Itemid=14";

?>
