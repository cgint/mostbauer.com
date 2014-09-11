<?php
  require("itemView.php");
  if(!($nameFamName=getItemNameFamName($ID))) die("Der Mostbauer mit der ID='$ID' ist nicht oder nicht mehr im System.");
  $name=getItemName($ID);
  global $CmsLinkInfo;
?>

<?
if( !isset($_REQUEST["include"]) ) {
?>
<html>
<head>
<title>www.Mostbauer.com - Mostbauern Eckn f&uuml;r d' Stodleit - Detail: <?=$nameFamName?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="styles.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="mb_gmaps.css" />
	<script type="text/javascript">
		// set page-parameters
		var anfahrtId=<?=$ID?>;
		var gmapsBackendURL = "mb_gmaps_backend.php";
	</script>
	<script src="javascript/detail_div.js" type="text/javascript"></script>
	<script type="text/javascript" src="gmaps_lib/jquery-1.4.1.min.js"></script>
	<script src="http://www.google.com/jsapi?key=ABQIAAAAdOT6QtejptiTMh6F6lauoRQ0jeGsySTJPOlA8He0i_oJxGVfSRRnfaFf-7toSO2C3rZORmi9kANJhQ" type="text/javascript"></script> 
	<script type="text/javascript" src="gmaps_lib/mb_gmaps.js"></script>
	<script type="text/javascript">
		google.setOnLoadCallback(mbgm_initialize_map_anfahrt);
	</script>
</head>
<body onload="if ( this == top ) document.location = '<?=$CmsLinkDetailBase.$_REQUEST["ID"]?>';">
<?
 } else {
?>
<link rel="stylesheet" href="http://mostbauer.com/dynamic_import/styles.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="http://mostbauer.com/dynamic_import/mb_gmaps.css" />
	<script type="text/javascript">
		// set page-parameters
		var anfahrtId=<?=$ID?>;
		var gmapsBackendURL = "http://www.mostbauer.com/dynamic_import/mb_gmaps_backend.php";
	</script>
	<script src="http://mostbauer.com/dynamic_import/javascript/detail_div.js" type="text/javascript"></script>
	<script type="text/javascript" src="http://mostbauer.com/dynamic_import/gmaps_lib/jquery-1.4.1.min.js"></script>
	<script src="http://www.google.com/jsapi" type="text/javascript"></script> 
	<script type="text/javascript" src="http://mostbauer.com/dynamic_import/gmaps_lib/mb_gmaps.js"></script>
	<script type="text/javascript">
		google.setOnLoadCallback(mbgm_initialize_map_anfahrt);
	</script>
<?
}
?>

<table width="700" border="0" cellpadding="0" cellspacing="0" bgcolor="">
  <tr valign="top"> 
    <td class="top_headline"><?=$name?><img src="images/ico_birne.gif" width="15" height="15" align="absmiddle"> 
      <img src="images/square-full.gif" width="7" height="7"><img src="images/square-full.gif" width="7" height="7">
      <span class="active">Home</span> 
      <img src="images/square.gif" width="7" height="7"> <a href="<?=$CmsLinkBewBase . $ID?>" target="_parent">G&auml;ste-Bewertung/Kommentare</a>
      <img src="images/square.gif" width="7" height="7"> <a href="<?=$CmsLinkGalerieBase . $ID?>" target="_parent">Fotogalerie</a>
    </td>
  </tr>
  <tr valign="top"> 
    <td width="566" background="images/higru.jpg"> 
      <table border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="36" valign="top">&nbsp;</td>
          <td width="764" valign="top"> <br>

<?php viewItemDetail($ID, "detail"); ?>

            <br>

<?php $MBI=sachenView($ID); ?>

                  </table>
                </td>
              </tr>
            </table>
            <br>

<?php bewertungView($MBI, $ID); ?>

            <br>

<?php infoView($ID); ?>

            <br>

<?php anfahrtView($ID); ?>

            <br>

<?php gastBewertungView ($ID); ?>

              <br>
              <a href="<?=$CmsLinkDetailBase.$_REQUEST["ID"]?>#top" style="background-color:#4E5D15;"><img src="images/ico_pfeilauf.gif" width="13" height="13" alt="ganz aufi" border="0" align="absmiddle"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">ganz 
              aufi</font></a> </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?
if( !isset($_REQUEST["include"]) ) {
?>
</body>
</html>
<?
}
?>
