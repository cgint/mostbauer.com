<?php

  require("itemView.php");

  if(!($nameFamName=getItemNameFamName($ID))) die("Der Mostbauer mit der ID='$ID' ist nicht oder nicht mehr im System.");

  $name=getItemName($ID);

?>

<?
if( !isset($_REQUEST["include"]) ) {
?>
<html>
<head>
<title>www.Mostbauer.com - Mostbauern Eckn f�r d' Stodleit - Detail: <?=$nameFamName?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="styles.css" type="text/css">
<link rel="stylesheet" href="lightbox/css/lightbox.css" type="text/css" media="screen">
<script src="lightbox/js/prototype.js" type="text/javascript"></script>
<script src="lightbox/js/scriptaculous.js?load=effects,builder" type="text/javascript"></script>
<script src="lightbox/js/lightbox.js" type="text/javascript"></script>
</head>
<body onload="if ( this == top ) document.location = '<?=$CmsLinkGalerieBase.$_REQUEST["ID"]?>';">
<?
 } else {
?>
<link rel="stylesheet" href="http://mostbauer.com/dynamic_import/styles.css" type="text/css">
<link rel="stylesheet" href="http://mostbauer.com/dynamic_import/lightbox/css/lightbox.css" type="text/css" media="screen">
<script src="http://mostbauer.com/dynamic_import/lightbox/js/prototype.js" type="text/javascript"></script>
<script src="http://mostbauer.com/dynamic_import/lightbox/js/scriptaculous.js?load=effects,builder" type="text/javascript"></script>
<script src="http://mostbauer.com/dynamic_import/lightbox/js/lightbox.js" type="text/javascript"></script>
<?
}
?>

<table width="700" border="0" cellpadding="0" cellspacing="0" bgcolor="">

  <tr valign="top"> 

    <td class="top_headline"><?=$name?><img src="images/ico_birne.gif" width="15" height="15" align="absmiddle">

      <img src="images/square-full.gif" width="7" height="7"><img src="images/square-full.gif" width="7" height="7"> 

      <a href="<?=$CmsLinkDetailBase . $ID?>" target="_parent">Home</a> 

      <img src="images/square.gif" width="7" height="7"> <a href="<?=$CmsLinkBewBase . $ID?>" target="_parent">G&auml;ste-Bewertung/Kommentare</a>

	  <img src="images/square.gif" width="7" height="7"> <span class="active">Fotogalerie</span>

    </td>

  </tr>

  <tr valign="top"> 

    <td background="images/higru.jpg"> 

      <table width="100%" border="0" cellspacing="0" cellpadding="0">

        <tr> 

          <td width="36" valign="top">&nbsp;</td>

          <td width="700" valign="top"> <br>



<?php viewItemGalleryWithLightbox($ID, $offset); ?>





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
