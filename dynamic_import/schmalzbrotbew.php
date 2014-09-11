<?php
  require("config.php");
  require("itemView.php");
  
  $stdEMail=getDbText("itemvote_stdEmail");
  $stdComment=getDbText("itemvote_stdComment");
  
  $cap_noNameError=getDbText("error_noName");
  $cap_noTextError=getDbText("error_noText");
  $cap_noLinksError=getDbText("error_noLinks");
  $noLinksNeedle="<a href";

  if(!($nameFamName=getItemNameFamName($ID))) die("Der Mostbauer mit der ID='$ID' ist nicht oder nicht mehr im System.");
  $name=getItemName($ID);
  

// Bewertung
if(isset($nurbewertung) || isset($beides)) {
  for($i=1; $i<=3; $i++) {
    $bewname="vote_$i";
    if(isset($$bewname) && $$bewname > 0) {
	   userVote($ID, $i, $$bewname);
	 }
  }
}

// Kommentar
$commentInserted=false;
if(isset($nurkommentar) || isset($beides)) {
  $commentError="";
  if(!isset($email) || $email == "" || $email == $stdEMail) $commentError=$cap_noNameError;
  else if(!isset($kommentar) || $kommentar == "" || $kommentar == $stdComment) $commentError=$cap_noTextError;
  else if(strpos($kommentar, $noLinksNeedle) !== FALSE || strpos($email, $noLinksNeedle) !== FALSE) $commentError=$cap_noLinksError;
  else {
    userComment($ID, $email, $kommentar);
	 $commentInserted=true;
  }
}

if(isset($commentError) && $commentError != "") $doComment=1;

?>

<?
if( !isset($_REQUEST["include"]) ) {
?>
<html>
<head>
<title>www.Mostbauer.com - Mostbauern Eckn f&uuml;r d' Stodleit - Bewertung: <?=$nameFamName?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body onload="if ( this == top ) document.location = '<?=$CmsLinkBewBase.$_REQUEST["ID"]?>';">
<?
 } else {
?>
<link rel="stylesheet" href="http://mostbauer.com/dynamic_import/styles.css" type="text/css">
<?
}
?>
<table width="700" border="0" cellpadding="0" cellspacing="0" bgcolor="">
  <tr valign="top"> 
    <td class="top_headline"><?=$name?><img src="images/ico_birne.gif" width="15" height="15" align="absmiddle">
      <img src="images/square-full.gif" width="7" height="7"><img src="images/square-full.gif" width="7" height="7"> 
      <a href="<?=$CmsLinkDetailBase . $ID?>" target="_parent">Home</a> 
      <img src="images/square.gif" width="7" height="7"> <span class="active">G&auml;ste-Bewertung/Kommentare</span>
      <img src="images/square.gif" width="7" height="7"> <a href="<?=$CmsLinkGalerieBase . $ID?>" target="_parent">Fotogalerie</a>
    </td>
  </tr>
  <tr valign="top"> 
    <td background="images/higru.jpg"> 
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="36" valign="top">&nbsp;</td>
          <td valign="top"> 
            <br> 
				
<?php viewItemDetail($ID, "vote"); ?>

<?php if(isset($doComment) && $doComment) viewItemDetailBewForm($ID, $kommentar, $email, $commentInserted, $commentError); else viewItemDetailBewResults($ID); ?>

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
