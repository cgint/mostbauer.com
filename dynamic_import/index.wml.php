<?php 
  require("itemView.php");
  require("config.php");
  
  header("Content-type: text/vnd.wap.wml");
  
  $WML= <<< EOB
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>
 <card id="home" title="Mostbauer.com" newcontext="true">
  <p align="center">
   <small>MostbauernEckn fuer Stodleit von Linz</small><br/>
   <img src="birn.wbmp" alt="Birne"/><br/>
  </p>
  <p align="left">

EOB;
  $WML.=viewWMLWeekDayChoice();
  logWAPClientAcccess(); 
  $WML.= <<< EOB
  </p>
 </card>
</wml>
EOB;
  echo $WML;
?>