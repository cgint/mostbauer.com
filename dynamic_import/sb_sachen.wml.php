<?php 
  require("itemView.php");
  require("config.php");
  
  $mostbauer=getItemNameFamName($ID);

  header("Content-type: text/vnd.wap.wml");
  
  if(isset($_GET["tag"]) && $_GET["tag"] != "" ) {
    $dayTargetLinkParameter="tag=".$_GET["tag"];
  }

  $WML= <<< EOB
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>
 <card id="home" title="$mostbauer" newcontext="true">
  <p align="center">
   <small>$mostbauer</small><br/>
   ---------<br/>
  </p>
  <p align="left">
<small>
EOB;
  $WML.=sachenViewWML($ID);
  $WML.= <<< EOB
</small>
  <a href="schmalzbrot.wml.php?ID={$ID}&amp;{$dayTargetLinkParameter}">Zurück zu den Details</a>
  </p>
 </card>
</wml>
EOB;
  echo $WML;
?>
