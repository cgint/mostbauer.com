<?php
   require("itemView.php");
?>

<html>
<head>
<title>www.Mostbauer.com - Mostbauern Eckn f&uuml;r d' Stodleit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#FFFFFF" link="#FFFFFF" vlink="#FFFFFF" alink="#006600">
<table width="764" border="0" cellpadding="0" cellspacing="0">
  <tr valign="top"> 
    <td height="71" width="799" valign="middle"> 
      <div align="left"><font face="Verdana, Arial, Helvetica, sans-serif" size="7" color="#FFFFFF"><img src="http://mostbauer.gewerbeweb.com/templates/dd_olive/images/banner.jpg"></font></div></td>
  </tr>
  <tr valign="top"> 
    <td width="799"> 
      <table width="800" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="30" valign="top">&nbsp;</td>
          <td width="764" height="1" valign="top">
<?php

global $tablePrefix;
$itemResult=mysql_query("SELECT * FROM ".$tablePrefix."bauer WHERE ID={$ID}") or die ("detail-karte.php; Database error (".mysql_error().")");
if($bauerArray=mysql_fetch_array($itemResult, MYSQL_ASSOC)) {
  $imgFile="images/karte/karte-{$ID}.gif";
  if(file_exists($imgFile)) {
    $imageData=getimagesize($imgFile);
	 $imgWidth=$imageData[0];
	 $imgHeight=$imageData[1];
	 $cardErrorMessage="";
  } else {
	 $imgWidth=0;
	 $imgHeight=0;
	 $cardErrorMessage="<font color=\"red\">Karte leider nicht verfügbar.</font>";
  }
  $bauerName=$bauerArray['name'].(($bauerArray['famName']=="")?"":" - {$bauerArray['famName']}");
  $wayDescription=$bauerArray['text'];

  echo <<< EOT

             <p> <br>
              <font face="Verdana, Arial, Helvetica, sans-serif" size="4"><b>(#{$ID}) {$bauerName}</b></font>
				  <font face="Verdana, Arial, Helvetica, sans-serif" size="2">
				  <br>
				  <br>
              <img src="{$imgFile}" width="{$imgWidth}" height="{$imgHeight}"> 
				  {$cardErrorMessage}
              <br>
              <br>
              {$wayDescription}
				  <br>
              <br>
              </font>
				 </p>
EOT;
} else {
  echo <<< EOT

             <p>
              <br>
              <br>
				  <font face="Verdana, Arial, Helvetica, sans-serif" size="2">ID not found!</font>
              <br>
              <br>
				 </p>
EOT;
}
?>
         </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr valign="top"> 
    <td height="2" width="799" bgcolor="#669900"> <div align="left"><font size="1"><img src="images/spacer.gif" width="10" height="10" align="absmiddle"><font face="Verdana, Arial, Helvetica, sans-serif">(c)2003 
        CGint &amp; Chris • www.Mostbauer.com</font></font> <font size="4" face="Verdana, Arial, Helvetica, sans-serif"></font></div></td>
  </tr>
</table>
</body>
</html>

