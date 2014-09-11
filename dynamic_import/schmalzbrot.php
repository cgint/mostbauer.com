<?php

  require("itemView.php");

  require("config.php");

  $headline = "&Uuml;bersicht";

?>

<?
if( !isset($_REQUEST["include"]) ) {
?>
<html>
<head>
<title>www.Mostbauer.com - Mostbauern Eckn f�r d' Stodleit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body onload="if ( this == top ) document.location = '<?=$CmsLinkOverview.$_REQUEST["ID"]?>';">
<?
 } else {
?>
<link rel="stylesheet" href="http://mostbauer.com/dynamic_import/styles.css" type="text/css">
<?
}
?>

<table width="566" border="0" cellpadding="0" cellspacing="0">

  <tr valign="top"> 

    <td class="top_headline"><?=$headline?></td>

  </tr>

  <tr valign="top"> 

    <td background="images/higru.jpg" valig="top"> 

      <table border="0" cellspacing="0" cellpadding="0">

        <tr> 

          <td width="36" height="418" valign="top">&nbsp;</td>

          <td height="418" valign="top">

<?php

	$wochentagCode = display_weekday_selection($wochentag, "schmalzbrot");


	$itemsListArray = fetch_bauern_region_list($wochentagCode);
  

  foreach($itemsListArray as $ID => $regionName) {

    if($regionName != $oldRegionName) {

	   if(isset($oldRegionName)) echo "<br>"; // if this is not the first call

	   viewRegionSplit($regionName);

	   $oldRegionName=$regionName;

	 }

    echo "<br>";

    viewItemDetail($ID, "list");

  }



  

?>





          </td>

        </tr>

      </table>

    </td>

  </tr>

</table>



<a target="_top" href="http://extremetracking.com/open?login=gintchr2">

<img src="http://t1.extreme-dm.com/i.gif" height=38

border=0 width=41 alt=""></a><script language="javascript1.2"><!--

EXs=screen;EXw=EXs.width;navigator.appName!="Netscape"?

EXb=EXs.colorDepth:EXb=EXs.pixelDepth;//-->

</script><script language="javascript"><!--

EXd=document;EXw?"":EXw="na";EXb?"":EXb="na";

EXd.write("<img src=\"http://t0.extreme-dm.com",

"/c.g?tag=gintchr2&j=y&srw="+EXw+"&srb="+EXb+"&",

"l="+escape(EXd.referrer)+"\" height=1 width=1>");//-->

</script><noscript><img height=1 width=1 alt=""

src="http://t0.extreme-dm.com/c.g?tag=gintchr2&j=n"></noscript>


<?
if( !isset($_REQUEST["include"]) ) {
?>
</body>
</html>
<?
}
?>
