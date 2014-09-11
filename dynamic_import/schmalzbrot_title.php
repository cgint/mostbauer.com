<?php
  header('Content-Type: text/plain; charset=utf-8');
  require("itemView.php");
  require("config.php");

  $resultTitleText = "";
  if( isset($_REQUEST["ID"]) ) {
    $resultTitleText  = getItemNameFamName($_REQUEST["ID"]);

    // if( $_REQUEST["bauerPageType"] == "schmalzbrotdetail" ) $resultTitleText .= "nothing to add CG thinks";
    if( $_REQUEST["bauerPageType"] == "schmalzbrotbew" ) $resultTitleText .= " ::: Bewertung";
    if( $_REQUEST["bauerPageType"] == "schmalzbrotgalerie" ) $resultTitleText .= " ::: Fotogalerie";
  } else if( $_REQUEST["wochentag"] === "alle" ) {
    $resultTitleText = "Alle Mostbauern auf Mostbauer.com";

  } else if ( !isset($_REQUEST["wochentag"]) || $_REQUEST["wochentag"] == "heute" ) {
    $resultTitleText = "Alle Mostbauern, die heute offen haben";

  } else {
    $wochentagName = $weekdayStringArray[$_REQUEST["wochentag"]];
    $resultTitleText = "Alle Mostbauern, die am {$wochentagName} offen haben";

  }

  // need to replace - for joomla-title, else " - " gets stripped away
  // do NOT encode to utf-8, else joomla does it again
  $resultTitleText  = str_replace("-", "\-", $resultTitleText); 
  
  echo $resultTitleText;
?>
