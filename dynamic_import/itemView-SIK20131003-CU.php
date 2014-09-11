<?php
require("config.php");
require("http.php");
// Select random line MySQL >= 3.23
// 
// "SELECT * FROM tablename ORDER BY RAND() LIMIT 1"
// 
//  passthrough image from php-file
// 
/* <?php
$size = getimagesize ($filename);
$fp=fopen($filename, "rb");
if ($size && $fp) {
  header("Content-type: {$size['mime']}");
  fpassthru($fp);
  exit;
} else {
  // error
}
?>
*/ 

  function getRandomWebCode () {
    $code="";
	 for($i=0; $i<10; $i++) {
	   $current = rand(0,61);
		if($current >= 10 && $current <= 35) $current=chr($current-10+ord('A'));
		if($current >  35)                   $current=chr($current-36+ord('a'));
		$code .= $current;
	 }
    return $code;
  }

  function getImageBaseDir () {
    global $imgBaseDir;
    return $imgBaseDir;

// rotate items in $imgBaseDirs-array randomly to rotate mirror-usage
// this is not good enough because one user gets a picture from one server and
// next time from the other. this is bad because it's against caching-algorithms
// we should do this once in a session ...

//    global $imgBaseDirs, $imgBaseDirCount;
//	 $randInt=rand(0,$imgBaseDirCount-1);
//	 return $imgBaseDirs[$randInt];
  }

  function getLangDialect () {
    return "ma"; // substr(getcwd(), -2); // "hd" or "ma", ...
  }

  function printDialectSwitch () {
    $myLocation=$_SERVER['REQUEST_URI'];
	 $myDialect=getLangDialect();
	 
	 if($myDialect == "ma") {
	   $otherLocation=str_replace("/ma/", "/hd/", $myLocation);
      echo <<< EOT
		<img src="images/ico_birne_ma.gif" alt="Mundoat (aktiviat)" width="15" height="15" align="absmiddle"><a href="{$otherLocation}"><img src="images/ico_birne_hd_grau.gif" alt="Hochdeitsch (umschoitn...)" width="15" height="15" border="0" align="absmiddle"></a>
EOT;
	 } else if($myDialect == "hd") {
	   $otherLocation=str_replace("/hd/", "/ma/",$myLocation);
      echo <<< EOT
		<a href="{$otherLocation}"><img src="images/ico_birne_ma_grau.gif" alt="Mundoat (umschoitn...)" width="15" height="15" border="0" align="absmiddle"></a><img src="images/ico_birne_hd.gif" alt="Hochdeitsch (aktiviat)" width="15" height="15"align="absmiddle">
EOT;
	 }

  }

  function getDbText ($text_id) {
    global $tablePrefix;
	 $dialect=getLangDialect();
	 
    $itemResult=mysql_query("SELECT {$dialect} FROM ".$tablePrefix."text WHERE text_id='{$text_id}'") or die ("itemView.php;getDbText(): Database error (".mysql_error().")");
    list($text)=mysql_fetch_row($itemResult);
	 return $text;
  }
  
  function getItemNameFamName ($ID) {
    global $tablePrefix;
	$sql = "SELECT name, famName FROM ".$tablePrefix."bauer WHERE ID=$ID";
    $itemResult=mysql_query($sql) or die ("itemView.php;getItemNameFamName(): Database error [{$sql}] (".mysql_error().")");
    list($name, $famName)=mysql_fetch_row($itemResult);
    if($famName != "") $famName=" - ".$famName;
	 return $name . $famName;
  }
  
  function getItemName ($ID) {
    global $tablePrefix;
    $itemResult=mysql_query("SELECT name FROM ".$tablePrefix."bauer WHERE ID=$ID") or die ("itemView.php;getItemName(): Database error (".mysql_error().")");
    list($name)=mysql_fetch_row($itemResult);
	 return $name;
  }
  
  function getVoteName ($voteID) {
    global $tablePrefix;
    $itemResult=mysql_query("SELECT name FROM ".$tablePrefix."gastbewertung_name WHERE ID=$voteID") or die ("itemView.php;getVoteName(): Database error (".mysql_error().")");
    list($name)=mysql_fetch_row($itemResult);
	 return $name;
  }
    
  function logWAPClientAcccess () {
    global $tablePrefix;
	 $agent=$_SERVER['HTTP_USER_AGENT'];
	 $ip=$_SERVER['REMOTE_ADDR'];
	 $referer=$_SERVER['HTTP_REFERER'];
	 $hostname=$_SERVER['REMOTE_HOST'];
    mysql_query("INSERT INTO ".$tablePrefix."wap_logging VALUES (NOW(), '{$agent}', '{$ip}', '{$referer}', '{$hostname}')") or die ("itemView.php;logWAPClientAcccess(): Database error (".mysql_error().")");
  }
  
  function logMobilClientAcccess () {
    global $tablePrefix;
	 $agent=$_SERVER['HTTP_USER_AGENT'];
	 $ip=$_SERVER['REMOTE_ADDR'];
	 $referer=$_SERVER['HTTP_REFERER'];
	 $hostname=$_SERVER['REMOTE_HOST'];
    mysql_query("INSERT INTO ".$tablePrefix."mobil_logging VALUES (NOW(), '{$agent}', '{$ip}', '{$referer}', '{$hostname}')") or die ("itemView.php;logMobilClientAcccess(): Database error (".mysql_error().")");
  }
  
  function viewWMLWeekDayChoice () {  
    global $weekdayStringArray;
    $weekDayTodayCode=date("w", getCETDateTime()); // 0 is sunday ... 6 is saturday
	 
	 $targetURL="index_tag.wml.php";
	 $output="";
	 $output.="<a href=\"{$targetURL}\">Alle Bauern</a><br/>\n";
	 $output.="Tageswahl:<br/>\n";
	 $output.="<a href=\"{$targetURL}?tag={$weekDayTodayCode}\">Heute</a><br/>\n";
	 $output.="<a href=\"{$targetURL}?tag=".($weekDayTodayCode+1)."\">Morgen</a><br/>\n";
	 for($i=1; $i < 6; $i++) {
	   $todayCode=$weekDayTodayCode+1+$i;
 	   $output.="<a href=\"{$targetURL}?tag={$todayCode}\">".$weekdayStringArray[($todayCode)%7]."</a><br/>\n";
	 }
	 return $output;
  }
  
  function createMostbauernDropdownList ($selectName, $behaviourType, $selectedId=FALSE, $allowedItems=FALSE) {
	global $CmsLinkOverview;
	//................................................................
	//Stellt den HTML Text zusammen fï¿½r ein Dropdown mit allen Bauern
	//abhï¿½ngig von $behaviourType reagiert das Ding
	//Einschrï¿½nkung auf bestimmte Bauern nicht mï¿½glich
	//"jo" -> inkl. javascript link zum anchor auf uebersichtsseite
	//"jd" -> inkl. javascript link direkt zur detailseite
	//"lo" -> generiert nur liste fï¿½r auswahl
	//erstellt 	07.01.2005	Chris
	//ï¿½nderungen	07.04.2005	Chris - neutraler Eintrag als default der Liste, da sonst der erste Eintrag nicht funktioniert
	//...............................................................

	$output="";
	//$output.="ABC".$behaviourType."----";

	if (($behaviourType == "jo")||($behaviourType == "jd")) {
	    //$output.="ABCD".$behaviourType."----";
	    $output.="\n<script language='JavaScript'>\n";
	    $output.="function changeHref(location1){\n";
	    $output.="if (location1!=null){\n";
//	    $output.="parent.location.href = location1;\n";
	    $output.="location.href = location1;\n";
	    $output.="this.selectedIndex=0\n";
	    $output.="}\n";
	    $output.="}\n";
	    $output.="</script>\n";
	
	    $output.="<select name=$selectName onChange='changeHref(this.options[selectedIndex].value)' class='dropdown'>";  
	}

	if ($behaviourType == "lo" || $behaviourType == "map") {
	    $output.="<select name=$selectName>";
	}
	if ($behaviourType == "jo") {
	      $passenderText = getDbText ("sbrot_dropdown1");
 	      $output.="<option value='#'>$passenderText</option>";
  		
	}


	//leerer Eintrag zu Beginn
	if ($behaviourType == "jd") {
	      $passenderTextBeiJd = getDbText ("sbrot_dropdown2");
 	      $output.="<option value='#' selected>{$passenderTextBeiJd}</option>";
	}

	//Betriebe durchlaufen
   	//$itemResultForDropdown=mysql_query("SELECT ID,name,famName, longitude FROM most_bauer ORDER BY name") or die ("Fehler beim Laden der Daten.");  
	
	$itemResultForDropdown=mysql_query("SELECT ID,name,famName, longitude FROM most_bauer WHERE status='1' ORDER BY name") or die ("Fehler beim Laden der Daten.");
	while(list($ID,$name,$famName, $longitude)=mysql_fetch_row($itemResultForDropdown)) {
	if ( $allowedItems !== FALSE && !array_key_exists($ID, $allowedItems) ) continue;	// skip entry if it was not selected
	
	$curSelectedString = "";
	$curBauerHasMapCoord = "";
	if ($behaviourType == "jd") {
 	      $output.="<option value='./schmalzbrotdetail.php?ID=$ID'>$name ($famName)</option>";
	}
	if ($behaviourType == "jo") {
	      $entryUrl = "";
	      if( isset($_REQUEST["include"]) ) {
		$entryUrl = $CmsLinkOverview;
	        if( isset($_REQUEST["wochentag"]) && $_REQUEST["wochentag"] != "heute" ) $entryUrl .= "&wochentag=" . $_REQUEST["wochentag"];
	      }
 	      $output.="<option value='{$entryUrl}#$ID'>$name ($famName)</option>";
	}
	if ($behaviourType == "lo" || $behaviourType == "map") {
		  if ( $selectedId == $ID ) $curSelectedString = " selected";
		  if ( $behaviourType == "map" && $longitude != "" ) $curBauerHasMapCoord = " *";
		  $entryDescription = htmlentities("$name ($famName)");
 	      $output.="<option value='$ID'$curSelectedString>$entryDescription$curBauerHasMapCoord</option>";
	}

	}//while betrieb
       $output.="</select>";

	return $output;
  }

	function htmlizeUtf8($value) {
		return str_replace("'", "&#39;", htmlentities($value));
	}
  
	function cleanPhoneNum($value) {
		$value = str_replace(" ", "", $value);
		$value = str_replace("/", "", $value);
		$value = str_replace("-", "", $value);
		$value = str_replace(".", "", $value);
		return $value;
	}
	
  function viewItemDetailWML ($ID, $viewType) { // viewType "list", "detail" or "vote"
    global $tablePrefix;
	 $output="";
    if(isset($_GET["tag"]) && $_GET["tag"] != "" ) {
      $dayTargetLinkParameter="&#38;tag=".$_GET["tag"];
	 }

    $itemResult=mysql_query("SELECT * FROM ".$tablePrefix."bauer WHERE ID=$ID") or die ("itemView.php;viewItemDetail(): Database error (".mysql_error().")");
    if(!(list($id, $name, $famName, $wegBeschr, $bild1, $bild2, $web, $email, $telefon, $adresse, $regionID, $zeiten, $strCoord, $wandCoord, $status, $infoText, $infoText2)=mysql_fetch_row($itemResult))) {
      die("Item with ID=$ID not found!");
    }
	 if($famName != "") $famName=" - ".$famName;
	 if($viewType == "list") $output.="    <a href=\"schmalzbrot.wml.php?ID={$id}{$dayTargetLinkParameter}\">$name</a><br/>\n"; //   
	 if($viewType == "detail") {
//	   $output.="    $name - $famName<br/>\n";
	   $output.="    +offen: $zeiten<br/>\n";
	   $output.="    +adr: $adresse<br/>\n";
	   $output.="    +tel: $telefon<br/>\n";
	   $output.="    +web: $web<br/>\n";
	   $output.="    +info: $infoText<br/>\n";
	   $output.="    +anfahrt: $wegBeschr<br/>\n";
	 }
	 return $output;
  }
  
  function sachenViewWML ($ID) {
    global $tablePrefix;
	 $output="";
     $output.="name/bild/preis/menge/geschmack<br/>";

    $sachenMax=15;

    $dunno="---";
	 $sachenCount=0;
	 $sacheNameResult=mysql_query("SELECT ID, name FROM ".$tablePrefix."sachen_name ORDER BY ID") or die ("itemView.php;sachenView(): Database error (".mysql_error().")");
	 while(list($sacheID, $sacheName)=mysql_fetch_row($sacheNameResult)) {
     $sacheResult=mysql_query("SELECT preis, menge, geschmack, bild FROM ".$tablePrefix."sachen WHERE itemID=$ID AND sacheID=$sacheID") or die ("itemView.php;sachenView(): Database error (".mysql_error().")");
     if(!(list($preis, $menge, $geschmack, $bild)=mysql_fetch_row($sacheResult))) {
   	     $preis=$dunno;
		 $menge=$dunno;
		 $geschmack=$dunno;
		 $bild="";
     }
	  if(!$preis) $preis=$dunno; 
      if($preis != $dunno) $preis=sprintf("%1.2f", $preis);

	  if(!$menge) $menge=$dunno;
	  if(!$geschmack) $geschmack=$dunno;
	  if(!$bild) $bild="N"; else $bild="J";
	  
	  $output.="{$sacheName}/{$bild}/{$preis}/{$menge}/{$geschmack}<br/>";
	 }
	 return $output;
  }
  
  
  function viewRegionSplit ($regionName) {
echo <<< EOT
            <table border="0" cellspacing="0" cellpadding="0" bgcolor="#98b3f2">
              <tr valign="top"> 
                <td> 
                  <table width="700" border="0" cellpadding="0" cellspacing="1" bgcolor="#99cc33">
			<TR bgColor=#669900> 
                      <td colspan="2"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=4><IMG height=10 src="images/spacer.gif" width=10 border=0><B><FONT color=#000000>Most-Region</FONT></B></FONT></td>
                      </tr>
			<tr bgcolor="#99cc33"> 
                      <td valign="top"> 
                        <div align="left"><font face="Verdana, Arial, Helvetica, sans-serif" size="4"><img src="images/spacer.gif" width="10" height="10" border="0">
EOT;
echo "<b><font color=\"#000000\">{$regionName}&nbsp;</font></b>";
echo "</font></div>";
echo "</td>";
echo "<TD vAlign=top><img height=60 src='images/banner/$regionName.gif' width=468 align=right></font></TD>";								
echo <<< EOT
                    </tr>
                  </table>

                </td>
              </tr>
            </table>
EOT;
  }
  
  function viewItemDetailLinklist ($ID){ // 
 	 $cap_overview=getDbText("bauer_overview");
	 $cap_toOverview=getDbText("bauer_toOverview");
	 $cap_detail=getDbText("bauer_detail");
	 $cap_toDetail=getDbText("bauer_toDetail");
        $cap_erreichbar=getDbText("itemdetail_reachable");

        global $tablePrefix;
        $itemResult=mysql_query("SELECT * FROM ".$tablePrefix."bauer WHERE ID={$ID}") or die ("itemView.php;viewItemDetail(): Database error (".mysql_error().")");
        if(!(list($id, $name, $famName, $wegBeschr, $bild1, $bild2, $web, $email, $telefon, $adresse, $regionID, $zeiten, $strCoord, $wandCoord, $status, $infoText, $infoText2, $karte)=mysql_fetch_row($itemResult))) {
          die("Item with ID=$ID not found!");
        }

	 echo "$name";


  }





 
 function getHomeAdressFromUser($userID) {

 
 /*   global $my;
    $my->id;
    echo "++{$my}++";
 
  if (!$user->guest) {
       echo 'You are logged in as:<br />';
       echo 'User name: ' . $my->username . '<br />';
       echo 'Real name: ' . $my->name . '<br />';
       echo 'User ID  : ' . $my->id . '<br />';
    }

 */

/* ja scheisse - die Session vom Joomla ist hier nicht sichtbar. so ein dreck...

echo("-1-");
var_dump($_session);
echo("-2-");
*/

    $parameter="Hauptplatz, 4020 Linz";
    return $parameter;

  
  }







  function viewItemDetail ($ID, $viewType) { // viewType "list", "detail" or "vote"
    $imageBaseDir=getImageBaseDir();
	 
	 $cap_overview=getDbText("bauer_overview");
	 $cap_toOverview=getDbText("bauer_toOverview");
	 $cap_detail=getDbText("bauer_detail");
	 $cap_toDetail=getDbText("bauer_toDetail");
    $cap_erreichbar=getDbText("itemdetail_reachable");
	 
    global $tablePrefix;
    $itemResult=mysql_query("SELECT * FROM ".$tablePrefix."bauer WHERE ID={$ID}") or die ("itemView.php;viewItemDetail(): Database error (".mysql_error().")");
    if(!(list($id, $name, $famName, $wegBeschr, $bild1, $bild2, $web, $email, $telefon, $adresse, $regionID, $zeiten, $strCoord, $wandCoord, $status, $infoText, $infoText2, $karte, $passwort, $latitude, $longitude, $anfahrtpolygon, $urlfacebook, $urlgoogleplus, $Bild1copyright, $Bild2copyright, $lastupdate, $lastupdatewho)=mysql_fetch_row($itemResult))) {
      die("Item with ID=$ID not found!");
    }

	//Öffnungszeiten/Tage/Infos lesen
    $itemResultTage=mysql_query("SELECT * FROM ".$tablePrefix."tage WHERE bauerID={$ID}") or die ("itemView.php;viewItemDetail(): Database error (".mysql_error().")");
    if(!(list($bauerID, $mo, $di, $mi, $do, $fr, $sa, $so, $feiertag, $nurbeischoenwetter, $hatwaszumdrinsitzen)=mysql_fetch_row($itemResultTage))) {
      //die("Item with ID=$ID not found!");
      //wenn keine einträge für die bauernid vorhandensiund, dann alle werte leer setzen 
	  $mo="";
	  $di="";
	  $mi="";
	  $do="";
	  $fr="";
	  $sa="";
	  $so="";
	  $feiertag = "";
	  $nurbeischoenwetter = "";
	  $hatwaszumdrinsitzen = "";
	  $urlfacebook = "";
	  $urlgoogleplus = "";
	  	
	}

	if ($nurbeischoenwetter == "Y"){
		//echo "Nur bei Schönwetter!";
		$ico_nurbeischoenwetter="<img src='images/ico_nur-bei-schoenwetter.gif' title='Nur bei Schönwetter offen!'>";
	}
	
	if ($hatwaszumdrinsitzen == "Y"){
		//echo "Nur bei Schönwetter!";
		$ico_hatwaszumdrinsitzen="<img src='images/ico_hatwaszumdrinsitzen.gif' title='Hat was zum drin sitzen.'>";
	}
	
	//Bildercopyrigt zusammenbauen
	if ($Bild1copyright !="") $Bild1copyright="&copy;  ".$Bild1copyright;
	if ($Bild2copyright !="") $Bild2copyright="&copy;  ".$Bild2copyright;
	//echo "-1".$Bild1copyright."-2".$Bild2copyright."*";
	

echo <<< EOT

	     <a name="$id"></a>				
            <table border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
              <tr valign="top"> 
                <td> 
                  <table width="700" border="0" cellpadding="0" cellspacing="1" bgcolor="#669900">
                    <tr bgcolor="#669900"> 
                      <td valign="middle" height="30"  colspan="2"> 
                        <div align="left"><font face="Verdana, Arial, Helvetica, sans-serif" size="4"><img src="images/spacer.gif" width="10" height="10" border="0">
EOT;
global $CmsLinkOverview, $CmsLinkDetailBase, $CmsLinkGalerieBase, $CmsLinkMapBase, $CmsLinkHaftung, $CmsLinkInfo;

echo "&quot;";
if($viewType=="list") echo "<a href=\"{$CmsLinkDetailBase}{$id}\" target=\"_parent\">";
echo "<b><font color=\"#000000\">$name</font></b>";
if($viewType=="list") echo "</a>";
echo "&quot;";
if($famName != "") $famName=" - ".$famName;
echo "$famName</font></div>";
echo <<< EOT


                      </td>
                      <td width="200" valign="middle"> 
                        <div align="right"> 
EOT;
if($viewType=="detail" || $viewType=="vote") echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\"><a href=\"{$CmsLinkOverview}\" target=\"_parent\"><font color=\"black\">{$cap_overview}</font></a></font>&nbsp;<a href=\"{$CmsLinkOverview}\" target=\"_parent\"><img src=\"images/ico_wirtuebersicht.gif\"   border=\"0\" title=\"{$cap_toOverview}\" align=\"absmiddle\"></a>&nbsp;";
if($viewType=="vote") echo "&nbsp;";
if($viewType=="list" || $viewType=="vote") echo "<a href=\"{$CmsLinkDetailBase}{$id}\" target=\"_parent\"><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\" color=\"black\">{$cap_detail}</font></a>&nbsp;<a href=\"{$CmsLinkDetailBase}{$id}\" target=\"_parent\"><img src=\"images/ico_genaues.gif\"  border=\"0\" title=\"{$cap_toDetail}\" align=\"absmiddle\"></a>&nbsp;";

$saisonText = getSaisonText($id);

echo <<< EOT

                          <font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b></b></font></div>
                      </td>
                    </tr>
                    <tr> 
						<td width="200" valign="top" rowspan="2">
							<img src="{$imageBaseDir}{$bild1}" title="{$Bild1copyright}" width="200" height="150">
						</td>
						<td width="100%" valign="top" bgcolor="#99CC33" height="0">
						<!-- CU 28.3.2013 - ausgeblendet um Platz für "Saison:" zu schaffen
							<img src="images/spacer.gif" width="10" height="10" border="0"> 
							<font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							<a href="{$CmsLinkInfo}" target="_blank"><img src="images/ico_info.gif" width="13" height="13" border="0" title="Erklärung der Zeichen"></a><b>{$cap_erreichbar}:</b>
							</font>-->
						</td>
						
						<td width="200" valign="top" rowspan="2"><img src="{$imageBaseDir}{$bild2}" title="{$Bild2copyright}" width="200" height="150">
						</td>				  
                     
					</tr>
                    <tr>
						<td width="100%" valign="middle" bgcolor="#CCCC99" height="107"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							<img src="images/spacer.gif" width="10" height="10"><img src="images/ico_uhr.gif" title='Öffnungszeiten' width="15" height="15">$ico_nurbeischoenwetter$ico_hatwaszumdrinsitzen&nbsp;$zeiten<br>
							<img src="images/spacer.gif" width="10" height="10"><img src="images/ico_saison.gif" title='Saisonale Öffnungszeit' width="15" height="15">&nbsp;$saisonText<br>
							<img src="images/spacer.gif" width="10" height="10"><img src="images/ico_tel.gif" width="15" height="15">&nbsp;$telefon<br>
							<img src="images/spacer.gif" width="10" height="10"><img src="images/ico_adresse.gif" width="15" height="15">&nbsp;$adresse<br> 
EOT;
			// kommentare vorbereiten
			 $CmsLinkBewBase = "{$CmsBaseLink}index.php?option=com_wrapper&Itemid=2&bauerPageType=schmalzbrotbew&bauerID=";
  			 //echo "CmsLinkBewBase:{$CmsLinkBewBase}";
			 
			 $dirName="images/galerie/{$ID}/";
			 
			 if(!file_exists($dirName)){
			    //echo"<img src='images/spacer.gif' width='10' height='10'><img src='images/ico_galerie_nix.gif' width='15' height='15'>&nbsp;<font color='black'>keine Galerie</font>&nbsp;";
			    echo"<img src='images/spacer.gif' width='10' height='10'><img src='images/ico_galerie_nix.gif' border=0 width='15' height='15' title='Leider keine Galeriebilder vorhanden.' >&nbsp;";
			    echo"<A href='{$CmsLinkBewBase}{$ID}#bewliste'><img src='images/ico_kommentare.gif' border=0 align='abmiddle' title='direkt zu den Gäste-Kommentaren...'></A>";
		   	 	  }else{
			    //echo"<img src='images/spacer.gif' width='10' height='10'><img src='images/ico_webadresse.gif' width='15' height='15'>&nbsp;<a href='{$CmsLinkGalerieBase}{$ID}' target='_parent'><font color='blue'>Galerie</font></a>&nbsp;";
			    echo"<img src='images/spacer.gif' width='10' height='10'><a href='{$CmsLinkGalerieBase}{$ID}' target='_parent'><img src='images/ico_webadresse.gif' width='15' height='15' border=0 title='Zur Galerie' >&nbsp;";
			    echo"<A href='{$CmsLinkBewBase}{$ID}#bewliste'><img src='images/ico_kommentare.gif' border=0 align='abmiddle' title='Direkt zu den Gäste-Kommentaren...'></A>";
			  }
			  if ($web!=""){
			     $ico_internet="ico_internet.gif";
			  	 echo"&nbsp;<a href='http://$web' target='_blank'><font color='blue'><img src='images/{$ico_internet}' border=0 align='absmiddle' alt='Internetadresse'>$web</font></a>";
			  } else {
				$ico_internet="ico_internet_grau.gif";
			    echo"&nbsp;<img src='images/{$ico_internet}' align='absmiddle' title='Keine Internetadresse verfügbar' border=0 alt='Keine Internetadresse verfügbar'>";
			  }
			  
			  //echo"&nbsp;<a href='http://$web' target='_blank'><img src='images/{$ico_internet}' align='absmiddle' alt='$web'></a>";
			  
			  if ($email!=""){
			     echo"&nbsp;<a href='mailto:$email' target='_blank'><font color='blue'><img src='images/ico_email.gif' border=0 align='absmiddle' title='E-Mail an den Bauern senden' alt='e-mail'></a>";
			  }
		   
			  if ($urlfacebook!=""){
			     echo"&nbsp;<a href='$urlfacebook' target='_blank'><font color='blue'><img src='images/ico_urlfacebook.gif' border=0 align='absmiddle' title='Zur facebookseite des Bauern' alt='facebookseite'></a>";
			  }
		   


// prepare karte-info
global $CmsKarteLinks, $CmsLinkDetailBase;
//$karteLinkOverview = $CmsKarteLinks["overview"];
//$karteLinkDetail   = $CmsKarteLinks[$karte];
//
//			 <img src="images/spacer.gif" width="10" height="10"><img src="images/ico_karte.gif" width="15" height="15" align="absmiddle">&nbsp;<a href="{$karteLinkOverview}" target="_parent"><font color="blue">&Uuml;bersichtskarte</font></a>
//			 <br>
//			 <img src="images/spacer.gif" width="10" height="10"><img src="images/zoom.gif" width="15" height="15" align="absmiddle">&nbsp;#{$ID}&nbsp;<a href="{$karteLinkDetail}" target="_parent"><font color="blue">Detailkarte</font></a>
//			 <img src="images/spacer.gif" width="10" height="10">
//				<img src="images/zoomzoom.gif" width="16" height="16" align="absmiddle">&nbsp;
//				<a href="detail-karte.php?ID={$ID}" target="_blank"><font color="blue">Anfahrt</font></a></font>
//
//
//                      </td>
//                    </tr>
		      

// Anfahrt 
if($viewType=="detail") {
  echo "<BR><img src='images/spacer.gif' width='10' height='10' border='0'><img src='images/zoomzoom.gif' width='15' height='15'>&nbsp;<a href='{$CmsLinkDetailBase}{$ID}#anfahrt'><font color='blue'>Anfahrt</font></A>"; 
}else{
  // echo "<BR><img src='images/spacer.gif' width='10' height='10' border='0'><img src='images/zoomzoom.gif' width='15' height='15'>&nbsp;Anfahrt siehe <a href='{$CmsLinkDetailBase}{$id}' target='_parent'><font color='blue'>Detailseite</font></A>";
}

echo "<form name='search_routegoogle{$ID}' method='get' action='http://maps.google.de/' target='_blank'>";
echo "<img src='images/spacer.gif' width='10' height='10' border='0'><img src='images/ico_karte.gif' width='15' height='15'>&nbsp;<a href='{$CmsLinkMapBase}{$ID}' target='_parent'><font color='blue'>Bauer in Karte zeigen</font></A>";
//route berechnen
$startAdresse = getHomeAdressFromUser("214");
//$startAdresse="Hauptplatz, 4020 Linz";

echo "<input name='saddr' type='hidden' id='saddr' value='{$startAdresse}'>";
echo " &nbsp; ";
echo "<input name='daddr' type='hidden' id='daddr' value='{$adresse}'>";
echo "<input type='image' src='images/i	co_karte_g.gif' id='SUBMIT1' name='SUBMIT1' align='absmiddle'>";
echo " <a href='#' onClick='document.search_routegoogle{$ID}.submit();return false;'><font color=blue> Route zeigen</font></a>";
echo "</form>";
echo "<font size='1'>";

echo "<img src='images/spacer.gif' width='10' height='10' border='0'><a href='mailto:mona@mostbauer.com?subject=Achtung: Die Angaben zum {$name} sind nicht mehr aktuell !'><img src='images/ico_wronginfo.gif' align='absmiddle' title='Daten falsch? Hier einfach klicken und dem Mostbauer.com Team melden...' alt='Daten falsch?' border=0>";
echo "&nbsp;&nbsp;<font color='black'>Daten falsch?</font></A>";
if ( $lastupdate!='0000-00-00' ) {
	//echo "Aktualisert {$lastupdate} ({$lastupdatewho})";
	echo "&nbsp;&nbsp;(aktualisiert am {$lastupdate})";
}

			  
echo <<< EOT
                    <tr bgcolor="#cccc99"> 
                      <td colspan="3" valign="top"><img src="images/spacer.gif" width="10" height="10">
							  <img src="images/ico_wichtig.gif" width="13" height="13" align="absmiddle">
							  <font face="Verdana, Arial, Helvetica, sans-serif" size="2">$infoText</font>
							 </td>
                    </tr>



EOT;
	if($viewType=="detail" || $viewType=="vote"){
echo <<< EOT

	     
		      <TR bgColor=#FFFFFF> 
                              <TD vAlign=top colSpan=3> <TABLE cellSpacing=0 cellPadding=0 width="100%">
                                  <TBODY>
                                    <TR> 
                                      <TD height=46><IMG height=10 
                              src="images/spacer.gif" 
                              width=15> <FONT 
                              face="Verdana, Arial, Helvetica, sans-serif" 
                              size=2>&nbsp;</FONT></TD>
                                      <TD vAlign=top><FONT 
                              face="Verdana, Arial, Helvetica, sans-serif" 
                              color=#cccc99 size=1>Die unten angef&uuml;hrten Gr&ouml;&szlig;enangaben 
                                        der Speisen sowie deren Geschmack sind 
                                        rein subjektiv und auf einen bestimmten 
                                        Zeitpunkt bezogen. Auch die von uns angebotenen 
                                        Abbildungen der Speisen sind als Momentaufnahme 
                                        zu betrachten. N&auml;heres zum </FONT><FONT 
                              size=1 
                              face="Verdana, Arial, Helvetica, sans-serif"><A 
                              href="{$CmsLinkHaftung}" target="_parent"><font color="#cccc99">Haftungsausschlu&szlig;</font></A><font color="#cccc99"><A 
                              href="{$CmsLinkHaftung}" target="_parent"></A></font></FONT><FONT 
                              face="Verdana, Arial, Helvetica, sans-serif" 
                              color=#cccc99 size=1>.<BR>
                                        </FONT></TD>
                                      <TD><IMG height=10 src="images/spacer.gif" width=10> <FONT face="Verdana, Arial, Helvetica, sans-serif" size=2>&nbsp;</FONT></TD>
                                    </TR>
                                  </TBODY>
                                </TABLE></TD>
                    </TR>

EOT;
		}
echo <<< EOT

                  </table>
                </td>
              </tr>
            </table>
			
			<!--
			<font face="Verdana, Arial, Helvetica, sans-serif" size="2">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Weitersagen: &nbsp;&nbsp;&nbsp;
			<a href="../index.php?option=com_wmessenger&Itemid=38&empf__sug_farmer={$ID}&empf_message=Hallo,schau%20Dir%20den%20Bauern%20an:%20{$name}!" TARGET="_parent"><img src="../images/ico_emailempfehlung.gif" align="absmiddle" title="Diesen Bauern jemand empfehlen..."></A>
			</font>
			-->
			<BR>
		
EOT;


	if($viewType=="detail" || $viewType=="vote"){
		$infoText2 = nl2br($infoText2);
echo <<< EOT

		<font face="Verdana, Arial, Helvetica, sans-serif" size="2">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Weitersagen: &nbsp;&nbsp;&nbsp;
		<a href="../index.php?option=com_wmessenger&Itemid=38&empf__sug_farmer={$ID}&empf_message=Hallo,schau%20Dir%20den%20Bauern%20an:%20{$name}!" TARGET="_parent"><img src="../images/ico_emailempfehlung.gif" align="absmiddle" title="Diesen Bauern jemand empfehlen..." style="border: none;"></A>
		</font>


      <a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.pp">TEILEN</a>
      <script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
		<BR><BR><TABLE cellSpacing=0 cellPadding=0 width=700 bgColor=#339933 border=0>
              <TBODY>
                <TR vAlign=top> 
                  <TD> <TABLE cellSpacing=1 cellPadding=0 width=700 border=0>
                      <TBODY>
                        <TR bgColor=#99CC33> 
                          <TD width=700 bgColor=#66CC33> <FONT face="Verdana, Arial, Helvetica, sans-serif" size=2><IMG height=10 src="images/spacer.gif" width=10 border=0><B>Was der Bauer selber sagt</B></FONT>
				<a href="{$CmsLinkInfo}#bauer" target="_blank"><img src="images/ico_wasderbauerselbersagt.gif" align="absmiddle" border="0"></a>
			    </TD>
                        </TR>
                        <TR bgColor=#99CC33> 
                          <TD vAlign=top bgColor=#BCFF79> <TABLE cellSpacing=0 cellPadding=0 width="100%">
                              <TBODY>
                                <TR> 
                                  <TD vAlign=top><IMG height=10 src="images/spacer.gif" width=10 border=0></TD>
                                  <TD vAlign=top><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2>$infoText2</FONT><BR></TD>
                                  <TD><IMG height=10 src="images/spacer.gif" width=10> 
				     </TD>
                                </TR>
                              </TBODY>
                            </TABLE></TD>
                        </TR>
                      </TBODY>
                    </TABLE></TD>
                </TR>
              </TBODY>
            </TABLE>
EOT;
	}


  }
  
  function sachenView ($ID) {
    global $tablePrefix, $CmsLinkInfo;
    $imageBaseDir=getImageBaseDir();


	 
	 $cap_mbi=getDbText("sachen_mbi");
	 $cap_info=getDbText("sachen_info");
	 $cap_price=getDbText("sachen_price");
	 $cap_amount=getDbText("sachen_amount");
	 $cap_flavor=getDbText("sachen_flavor");
	 $cap_explain=getDbText("sachen_explain");
	 $cap_overall=getDbText("sachen_overall");

	 $marksAndMBI = calcMarksAndMBIForItem($ID);

  echo <<< EOT
	       
	     <BR>

		<table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
              <tr valign="top"> 
                <td height="3"> 
                  <table width="700" border="0" cellspacing="1" cellpadding="0">
                    <tr bgcolor="#99CC33"> 
                      <td colspan="5"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><img src="images/spacer.gif" width="10" height="10" border="0"><b>{$cap_mbi}</b></font>
				<a href="{$CmsLinkInfo}#MBI" target="_blank"><img src="images/ico_info.gif" width="13" height="13" alt="{$cap_info}" border="0"></a></td>
                    </tr>
                    <tr bgcolor="#cccc99"> 
                      <td width="209" height="15"><img src="images/spacer.gif" width="10" height="10" border="0"></td>
                      <td width="65" height="15"><div align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">{$cap_price}</font></div></td>
                      <td width="109" height="15"><div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">{$cap_amount}</font></div></td>
                      <td width="84" height="15"><div align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">{$cap_flavor}</font></div></td>
                      <td width="233" height="15"><div align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">{$cap_explain}</font></div></td>
                    </tr>
EOT;
    $sachenMax=15;



    $dunno="---";
	 $MBImult=0;
	 $MBIadd=0;  
	 $sachenCount=0;
	 $sacheNameResult=mysql_query("SELECT ID, name FROM ".$tablePrefix."sachen_name ORDER BY ID") or die ("itemView.php;sachenView(): Database error (".mysql_error().")");
	 while(list($sacheID, $sacheName)=mysql_fetch_row($sacheNameResult)) {
     $sacheResult=mysql_query("SELECT preis, menge, geschmack, bild FROM ".$tablePrefix."sachen WHERE itemID=$ID AND sacheID=$sacheID") or die ("itemView.php;sachenView(): Database error (".mysql_error().")");
     if(!(list($preis, $menge, $geschmack, $bild)=mysql_fetch_row($sacheResult))) {
   	 $preis=$dunno;
		 $menge=$dunno;
		 $geschmack=$dunno;
		 $bild="";
     }
	  if(!$preis) $preis=$dunno; 
          if($preis != $dunno) $preis=sprintf("%1.2f", $preis);

	  if(!$menge) $menge=$dunno;
	  if(!$geschmack) $geschmack=$dunno;
	  
          $preisnote="";
          if($marksAndMBI["{$sacheID}_preisnote"]) {
            $preisnote=" <font color=gray>(" . $marksAndMBI["{$sacheID}_preisnote"] .")</font>";
          }

	  if($marksAndMBI[$sacheID]) {
	    $brotLaenge=60*(5-$marksAndMBI[$sacheID]);
		 $brotMsg="&nbsp"; // tiny bread is shown in brot-cell if mark is 5
	  } else {
	    $brotLaenge=0;
		 $brotMsg=""; // nothing is shown in brot-cell
	  }
  echo <<< EOT
                  	 <tr valign="center" bgcolor="#CCCC99"> 
                     	<td width="209" height="2"><img src="images/spacer.gif" width="10" height="10">
EOT;
  if($bild == "")
	 echo "<img src=\"images/ico_foto_nix.gif\" width=\"15\" height=\"15\">";
  else
	 echo "<a href=\"{$imageBaseDir}{$bild}\" onmouseover=\"showSachenImage('{$imageBaseDir}{$bild}');\" onmouseout=\"hideSachenImage();\" target=\"_sacheBild\"><img src=\"images/ico_foto.gif\" width=\"15\" height=\"15\" border=\"0\"></a>";
  echo <<< EOT
								 <font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$sacheName}</b></font>
								</td>
                     	<td width="65" height="2"><div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><nobr>{$preis}{$preisnote}</nobr></font></div></td>
                     	<td width="109" height="2"><div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$menge}</font></div></td>
                     	<td width="84" height="2"><div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$geschmack}</font></div></td>
                     	<td width="233" height="2"> 
                          <table width="{$brotLaenge}" border="0" cellspacing="0" cellpadding="0" background="images/brot-balken2.gif">
                        	 <tr> 
                           	<td>{$brotMsg}</td>
                        	 </tr>
                          </table>
                     	</td>
                  	 </tr>

EOT;
	 }
	 if($marksAndMBI['MSI']) {
	   $MBI=$marksAndMBI['MSI'];
	   $brotLaenge=60*(5-$MBI);
		$brotMsg="&nbsp"; // tiny bread is shown in brot-cell if mark is 5
	 } else {
	   $MBI="---";
      $brotLaenge=0;
	   $brotMsg=""; // nothing is shown in brot-cell
	 }

  echo <<< EOT
                    <tr valign="top" bgcolor="#CCCC99"> 
                      <td width="209"> 
                        <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Speisen-Index</b></font><img src="images/spacer.gif" width="10" height="10"></div>
                      </td>
                      <td width="65"> 

                        <div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$MBI}</b></font></div>
                      </td>
                      <td width="109"> 
                        <div align="center"><img src="images/spacer.gif" width="10" height="10"></div>
                      </td>
                      <td width="84"> 
                        <div align="center"><img src="images/spacer.gif" width="10" height="10"></div>
                      </td>
                      <td width="233"> 
                          <table width="{$brotLaenge}" border="0" cellspacing="0" cellpadding="0" background="images/brot-balken2.gif">
                        	 <tr> 
                           	<td>{$brotMsg}</td>
                        	 </tr>

                          </table>
                      </td>
                    </tr>
EOT;
	 return $marksAndMBI;
  }
  
  function bewertungView ($marksAndMBI, $ID) {
    global $tablePrefix, $CmsLinkInfo;
	 
	 $cap_howGood=getDbText("sachen_howGood");
	 $cap_info=getDbText("sachen_info");
 	 $cap_overall=getDbText("sachen_overall");
 	 $cap_good=getDbText("sachen_good");
 	 $cap_explain=getDbText("sachen_explain");
	 



	 $overallSum=0;
	 $overallCount=0;
	 
	 $MBI=$marksAndMBI['MSI'];

    if($MBI) {
	   $MBIbrotLaenge=60*(5-$MBI);  
		$brotMsg="&nbsp"; // tiny bread is shown in brot-cell if mark is 5
		$overallCount++;
		$overallSum+=$MBI;
	 } else {
	   $MBIbrotLaenge=0;
		$brotMsg=""; // nothing is shown in brot-cell
	 }
  
  echo <<< EOT
            <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
              <tr valign="top"> 
                <td height="95"> 
                  <table width="700" border="0" cellspacing="1" cellpadding="0">
                    <tr bgcolor="#99CC33"> 
                      <td colspan="3"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><img src="images/spacer.gif" width="10" height="10" border="0"><b>{$cap_howGood}</b></font>
							  <a href="{$CmsLinkInfo}#wgiw" target="_blank"><img src="images/ico_info.gif" width="13" height="13" alt="{$cap_info}" border="0"></a></td>
                    </tr>
                    <tr bgcolor="#cccc99"> 
                      <td width="184" height="15">&nbsp;</td>
                      <td width="127" height="15"><img src="images/spacer.gif" width="10" height="10" border="0">
							  <font size="1" face="Verdana, Arial, Helvetica, sans-serif">{$cap_good}</font>
							 </td>
                      <td width="382" height="15"><img src="images/spacer.gif" width="10" height="10" border="0">
							  <font size="1" face="Verdana, Arial, Helvetica, sans-serif">{$cap_explain}</font>
							 </td>

                    </tr>
EOT;

  echo <<< EOT
                    <tr valign="center" bgcolor="#CCCC99"> 
                      <td width="184" height="2"> 
                        <div align="left"><img src="images/spacer.gif" width="10" height="10"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Speisen-Index</b></font></div>
                      </td>
                      <td width="127" height="2"><img src="images/spacer.gif" width="10" height="10"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$MBI}</font></td>
                      <td width="382" height="2"> 
                        <table width="{$MBIbrotLaenge}" border="0" cellspacing="0" cellpadding="0" background="images/brot-balken.gif">
                          <tr> 
                            <td>{$brotMsg}</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
EOT;

  $bewertungNameResult=mysql_query("SELECT ID, name FROM ".$tablePrefix."bewertung_name ORDER BY ID") or die ("itemView.php;bewertungView(): Database error (".mysql_error().")");
  while(list($bewertungID, $bewertungName)=mysql_fetch_row($bewertungNameResult)) {
   $bewertungResult=mysql_query("SELECT wert FROM ".$tablePrefix."bewertung WHERE itemID=$ID AND bewertungID=$bewertungID") or die ("itemView.php;bewertungView(): Database error (".mysql_error().")");
   if(!(list($wert)=mysql_fetch_row($bewertungResult))) {
     $wert=$dunno;
	  $brotLaenge=0;
	  $brotMsg=""; // nothing is shown in brot-cell
   } else {
	  $brotLaenge=60*(5-$wert);
	  $overallCount++;
	  $overallSum+=$wert;
	  $brotMsg="&nbsp"; // tiny bread is shown in brot-cell if mark is 5
   }
  echo <<< EOT
                    <tr valign="center" bgcolor="#CCCC99"> 
                      <td width="184" height="2"> 
                        <div align="left"><img src="images/spacer.gif" width="10" height="10"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$bewertungName}</b></font></div>
                      </td>
                      <td width="127" height="2"><img src="images/spacer.gif" width="10" height="10"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$wert}</font></td>
                      <td width="382" height="2"> 
                        <table width="{$brotLaenge}" border="0" cellspacing="0" cellpadding="0" background="images/brot-balken.gif">
                          <tr> 
                            <td>{$brotMsg}</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
EOT;
  }

  if($overallCount > 0) {
    $overallMark=round($overallSum/$overallCount,1);
    $brotLaenge=60*(5-$overallMark);
	 $brotMsg="&nbsp;"; // tiny bread is shown in brot-cell if mark is 5
  } else {
    $overallMark="---";
    $brotLaenge=0;
	 $brotMsg=""; // nothing is shown in brot-cell
  }

  echo <<< EOT
                    <tr valign="top" bgcolor="#CCCC99"> 
                      <td width="184" height="23"> 
                        <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$cap_overall}</b></font><img src="images/spacer.gif" width="10" height="10"></div>
                      </td>
                      <td width="127" height="23"><img src="images/spacer.gif" width="10" height="10"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$overallMark}</b></font></td>
                      <td width="382" height="23"> 
                        <table width="{$brotLaenge}" border="0" cellspacing="0" cellpadding="0" background="images/brot-balken.gif">
                          <tr> 
                            <td>{$brotMsg}</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
EOT;

  echo <<< EOT
                  </table>
                </td>
              </tr>
            </table>
EOT;
  }
  
  function infoView ($ID) {
    global $tablePrefix, $CmsLinkInfo;

	 $cap_infoHeadline=getDbText("info_headline");
	 $cap_info=getDbText("sachen_info");

  echo <<< EOT
            <table width="698" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
              <tr valign="top"> 
                <td height="14"> 
                  <table width="699" border="0" cellspacing="1" cellpadding="0">
                    <tr bgcolor="#99CC33"> 
                      <td colspan="5"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							  <img src="images/spacer.gif" width="10" height="10"><b>{$cap_infoHeadline}</b></font>
							  <a href="{$CmsLinkInfo}#weaws" target="_blank"><img src="images/ico_info.gif" width="13" height="13" alt="{$cap_info}" border="0"></a></td>
                    </tr>
EOT;
  $infoNameResult=mysql_query("SELECT ID, name FROM ".$tablePrefix."info_name ORDER BY ID") or die ("itemView.php;infoView(): Database error (".mysql_error().")");
  while(list($infoID, $infoName)=mysql_fetch_row($infoNameResult)) {
   $infoResult=mysql_query("SELECT text FROM ".$tablePrefix."info WHERE itemID=$ID AND infoID=$infoID") or die ("itemView.php;infoView(): Database error (".mysql_error().")");
   list($infoText)=mysql_fetch_row($infoResult);
   echo <<< EOT

                    <tr bgcolor="#CCCC99"> 
                      <td colspan="4" height="4"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							  <b><img src="images/spacer.gif" width="10" height="10">{$infoName}</b></font></td>
                      <td width="511" valign="top" height="4"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							   <img src="images/spacer.gif" width="10" height="10">{$infoText}</font></td>
                    </tr>
EOT;
  }
  echo <<< EOT
                  </table>
                </td>
              </tr>
            </table>
EOT;
  }

  function getSaisonText ($bauern_ID){
	//$text="-siehe Detailansicht-".$bauern_ID."-";
	
	$saisonResult=mysql_query("SELECT text FROM most_info WHERE itemID=$bauern_ID AND infoID=7") or die ("itemView.php;getSaisonText(): Database error (".mysql_error().")");
	//echo($saisonResult);
	list($text)=mysql_fetch_row($saisonResult);
	if ($text == "") $text="keine Angabe";
	
	
	return $text;
	
  }
  
  
  
  function anfahrtView ($ID) {
    global $CmsLinkInfo;
	 $cap_infoHeadline="Anfahrt";
	 $cap_info=$cap_infoHeadline;
    
	echo <<< EOT
				<a name="anfahrt">
            <table width="698" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
              <tr valign="top"> 
                <td height="14"> 
                  <table width="699" border="0" cellspacing="1" cellpadding="0">
                    <tr bgcolor="#99CC33"> 
                      <td colspan="5"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							  <img src="images/spacer.gif" width="10" height="10"><b>{$cap_infoHeadline}</b></font>
							  <a href="{$CmsLinkInfo}#gmapanfahrt" target="_blank"><img src="images/ico_info.gif" width="13" height="13" alt="{$cap_info}" border="0"></a></td>
                    </tr>

                    <tr bgcolor="#CCCC99"> 
                      <td colspan="5">
                      	<div id="map" style="width: 697px; height: 300px; border: 0px;"></div>
							  			</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
       </a>
EOT;
  }
  
  function gastBewertungView ($ID) {
    global $tablePrefix, $CmsLinkInfo, $CmsLinkBewBase;

	 $voteSum=0;
	 $voteCount=0;





	 $cap_itemvote_guestBew=getDbText("itemvote_guestBew");
	 $cap_info=getDbText("sachen_info");
	 $cap_noVotes=getDbText("itemvote_noVotes");
	 $cap_voteCount=getDbText("itemvote_voteCount");
 	 $cap_good=getDbText("sachen_good");
 	 $cap_explain=getDbText("sachen_explain");
 	 $cap_overall=getDbText("sachen_overall");

   $gastbewertungAnzahl=mysql_query("SELECT SUM(anzahl) FROM ".$tablePrefix."gastbewertung WHERE itemID=$ID") or die ("itemView.php;gastBewertungView(): Database error (".mysql_error().")");
   list($gastbewertungsMenge)=mysql_fetch_row($gastbewertungAnzahl);
	if(isset($gastbewertungsMenge))
	  $gastbewertungsMenge=$gastbewertungsMenge." ".$cap_voteCount;
	 else
	  $gastbewertungsMenge=$cap_noVotes;


  echo <<< EOT
            <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#ff9900">
              <tr valign="top"> 
                <td height="86"> 
                  <table width="700" border="0" cellspacing="1" cellpadding="0" bgcolor="#ff9900">


                    <tr bgcolor="#ffcc33"> 
                      <td colspan="2"><img src="images/spacer.gif" width="10" height="10">
							  <font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$cap_itemvote_guestBew}</b>
							  <a href="{$CmsLinkInfo}#gb" target="_blank"><img src="images/ico_info.gif" width="13" height="13" alt="{$cap_info}" border="0"></a>
								</font></td>
                      <td width="382"><img src="images/spacer.gif" width="10" height="10">
							  <font face="Verdana, Arial, Helvetica, sans-serif" size="2">({$gastbewertungsMenge})</font>
                      </td>
                    </tr>
                    <tr bgcolor="#ffff66"> 
                      <td width="181" height="15">&nbsp;</td>
                      <td width="130" height="15"><img src="images/spacer.gif" width="10" height="10"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">{$cap_good}</font></td>
                      <td width="382" height="15"><img src="images/spacer.gif" width="10" height="10"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">{$cap_explain}</font></td>
                    </tr>
EOT;
  $gastbewertungNameResult=mysql_query("SELECT ID, name FROM ".$tablePrefix."gastbewertung_name ORDER BY ID") or die ("itemView.php;gastBewertungView(): Database error (".mysql_error().")");
  while(list($gastbewertungID, $gastbewertungName)=mysql_fetch_row($gastbewertungNameResult)) {
   $gastbewertungResult=mysql_query("SELECT wert/anzahl FROM ".$tablePrefix."gastbewertung WHERE itemID=$ID AND gastbewertungID=$gastbewertungID") or die ("itemView.php;gastBewertungView(): Database error (".mysql_error().")");
   list($gastbewertungErgebnis)=mysql_fetch_row($gastbewertungResult);
	if(!isset($gastbewertungErgebnis) || $gastbewertungErgebnis == "") {
	  $brotLaenge=0;
	} else {
    $voteCount++;
	 $voteSum+=$gastbewertungErgebnis;
	 $brotLaenge=(6-$gastbewertungErgebnis)*60;
	}
   echo <<< EOT
                    <tr valign="top" bgcolor="#ffffcc"> 
                      <td width="181" height="2" bgColor="#ffff66"><img src="images/spacer.gif" width="10" height="10">
							  <font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$gastbewertungName}</b></font></td>
                      <td width="130" height="2"><img src="images/spacer.gif" width="10" height="10">
							  <font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$gastbewertungErgebnis}</font></td>
                      <td width="382" height="2"> 
                        <table width="{$brotLaenge}" border="0" cellspacing="0" cellpadding="0" background="images/brot-balken.gif">
                          <tr> 
                            <td>&nbsp;</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
EOT;
  }
  if($voteCount == 0) {
    $brotLaenge=0;
	 $vote="";
  } else {
   $vote=$voteSum/$voteCount;
   $vote=floor($vote*100)/100;

   $brotLaenge=(6-$vote)*60;

  }
  echo <<< EOT
                    <tr valign="top" bgcolor="#ffffcc"> 
                      <td width="181" height="23" bgColor="#ffff66"> 
                        <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$cap_overall}</b></font></div>
                      </td>
                      <td width="130" height="23"><img src="images/spacer.gif" width="10" height="10">
							  <font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$vote}</b></font></td>
                      <td width="382" height="23"> 
                        <table width="{$brotLaenge}" border="0" cellspacing="0" cellpadding="0" background="images/brot-balken.gif">
                          <tr> 
                            <td>&nbsp;</td>
                          </tr>
                        </table>
                      </td>
                    </tr>		
                  
		     <TR vAlign=top bgColor=#ffffcc> 
                      <TD bgColor=#ffff66 height=2><IMG height=10 src="images/spacer.gif" width=10> <FONT face="Verdana, Arial, Helvetica, sans-serif" 
                        size=2><B>G&auml;ste-Kommentare</B></FONT>
		       </TD>
                      <TD colSpan=2> <DIV align=left>
			    <IMG height=10 src="images/spacer.gif" width=10> <FONT face="Verdana, Arial, Helvetica, sans-serif" size=2>
			    <A href="{$CmsLinkBewBase}{$ID}#bewliste"><FONT color=#ff3300>hier</FONT></A> k&ouml;nnt Ihr alle G&auml;ste-Kommentare lesen <br>
			    <IMG height=10 src="images/spacer.gif" width=10> <A href="{$CmsLinkBewBase}{$ID}&doComment=1#bewertung"><FONT color=#ff3300>hier</FONT></A> k&ouml;nnt Ihr selbst bewerten und kommentieren<br>
                          </FONT></DIV>
		 	</TD>
                    </TR>
		   </table>
                </td>
              </tr>
            </table>
EOT;
  }
  
  function viewItemDetailBewForm ($ID, $kommentar, $email, $commentInserted, $commentError) {
	 global $CmsLinkBewBase, $CmsLinkBewBaseNoId,  $CmsLinkBewParamOption, $CmsLinkBewParamItemId;
	 $stdEMail=getDbText("itemvote_stdEmail");
	 $stdComment=getDbText("itemvote_stdComment");
	 
	 $emailField=((isset($email)&&!$commentInserted)?$email:$stdEMail);
	 $commentField=((isset($kommentar)&&!$commentInserted)?$kommentar:$stdComment);
	 
	 $cap_addComment=getDbText("itemvote_addComment");
	 $cap_wirtbew=getDbText("itemvote_wirtBew");
    $cap_toEntries=getDbText("itemvote_toEntries");

	 echo "<a name=\"bewertung\"></a>\n";
	 echo "<p><a href=\"{$CmsLinkBewBase}{$ID}#bewliste\" style=\"background-color:#4E5D15;\">";
	 echo "<img src=\"images/ico_pfeillinks.gif\" width=\"13\" height=\"13\" border=\"0\" alt=\"\" align=\"absmiddle\">&nbsp;";
	 echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">{$cap_toEntries}</font>";
	 echo "</a></p>";

    echo <<< EOT
            <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900" height="142">
              <tr valign="top"> 
                <td> 
					 <form action="{$CmsLinkBewBaseNoId}" method="GET">
				<input type="hidden" name="bauerID" value="{$ID}"/>
				<input type="hidden" name="bauerPageType" value="schmalzbrotbew"/>
				<input type="hidden" name="option" value="$CmsLinkBewParamOption"/>
				<input type="hidden" name="itemId" value="$CmsLinkBewParamItemId"/>
                  <table width="700" border="0" cellspacing="1" cellpadding="0">
                    <tr bgcolor="#99CC33"> 
                      <td width="409"><img src="images/spacer.gif" width="10" height="10" border="0"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							  <b>{$cap_addComment}*</b>
EOT;
    if($commentError != "") echo "&nbsp;&nbsp;<font color=\"#FF0000\" size=2>$commentError</font>";
    echo <<< EOT
							  </font>
							 </td>
                      <td width="286"><img src="images/spacer.gif" width="10" height="10" border="0"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							 <b>{$cap_wirtbew}</b></font></td>
                    </tr>
                    <tr bgcolor="#D0D0D0"> 
                      <td width="409" valign="top" align="center" height="172"> 
                        <table border="0" cellspacing="10" cellpadding="0" width="324">
                          <tr valign="top"> 
                            <td> <font face="Verdana, Arial, Helvetica, sans-serif"> 
                              <input type="text" name="email" size="40" value="{$emailField}">
                              <br>
                              <br>
                              <textarea name="kommentar" cols="60" rows="5" wrap="VIRTUAL">{$commentField}</textarea>
                              <br>
                              <font size="2">*besonders willkommen sind Mostbauernspr&uuml;che, 
                              egal ob es Sinn-Spr&uuml;che oder Unnsinn-Spr&uuml;che 
                              sind ;-)</font></font></td>
                          </tr>
                        </table>
                      </td>
                      <td width="286" valign="top" align="center" height="172"> 
                        <table border="0" cellspacing="10" cellpadding="0" width="167">
                          <tr valign="top"> 
                            <td> 
                              <div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
EOT;

for($voteCounter=1; $voteCounter<=3; $voteCounter++) {
  echo getVoteName($voteCounter).":<br>";
  echo "<select name=\"vote_{$voteCounter}\">\n";
  echo "<option value={$optCount} selected>[".getDbText("itemvote_0")."]</option>\n";
  for($optCount = 1; $optCount<=5; $optCount++) {
    echo "<option value={$optCount}>".getDbText("itemvote_{$optCount}")."</option>\n";
  }	 
  echo "</select>\n";
  if($voteCounter != 3) echo "<br><br>";
}

  $cap_sendButton=getDbText("button_send");

  $cap_sendBothButton=getDbText("button_sendBoth");
  
echo <<< EOT
                                </font> <br>
                                <br>
                              </div>
                            </td>
                          </tr>
                        </table>
                      <div align="center"></div>
                      </td>
                    </tr>
                    <tr bgcolor="#D0D0D0"> 
                      <td width="409" valign="top" align="center"><input type="submit" name="nurkommentar" value="{$cap_sendButton}"></td>
                      <td width="286" valign="top" align="center"><input type="submit" name="nurbewertung" value="{$cap_sendButton}"></td>
                    </tr>
                    <tr bgcolor="#D0D0D0"> 
                      <td valign="top" align="center" colspan="2"><input type="submit" name="beides" value="{$cap_sendBothButton}"></td>
                    </tr>
                  </table>
						</form>
                </td>
              </tr>
            </table>

				<br>
EOT;
  }
  
  function calcMarksAndMBIforItem ($itemID) {
    global $tablePrefix;
	 
	 $decimals=1;
	 
	 $markCount=0;
	 $markSum=0;
	 $markWeightArray=array("preisnote" => 1, "menge" => 1, "geschmack" => 1);
	 $markArray=array();
	 $sacheMarks=array();

    $itemResult=mysql_query("SELECT sacheID, MIN(preis) as min, (MAX(preis)-MIN(preis))/4 as factor FROM {$tablePrefix}sachen WHERE preis>0 GROUP BY sacheID") or die ("itemView.php.gwc;calcMBIforItem(.): Database error (".mysql_error().")");
    while(list($sacheID, $min, $factor)=mysql_fetch_row($itemResult)) {
      $itemResult2=mysql_query("SELECT ROUND((preis-{$min})/{$factor}+1) as preisnote, menge, geschmack FROM {$tablePrefix}sachen WHERE itemID = {$itemID} AND sacheID = {$sacheID}") or die ("itemView.php.gwc;calcMBIforItem(.): Database error (".mysql_error().")");
      if($markArray=mysql_fetch_array($itemResult2, MYSQL_ASSOC)) {
		  $rowCount=0;
		  $rowSum=0;
		  foreach($markArray as $curKey => $curValue) {
			 if($curValue != NULL) {$rowCount++; $rowSum+=$curValue*$markWeightArray[$curKey];}
		  }

		  $markCount+=$rowCount;
		  $markSum+=$rowSum;

		  if($rowCount > 0 ) {
			$sacheMarks["{$sacheID}_preisnote"]=$markArray["preisnote"];
			$sacheMarks[$sacheID]=round($rowSum/$rowCount,$decimals);
		  } else {
			 $sacheMarks[$sacheID]=NULL;
		  }
		 } else {
			 $sacheMarks[$sacheID]=NULL;
		 }
	 }	 
	 
	 if($markCount > 0 )  // MostSpeisenIndex
	   $sacheMarks['MSI']=round($markSum/$markCount,$decimals);
	 else
	   $sacheMarks['MSI']=NULL;

    $itemResult=mysql_query("SELECT COUNT(wert) as count, SUM(wert) as sum FROM {$tablePrefix}bewertung WHERE itemID={$itemID}") or die ("itemView.php.gwc;calcMBIforItem(.): Database error (".mysql_error().")");
    list($count, $sum)=mysql_fetch_row($itemResult);
	 $markCount+=$count;
	 $markSum+=$sum;
	 
	 if($markCount > 0 )  // MostBauernIndex
	   $sacheMarks['MBI']=round($markSum/$markCount,$decimals);
	 else
	   $sacheMarks['MBI']=NULL;

	 return $sacheMarks;
  }

  function viewItemDetailBewResults ($ID) {
    global $CmsLinkBewBase;
    $cap_toVote=getDbText("button_toVote");
	 echo "<a name=\"bewliste\"></a>\n";
	 echo "<p><a href=\"".$CmsLinkBewBase."{$ID}&doComment=1#bewertung\" style=\"background-color:#4E5D15;\">";
	 echo "<img src=\"images/ico_pfeilrechts.gif\" width=\"13\" height=\"13\" border=\"0\" alt=\"\" align=\"absmiddle\">&nbsp;";
	 echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">{$cap_toVote}</font>";
	 echo "</a></p>\n";
	 gastBewertungView($ID);
	 echo "<br>";
	 viewUserComments($ID);
	 echo "<br><br>";
	 

	 
  }
  
  function generateWanderCoordinates () {
    global $tablePrefix;
    $itemResult=mysql_query("SELECT ID, name, wandCoord FROM ".$tablePrefix."bauer WHERE NOT ISNULL(wandCoord)") or die ("itemView.php.gwc;generateWanderCoordinates(): Database error (".mysql_error().")");
    while(list($id, $name, $coord)=mysql_fetch_row($itemResult)) {

      echo "<area shape=\"circle\" coords=\"$coord\" href=\"liste.php#$id\" alt=\"$name\">";
	 }
  }

  function generateStreetCoordinates () {
    global $tablePrefix;
    $itemResult=mysql_query("SELECT ID, name, strCoord FROM ".$tablePrefix."bauer WHERE NOT ISNULL(strCoord)") or die ("itemView.php.gsc;generateStreetCoordinates(): Database error (".mysql_error().")");
    while(list($id, $name, $coord)=mysql_fetch_row($itemResult)) {
      echo "<area shape=\"circle\" coords=\"$coord\" href=\"liste.php#$id\" alt=\"$name\">";
	 }
  }
  
  function userVote ($bauerID, $voteID, $vote) {
    global $tablePrefix;
    $itemResult=mysql_query("SELECT COUNT(*) FROM ".$tablePrefix."gastbewertung WHERE itemID=$bauerID AND gastbewertungID=$voteID") or die ("itemView.php;userVote(): Database error (".mysql_error().")");
	 list($count)=mysql_fetch_row($itemResult);
	 if($count == 0) // not voted yet
	   mysql_query("INSERT INTO ".$tablePrefix."gastbewertung VALUES ($bauerID, $voteID, 0, 0)") or die ("itemView.php;userVote(): Database error (".mysql_error().")");
    mysql_query("UPDATE ".$tablePrefix."gastbewertung SET anzahl=anzahl+1, wert=wert+$vote WHERE itemID=$bauerID AND gastbewertungID=$voteID") or die ("itemView.php;userVote(): Database error (".mysql_error().")");
  }
  
  function userComment ($bauerID, $email, $kommentar) {
    global $tablePrefix;
     $toUser=$from="most-meldung@mostbauer.com";
     if( !isEntryMessageSpam($kommentar) ) {
	 $dateTime=getCETDateTimeString();
    mysql_query("INSERT INTO ".$tablePrefix."kommentar VALUES ($bauerID, \"$dateTime\", \"$email\", \"$kommentar\")") or die ("itemView.php;userComment(): Database error (".mysql_error().")");

	 $subject="Neicha Eintrag: 'Gast-Kommentar zum Bauern'";
	 $message="Datum: {$dateTime}\nVon: {$email}\nBauer: ".getItemName($bauerID)."\n---\n{$kommentar}\n---";
	 mail($toUser, $subject, $message, "From: ".$from);
     } else {
	 $subject="Neicha Eintrag SPAM-notInserted: 'Gast-Kommentar zum Bauern'";
	 $message="Datum: {$dateTime}\nVon: {$email}\nBauer: ".getItemName($bauerID)."\n---\n{$kommentar}\n---";
	 mail($toUser, $subject, $message, "From: ".$from);
     }
  }

    function isEntryMessageSpam ($message) {
        $spamTriggerArray = array("link=", "url=", "http", 
				  "site. Thank", "Ja guat woars. Extra nach Linz gefahren",
		              	  "neue Hofgestaltung hams jetzt..Essen war auch");
	$spamInMessage = FALSE;
        foreach($spamTriggerArray as $spamTrigger) {
	  if( stristr($message, $spamTrigger) !== FALSE ) $spamInMessage = TRUE;
	}
  	return $spamInMessage;
  }
// --------------------------------------- time/date - START ---------------------------------------------

  function getCETDateTime () {
    global $timeZoneCorrection;
	 return time()+$timeZoneCorrection*60*60;
  }

  
  function getCETDateTimeString () {
    global $timeZoneCorrection;
	 $dateTime=date("Y-m-d H:i:s", getCETDateTime());
	 return $dateTime;

  }
  
  // $dbTime: YYYYMMDDhhmmss
  function dbTimestampToCETDateTime ($dbTime) {
    global $timeZoneCorrection;
    $YYYY=substr($dbTime,0,4);
    $MM=substr($dbTime,4,2);
    $DD=substr($dbTime,6,2);
    $hh=substr($dbTime,8,2);
    $mm=substr($dbTime,10,2);
    $ss=substr($dbTime,12,2);
	 
	 return date("d.m.Y H:i:s", mktime($hh+$timeZoneCorrection,$mm,$ss,$MM,$DD,$YYYY));
  }
  
// --------------------------------------- time/date - START ---------------------------------------------


  function viewUserComments ($ID) {
    global $tablePrefix;
    $itemResult=mysql_query("SELECT time, email, kommentar FROM ".$tablePrefix."kommentar WHERE itemID=$ID ORDER BY time DESC") or die ("itemView.php;viewUserComments(): Database error (".mysql_error().")");
    while(list($time, $email, $kommentar)=mysql_fetch_row($itemResult)) {
	   echo <<< EOT
            <hr width="700" noshade align="left" size="1">
            <font face="Verdana, Arial, Helvetica, sans-serif" size="2">$time - $email<br>
EOT;
      echo "<b>".textToHTMLText($kommentar)."</b></font><br>";
	 }
  }
  
// --------------------------------------- guestbook - START ---------------------------------------------

  function printGuestbookEntryForm () {

    $textInputSize=50;
	 
	 $cap_toEntries=getDbText("button_toEntries");
    $cap_nick=getDbText("cap_nick");
    $cap_email=getDbText("cap_email");
    $cap_emailAnzeigen=getDbText("cap_emailAnzeigen");
    $cap_homepage=getDbText("cap_homepage");
    $cap_ort=getDbText("cap_ort");
    $cap_message=getDbText("cap_message");

    $cap_buttonSend=getDbText("button_send");
	 
	 $val_nick=$_POST['nick'];
	 $val_email=$_POST['email'];
	 $val_homepage=$_POST['homepage'];
	 $val_ort=$_POST['ort'];
	 $val_message=$_POST['message'];
	 $showMailChecked=(isset($_POST['showEmail']) && $_POST['showEmail'])?" checked":"";
	 
	 if($val_homepage == "") $val_homepage = "http://";
	 
echo <<< EOT
      <p>
	   <IMG height=13 alt="" src="images/ico_pfeillinks.gif" width=13 align=bottom border=0>
	   <a href="mostgast.php"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2 color="#000000">{$cap_toEntries}</FONT></a>
		</p>
	<div style="background-color:white;border: 2px solid red;text-align:center;width:700px;height:30px;margin-bottom:10px;">
		Das Formular ist vor&uuml;bergehen wegen zahlreicher SPAM-Eintr&auml;ge gesperrt!
	</div>
      <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
        <tr valign="top"> 
          <td> <table width="700" border="0" cellspacing="1" cellpadding="2">
              <tr bgcolor="#99CC33"> 
                <td>
					  <div align="left"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2>{$cap_message}</FONT></div>
					 </td>
              </tr>
              <tr bgcolor="#CCCC99" valign="top"> 
                <td height="22"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2><B><FONT size=4> 
					  <form action="mostgast.php" method="post">
					  <table>
						 <tr><td><div align="right"><font size="2">{$cap_nick}:</font></div></td><td><input type="text" name="nick" size="{$textInputSize}" value="$val_nick"></td></tr>
						 <tr><td><div align="right"><font size="2">{$cap_email}:</font></div></td><td><div align="right"><font size="2"><input type="text" name="email" size="{$textInputSize}" value="$val_email">&nbsp;<input type="checkbox" name="showEmail" value="1"{$showMailChecked}> {$cap_emailAnzeigen} ?</font></div></td></tr>
						 <tr><td><div align="right"><font size="2">{$cap_homepage}:</font></div></td><td><input type="text" name="homepage" size="{$textInputSize}" value="$val_homepage"></td></tr>
						 <tr><td><div align="right"><font size="2">{$cap_ort}:</font></div></td><td><input type="text" name="ort" size="{$textInputSize}" value="$val_ort"></td></tr>
						 <tr><td><div align="right"><font size="2">{$cap_message}:</font></div></td><td><textarea name="message" rows="10" cols="50">{$val_message}</textarea></td></tr>
					  </table>
            </TD></TR></TBODY>
          </TABLE>
          </td>
      </tr>
    </table>
	 <br>
	 <input type="submit" name="eintragen" value="{$cap_buttonSend}">
	 </form>
EOT;
  }

  function storeGuestbookEntryToDB ($params) {
    global $tablePrefix;
    $gbElements=array(
	 							"nick" => "1",
	 							"email" => "0",
	 							"homepage" => "0",
	 							"ort" => "0",
	 							"message" => "1",
								);

	 if(isset($params['showEmail']) && $params['showEmail']) $showEmail="1"; else $showEmail="0";
	 $insertStatement="INSERT INTO {$tablePrefix}guestbook SET ";
	 
	 $message="";
	 foreach($gbElements as $elemName => $notEmpty) {
	   if($notEmpty && $params[$elemName] == "") return false;
		$insertStatement .= "{$elemName} = '".$params[$elemName]."', ";

		$message.="{$elemName} = ".$params[$elemName]."\n";
	 }
	 $message.="\n\nDe Mostbauanbuam!";

	 $toUser=$from="most-gaestebuch@mostbauer.com";
	 $subject="neicha gaestebuch-eintrag";


	 $insertStatement.="showEmail={$showEmail}, time=NOW()";
	 $agentString = $_SERVER['REMOTE_ADDR'] . "@=@" . $_SERVER['HTTP_USER_AGENT'];
	 $insertStatement.=", agent='{$agentString}'";
/*	 
	 if ( isGuestbookEntrySpam($params) ) {
	 	$spamMessageText = "cgint: This message was ignored because it was found to be automatically generated.\n\n";
		$message = $spamMessageText . $message;
		$subject = "[SPAM] Ignored - ".$subject;
		$insertStatement.=", spam='yes';";
	 } else {
	 	mail($toUser, $subject, $message, "From: ".$from);
		$insertStatement.=", spam='no';";
	 }
	 
     mysql_query($insertStatement) or die ("itemView.php;storeGuestbookEntryToDB(..): Database error (".mysql_error().")");
*/ 
	 return true;
	 
  }
  
  function isGuestbookEntrySpam ($params) {
    $httpInOrt = stristr($params["ort"], 'http://') !== FALSE;
	$httpCountInMessage = count( spliti( "http", $params["message"] ) ) - 1;	// array contains one entry if no http was found to split around
	$urlCountInMessage = count( spliti( "\[URL", $params["message"] ) ) - 1;
	$lizzyAtGmail = stristr($params["email"], 'lizzy@gmail.com') !== FALSE;

	$spam1InMessage = stristr($params["message"], 'site. Thank') !== FALSE;
	$spamInMessage = $spam1InMessage;
	
	$urlCountInMessage = count( spliti( "\[URL", $params["message"] ) ) - 1;
	
  	return $httpInOrt || $httpCountInMessage > 0 || $urlCountInMessage > 0 || $spamInMessage || $lizzyAtGmail;
  }

  function listGuestbook ($offset) {
    global $tablePrefix;
    $onePageEntryCount=5;

    $cap_email=getDbText("cap_email");
    $cap_homepage=getDbText("cap_homepage");
	 
	 $itemResult=mysql_query("SELECT COUNT(*) FROM {$tablePrefix}guestbook WHERE spam='no';") or die ("itemView.php;listGuestbook(\$offset={$offset}): Database error (".mysql_error().")");
	 list($entryCount)=mysql_fetch_row($itemResult);
	 
	 if(!is_numeric($offset) || $offset >= $entryCount/$onePageEntryCount) $offset=0; // offset invalid or out of range
	 
    $pageSplitNavHTML="<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"1\">[ ";
	 for($i=0; $i<$entryCount/$onePageEntryCount; $i++) {
	   $offsetElement=$i+1;


		if($offset == $i) 
  	     $pageSplitNavHTML .= "<b>{$offsetElement}</b> ";
		else
  	     $pageSplitNavHTML .= "<a href=\"mostgast.php?offset={$i}\"><font face=\"Verdana, Arial, Helvetica, sans-serif\" color='#669900' size=\"1\">{$offsetElement}</font></a> ";
	 }
	 
	 $pageSplitNavHTML .= " ]</font>";

    $cap_addGBEntry=getDbText("button_addEntry");
	 echo "<p>";
	 echo "<img src=\"images/ico_pfeilrechts.gif\" width=\"13\" height=\"13\" border=\"0\" alt=\"\" align=\"absmiddle\">&nbsp;";
	 echo "<a href=\"mostgast.php?addEntry=1\"><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\" color=\"#000000\">{$cap_addGBEntry}</font></a>";
	 echo "</p>";
	 ?>
	<div style="background-color:white;border: 2px solid red;text-align:center;width:700px;height:30px;margin-bottom:10px;">
		Das Formular ist vor&uuml;bergehen wegen zahlreicher SPAM-Eintr&auml;ge gesperrt!
	</div>
	<?
	 
	 if($entryCount > 0) {
		echo "<center>{$pageSplitNavHTML}</center>\n<br>\n";

   	$startLimit=$offset*$onePageEntryCount;
   	$endLimit=$onePageEntryCount;
		
		$itemResult=mysql_query("SELECT * FROM {$tablePrefix}guestbook WHERE spam='no' ORDER BY time DESC LIMIT $startLimit, $endLimit") or die ("itemView.php;listGuestbook(\$offset={$offset}): Database error (".mysql_error().")");
		while(list($gb_id, $time, $nick, $ort, $email, $homepage, $message, $showEmail)=mysql_fetch_row($itemResult)) {
	     if($showEmail == "0" || $email=="") $emailDisplay=""; else $emailDisplay=" - <a href=\"mailto:$email\">{$cap_email}</a>";
	     if($homepage=="" || $homepage=="http://") $homepageDisplay=""; else $homepageDisplay=" - <a href=\"$homepage\">{$cap_homepage}</a>";
	     if($ort=="") $ortDisplay=""; else $ortDisplay=" aus {$ort}";
		  
		  $dateTime=dbTimestampToCETDateTime($time);
		  $messageDisplay=nl2br($message);

echo <<< EOT

        <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
          <tr valign="top"> 
            <td> 
              <table width="700" border="0" cellspacing="1" cellpadding="2">
                <tr bgcolor="#99CC33"> 
                  <td>
						 <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="white">
						  {$dateTime} - <b>{$nick}</b>{$ortDisplay}{$emailDisplay}{$homepageDisplay}
						 </font>
						</td>
                </tr>
                <tr bgcolor="#CCCC99" valign="top"> 
                  <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$messageDisplay}</font></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
		  <br>
EOT;
		}

		echo "<center>{$pageSplitNavHTML}</center>\n";
	 } else {
	   echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">".getDbText("msg_noEntry")."</font>";
	 }
  }
  
// --------------------------------------- guestbook -  END  ---------------------------------------------

  
// --------------------------------------- suggestions - START ---------------------------------------------

  function printSuggestionsEntryForm () {
    global $sugElements;



    $textInputSize=50;
	 
	 $cap_toEntries=getDbText("button_toEntries");
	 $cap_mySuggestion=getDbText("cap_sugMySuggestion");
	 $cap_myInfo=getDbText("cap_sugMyInfo");
    $cap_nick=getDbText("cap_nick");
    $cap_email=getDbText("cap_email");
    $cap_emailAnzeigen=getDbText("cap_emailAnzeigen");
    $cap_homepage=getDbText("cap_homepage");
    $cap_ort=getDbText("cap_ort");
    $cap_buttonSend=getDbText("button_send");
	 
	 $val_nick=$_POST['nick'];
	 $val_email=$_POST['email'];
	 $val_homepage=$_POST['homepage'];
	 $val_ort=$_POST['ort'];
	 $showMailChecked=(isset($_POST['showEmail']) && $_POST['showEmail'])?" checked":"";
	 
	 if($val_homepage == "") $val_homepage = "http://";
	 
echo <<< EOT

      <p>
	   <IMG height=13 alt="" src="images/ico_pfeillinks.gif" width=13 align=bottom border=0>
	   <a href="mostvorschlag.php"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2 color="black">{$cap_toEntries}</FONT></a>
		</p>
	<div style="background-color:white;border: 2px solid red;text-align:center;width:700px;height:30px;margin-bottom:10px;">
		Das Formular ist vor&uuml;bergehen wegen zahlreicher SPAM-Eintr&auml;ge gesperrt!
	</div>
      <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
        <tr valign="top"> 
          <td> <table width="700" border="0" cellspacing="1" cellpadding="2">
              <tr bgcolor="#99CC33"> 
                <td>
					  <div align="left"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2>{$cap_myInfo}</FONT></div>
					 </td>
              </tr>
              <tr bgcolor="#CCCC99" valign="top"> 
                <td height="22"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2><B><FONT size=4> 
				  <form action="mostvorschlag.php" method="post">
				  <table>
					 <tr><td width="150"><div align="right"><font size="2">{$cap_nick}:</font></div></td><td><input type="text" name="nick" size="{$textInputSize}" value="$val_nick"></td></tr>
					 <tr><td width="150"><div align="right"><font size="2">{$cap_email}:</font></div></td><td><font size="2"><input type="text" name="email" size="{$textInputSize}" value="$val_email">&nbsp;<input type="checkbox" name="showEmail" value="1"{$showMailChecked}> {$cap_emailAnzeigen} ?</font></td></tr>
					 <tr><td width="150"><div align="right"><font size="2">{$cap_homepage}:</font></div></td><td><input type="text" name="homepage" size="{$textInputSize}" value="$val_homepage"></td></tr>
					 <tr><td width="150"><div align="right"><font size="2">{$cap_ort}:</font></div></td><td><input type="text" name="ort" size="{$textInputSize}" value="$val_ort"></td></tr>
              </table>
				  <tr bgcolor="#99CC33"> 
                <td>
					  <div align="left"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2>{$cap_mySuggestion}</FONT></div>
					 </td>
              </tr>
              <tr bgcolor="#CCCC99" valign="top"> 
                <td height="22"><FONT face="Verdana, Arial, Helvetica, sans-serif" size=2><B><FONT size=4> 
				  <table>
EOT;
    foreach(array_keys($sugElements) as $elemName) {
	   $capName="cap_{$elemName}";
	   $valName="val_{$elemName}";
		$$valName=$_POST["{$elemName}"];
		echo "<tr><td width=\"150\"><div align=\"right\"><font size=\"2\">".getDbText($capName).":</font></div></td>";
      if($elemName == "sugWegBeschr" ||  $elemName == "sugBeschreibung")
	     echo "<td><textarea name=\"{$elemName}\" rows=\"10\" cols=\"50\">{$$valName}</textarea></td>";
		else 
	     echo "<td><input type=\"text\" name=\"{$elemName}\" size=\"{$textInputSize}\" value=\"{$$valName}\"></td>";
		echo "</tr>\n";
	 }
echo <<< EOT

				  </table>
                                </TD></TR></TBODY>
                              </TABLE>
                              </td>
                          </tr>
                        </table>
	 <input type="submit" name="eintragen" value="{$cap_buttonSend}">
	 </form>
EOT;
  }

  function storeSuggestionEntryToDB ($params) {
    global $tablePrefix, $sugElements;
    $elements=array_merge($sugElements,array(
	 							"nick" => "1",
	 							"email" => "0",
	 							"homepage" => "0",
	 							"ort" => "0",
								));
	 if(isset($params['showEmail']) && $params['showEmail']) $showEmail="1"; else $showEmail="0";
	 $insertStatement="INSERT INTO {$tablePrefix}suggestion SET ";
	 
	 $message="";
	 foreach($elements as $elemName => $notEmpty) {
	   if($notEmpty && $params[$elemName] == "") return false;
		$insertStatement .= "{$elemName} = '".$params[$elemName]."', ";
		$message.="{$elemName} = ".$params[$elemName]."\n";
	 }
	 $message.="\n\nDe Mostbauanbuam!";

	 $toUser=$from="most-vorschlag@mostbauer.com";
	 $subject="neicha bauern-vorschlag";

	 $insertStatement.="showEmail={$showEmail}, time=NOW()";
	 $agentString = $_SERVER['REMOTE_ADDR'] . "@=@" . $_SERVER['HTTP_USER_AGENT'];
	 $insertStatement.=", agent='{$agentString}'";
/*
	 if ( isSuggestionEntrySpam($params) ) {
	 	$spamMessageText = "cgint: This message was ignored because it was found to be automatically generated.\n\n";
		$message = $spamMessageText . $message;
		$subject = "[SPAM] Ignored - ".$subject;
		$insertStatement.=", spam='yes';";
	 } else {
	 	mail($toUser, $subject, $message, "From: ".$from);
		$insertStatement.=", spam='no';";
	 }
	 
     mysql_query($insertStatement) or die ("itemView.php;storeSuggestionToDB(..): Database error (".mysql_error().")");
*/	 
	 return true;
	 
  }
  
  function isSuggestionEntrySpam ($params) {
    $httpInOrt = stristr($params["sugOrt"], 'http://') !== FALSE;
	$httpCountInMessage = count( spliti( "http", $params["sugWegBeschr"] ) ) - 1;	// array contains one entry if no http was found to split around
	$urlCountInMessage = count( spliti( "\[URL", $params["sugWegBeschr"] ) ) - 1;
	$httpCountInDesc = count( spliti( "http", $params["sugBeschreibung"] ) ) - 1;	// array contains one entry if no http was found to split around
	$urlCountInDesc = count( spliti( "\[URL", $params["sugBeschreibung"] ) ) - 1;

	$spam11InMessage = stristr($params["sugWegBeschr"], 'site. Thank') !== FALSE;
	$spam21InMessage = stristr($params["sugBeschreibung"], 'site. Thank') !== FALSE;
	$spamInMessage = $spam11InMessage || $spam21InMessage;
	
  	return $httpInOrt || $httpCountInMessage > 0 || $urlCountInMessage > 0 || $httpCountInDesc > 0 || $urlCountInDesc > 0 || $spamInMessage;
  }

  function listSuggestions ($offset) {
    global $tablePrefix, $sugElements;
    $onePageEntryCount=2;

    $cap_addGBEntry=getDbText("button_addEntry");
    $cap_email=getDbText("cap_email");
    $cap_homepage=getDbText("cap_homepage");
    $cap_toBauer=getDbText("suggestion_toBauer");

	 $sugCapArray=array();
	 foreach(array_keys($sugElements) as $elemName) {
  		$sugCapArray["cap_{$elemName}"]=getDbText("cap_{$elemName}");
	 }

	 $itemResult=mysql_query("SELECT COUNT(*) FROM {$tablePrefix}suggestion WHERE spam='no';") or die ("itemView.php;listGuestbook(\$offset={$offset}): Database error (".mysql_error().")");
	 list($entryCount)=mysql_fetch_row($itemResult);
	 
	 if(!is_numeric($offset) || $offset >= $entryCount/$onePageEntryCount) $offset=0; // offset invalid or out of range
	 
    $pageSplitNavHTML="<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"1\">[ ";
	 for($i=0; $i<$entryCount/$onePageEntryCount; $i++) {
	   $offsetElement=$i+1;
		if($offset == $i) 
  	     $pageSplitNavHTML .= "{$offsetElement} ";
		else
  	     $pageSplitNavHTML .= "<a href=\"mostvorschlag.php?offset={$i}\"><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"1\" color='#669900'>{$offsetElement}</font></a> ";
	 }
    $pageSplitNavHTML.=" ]</font>";

	 echo "<p>";
	 echo "<img src=\"images/ico_pfeilrechts.gif\" width=\"13\" height=\"13\" border=\"0\" alt=\"\" align=\"absmiddle\">&nbsp;";
	 echo "<a href=\"mostvorschlag.php?addEntry=1\"><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\" color=\"black\">{$cap_addGBEntry}</font></a>";
	 echo "</p>";
	 ?>
	<div style="background-color:white;border: 2px solid red;text-align:center;width:700px;height:30px;margin-bottom:10px;">
		Das Formular ist vor&uuml;bergehen wegen zahlreicher SPAM-Eintr&auml;ge gesperrt!
	</div>
	<?
	 
	 if($entryCount > 0) {
		echo "<center>{$pageSplitNavHTML}</center>\n<br>\n";

   	$startLimit=$offset*$onePageEntryCount;
   	$endLimit=$onePageEntryCount;
		

		$itemResult=mysql_query("SELECT * FROM {$tablePrefix}suggestion WHERE spam='no' ORDER BY time DESC LIMIT $startLimit, $endLimit") or die ("itemView.php;listGuestbook(\$offset={$offset}): Database error (".mysql_error().")");
		while($itemData=mysql_fetch_array($itemResult, MYSQL_ASSOC)) {
		  $nick=$itemData['nick'];
		  $email=$itemData['email'];
		  $homepage=$itemData['homepage'];
		  $ort=$itemData['ort'];
		  $showEmail=$itemData['showEmail'];
	     if($showEmail == "0" || $email=="") $emailDisplay=""; else $emailDisplay=" - <a href=\"mailto:$email\">{$cap_email}</a>";
	     if($homepage=="" || $homepage=="http://") $homepageDisplay=""; else $homepageDisplay=" - <a href=\"$homepage\">{$cap_homepage}</a>";
	     if($ort=="") $ortDisplay=""; else $ortDisplay=" aus {$ort}";
  		  $message="";
		  foreach(array_keys($sugElements) as $elemName) {
		    if($elemName == "sugWegBeschr" ||  $elemName == "sugBeschreibung") $content=nl2br($itemData[$elemName]); else $content=$itemData[$elemName];
  		    $message .= $sugCapArray["cap_{$elemName}"].": <font color=\"gray\">{$content}</font><br>";
		  }
		  
		  $dateTime=dbTimestampToCETDateTime($itemData['time']);
		  
		  if($itemData['status'] == "REPORTED") {
		    $birneURL="ico_birne_blau.gif";
			 $bauerLink="";
		  } else if($itemData['status'] == "ACCEPTED") {
		    $birneURL="ico_birne_gruen.gif";
			 $bauerLink="<a href=\"schmalzbrotdetail.php?ID=".$itemData['acceptedBauer_ID']."\">{$cap_toBauer}</a>";
		  } else { // DENIED
		    $birneURL="ico_birne_rot.gif";
			 $bauerLink="";
		  }
		  
echo <<< EOT
        <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
          <tr valign="top"> 
            <td>
				 <table width="700" border="0" cellspacing="1" cellpadding="2">
                <tr bgcolor="#99CC33"> 
                  <td>
						  <table border="0" cellspacing="0" cellpadding="0" width="100%">
						  <tr>
						    <td><font face="Verdana, Arial, Helvetica, sans-serif" color="white" size="2">{$dateTime} - <b>{$nick}</b>{$ortDisplay}{$emailDisplay}{$homepageDisplay}</font></td>
						    <td align="right"><font face="Verdana, Arial, Helvetica, sans-serif" color="white" size="2">{$bauerLink}&nbsp;<img src="images/{$birneURL}">&nbsp;</font></td>
						  </tr>
						 </table>
						</td>
                </tr>
                <tr bgcolor="#CCCC99" valign="top"> 
                  <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$message}</font></td>
                </tr>
             </table>
				</td>
          </tr>
        </table>
		  <br>
EOT;
		}

		echo "<center>{$pageSplitNavHTML}</center>\n";
	 } else {
	   echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">".getDbText("msg_noEntry")."</font>";
	 }
  }
  
// --------------------------------------- suggestions -  END  ---------------------------------------------

  
// --------------------------------------- mostpost - START ---------------------------------------------

  function listMostpostPix ($gewaehltesBild) {
    global $tablePrefix, $imgBaseDir;
	 $cellWidth=130;

	 if ($gewaehltesBild=="") {
	    $gewaehltesBild = 1;
	 } 

	 $cap_selectImage=getDbText("cap_postSelect");

	 $itemResult=mysql_query("SELECT COUNT(*) FROM {$tablePrefix}mostcardpix;") or die ("itemView.php;listMostpostPix(): Database error (".mysql_error().")");
	 list($entryCount)=mysql_fetch_row($itemResult);

	 if($entryCount > 0) {
	   echo <<< EOT
		
            <table width="720" border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
              <tr valign="top"> 
                <td> 
                  <table width="725" border="0" cellpadding="0" cellspacing="1">
                    <tr bgcolor="#99cc33"> 
                      <td valign="top"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><img src="images/spacer.gif" width="10" height="10" border="0"></b></font>
							 {$cap_selectImage}</font></td>
                    </tr>
                    <tr bgcolor="#CCCC99"> 
                      <td valign="top" bgcolor="#CCCC99">
					  	<div style="width:725px;height:210px;overflow:scroll;overflow-y:hidden;">
                        <table width="100%" border="1">
                          <tr> 
EOT;

		$itemResult=mysql_query("SELECT * FROM {$tablePrefix}mostcardpix ORDER BY card_id ASC") or die ("itemView.php;listMostpostPix(): Database error (".mysql_error().")");
		

		//$selectedItemSet=false;
		$itemSelected="";

		while(list($card_id, $url, $desc)=mysql_fetch_row($itemResult)) {
		     //ausgewaehltes bild
		     //$gewaehltesBild = 26;
		     //echo $card_id . "-". $gewaehltesBild ;
		     if ($gewaehltesBild ==  $card_id) { 
			//echo "jetzt";
			$itemSelected=" checked";
			} else {
			$itemSelected="";
		     }//if
		
        	     //if ( !$selectedItemSet ) {$itemSelected=" checked";$selectedItemSet=true;} else $itemSelected="";
		        $imgUrl="{$imgBaseDir}mostpost/{$url}";
		        $fitImgDim=fitImageDimsInBBox($imgUrl, 124, 124, FALSE);
		        $paddingToText = 128 - $fitImgDim['height'];

echo <<< EOT
	<td width="{$cellWidth}" align="center" valign="top">
		<table cellspacing="0" cellpadding="0" border="0" width="{$cellWidth}" height="100%">
			<tr>
         		<td align="center" valign="top" height="130">
				   <a href="{$imgBaseDir}mostpost/{$url}" onmouseover="showSachenImage('{$imgBaseDir}mostpost/{$url}');" onmouseout="hideSachenImage();" target="_blank"><img src="{$imgUrl}" width="{$fitImgDim['width']}" height="{$fitImgDim['height']}" border="0"></a>
				</td>
			</tr>
			<tr>
				<td align="left" valign="top">
                   <font face="Verdana, Arial, Helvetica, sans-serif" size="1"><input type="radio" name="cardPic_id" value="{$card_id}"{$itemSelected}>{$desc}</font>
				</td>
			</tr>
		</table>
	</td>
EOT;
		} //while schleife end

echo <<< EOT

                          </tr>
                        </table>
						</div>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
EOT;
	 } else {

	   echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">".getDbText("msg_noEntry")."</font>";
	 }
  }
  

  function insertMostCard ($senderMail, $senderName, $receiverMail, $receiverName, $postText, $cardPic_id, $code, $postTextFix ) {
    global $tablePrefix;
    $sqlstatement="INSERT INTO {$tablePrefix}mostcards VALUES ('', NOW(), '$senderName', '$senderMail', '$receiverName', '$receiverMail', '$postText', '$cardPic_id', '$code', '$postTextFix')";
	 $itemResult=mysql_query($sqlstatement) or die ("itemView.php;insertMostCard(): Database error (".mysql_error().")");
    return mysql_insert_id();
  }

// --------------------------------------- mostpost -  END  ---------------------------------------------

// --------------------------------------- schmalzbrotOrNot -  START  ---------------------------------------------

  function getRandomSBONData ($excludeId) {
    global $tablePrefix;

	 if($excludeId) $excludeClause=" WHERE sbon_id <> {$excludeId}";

	 $itemResult=mysql_query("SELECT COUNT(*) FROM {$tablePrefix}sbonImage{$excludeClause}") or die ("itemView.php;getRandomSBONImage(): Database error (".mysql_error().")");
	 list($itemCount)=mysql_fetch_row($itemResult);
	 if($itemCount > 0) {
	   $myItemPosition=rand(0,$itemCount-1);
		$itemResult=mysql_query("SELECT * FROM {$tablePrefix}sbonImage{$excludeClause} LIMIT {$myItemPosition},1") or die ("itemView.php;getRandomSBONImage(): Database error (".mysql_error().")");
		$sbonData=mysql_fetch_array($itemResult, MYSQL_ASSOC);
		if($sbonData) $sbonData['url']="images/sbon/".$sbonData['url']; // images can not be relocated, because of file-upload
	 }
    return $sbonData;
  }
  function storeSBONVote ($sbonId, $vote) {
    global $tablePrefix;
	 mysql_query("UPDATE {$tablePrefix}sbonImage SET voteSum=voteSum+{$vote}, voteCount=voteCount+1 WHERE sbon_id={$sbonId}") or die ("itemView.php;storeSBONVote(.): Database error (".mysql_error().")");
  }

  function getSBONData ($sbonId) {
    global $tablePrefix;
	 $itemResult=mysql_query("SELECT * FROM {$tablePrefix}sbonImage WHERE sbon_id={$sbonId}") or die ("itemView.php;getSBONData(.): Database error (".mysql_error().")");

	 $sbonData=mysql_fetch_array($itemResult, MYSQL_ASSOC);
	 if($sbonData) $sbonData['url']="images/sbon/".$sbonData['url']; // images can not be relocated, because of file-upload
    return $sbonData;
  }

function viewSBONPage () {
  if(isset($_GET['sbonID'])) {
    $curSBONData=getSBONData($_GET['sbonID']); // directly select an sbonImage (eg. from image-upload)
  } else {
    $curSBONData=getRandomSBONData($_POST['sbonVoteID']); // if former voting occured, exclude this pic from possible next images; none excluded if empty
  }
  
  if(!$curSBONData) return;
  
  $curVoteImage=$curSBONData["url"];
  $curVoteImageDim=fitImageDimsInBBox($curVoteImage, 200, 280, TRUE);
  $curVoteID=$curSBONData["sbon_id"];
  
  $cap_overallVote=getDbText("sbon_overallVote");
  $cap_noteVoted=getDbText("sbon_noteVoted");
  $cap_yourVote=getDbText("sbon_yourVote");
  $cap_sbonDoUpload=getDbText("sbon_doUpload");
















  echo "<p>";
  echo "<img src=\"images/ico_pfeilrechts.gif\" width=\"13\" height=\"13\" border=\"0\" alt=\"\" align=\"absmiddle\">&nbsp;";
  echo "<a href=\"sbon-bewertung.php?sbonAction=uploadPage\"><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\" color=\"black\">{$cap_sbonDoUpload}</font></a>";
  echo "</p>";

  if(isset($_POST['sbonVoteID'])) {
	 for($i=1; $i <= 7; $i++) {
   	$curVar="vote_{$i}_x";
   	if($_POST[$curVar]) {$lastVote=$i;break;}
	 }

	 if(isset($lastVote)) {
	   storeSBONVote($_POST['sbonVoteID'], $lastVote);
		$cap_lastVoteMeans=getDbText("sbon_vote{$lastVote}");
	 }

	 $lastVoteData=getSBONData($_POST['sbonVoteID']);
	 $lastVoteImage=$lastVoteData["url"];
	 $lastVoteImageDim=fitImageDimsInBBox($lastVoteImage, 120, 120, FALSE);
	 $lastVoteSum=$lastVoteData["voteSum"];
	 $lastVoteCount=$lastVoteData["voteCount"];
	 $lastVoteMark=($lastVoteCount>0)?round($lastVoteSum/$lastVoteCount,2):"---";
	 
	 $lastVoteContent="{$cap_overallVote}: {$lastVoteCount}<br>{$cap_yourVote}: {$lastVote} ({$cap_lastVoteMeans})";
  } else {
    $lastVoteImage="images/spacer.gif";
	 $lastVoteImageDim['width']=120;
	 $lastVoteImageDim['height']=120;
	 $lastVoteContent=$cap_noteVoted;
  }
  echo <<< EOT
            <center>
				<form action="sbon-bewertung.php" method="post">
            <table border="0" cellspacing="0" cellpadding="0" bgcolor="#669900">
              <tr>
                <td> 
                  <table border="0" bgcolor="#669900" cellpadding="2" cellspacing="1">
                    <tr> 
                      <td height="70" bgcolor="#CCCC99" width="42%"> 
                        <div align="center"><a target="_blank" href="{$curVoteImage}" onmouseover="showSachenImage('{$curVoteImage}');" onmouseout="hideSachenImage();"><img src="{$curVoteImage}" width="{$curVoteImageDim['width']}" height="{$curVoteImageDim['height']}" border=0></a></div>
                      </td>
                      <td width="200" rowspan="4" background="images/higru.jpg" valign="top"> 

                        <div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">SCHMALZBROT 
                          <a href="#info"><img src="images/ico_info.gif" width="13" height="13" border="0"></a><br>
								  <input type="hidden" name="sbonVoteID" value="{$curVoteID}">
EOT;
  for($v=7; $v >= 1; $v--) {
    $cap_voteText=getDbText("sbon_vote{$v}");
  echo <<< EOT
                          <input type="image" name="vote_{$v}" src="images/zr{$v}.gif" alt="{$cap_voteText}"><br>
EOT;
  }
  echo <<< EOT
  
                          <br>
                          NOT</font></div>
                      </td>
                    </tr>
                    <tr> 
                      <td valign="center" bgcolor="#CCCC99"> 
                        <div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>{$cap_overallVote}<br></b></font></div>
                      </td>
                    </tr>
                    <tr> 
                      <td height="120" valign="center" bgcolor="#CCCC99"> 
                        <div align="center"><a target="_blank" href="{$lastVoteImage}" onmouseover="showSachenImage('{$lastVoteImage}');" onmouseout="hideSachenImage();"><img src="{$lastVoteImage}" width="{$lastVoteImageDim['width']}" height="{$lastVoteImageDim['height']}" border=0></a></div>
                      </td>
                    </tr>
                    <tr> 
                      <td valign="top" bgcolor="#CCCC99"> 
                        <div align="center">
								  <font face="Verdana, Arial, Helvetica, sans-serif" size="6"><b>{$lastVoteMark}</b></font><br>
								  <font size="2">{$lastVoteContent}</font>
								</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
				</form>
            </center>
EOT;
}

function viewSBONUploadPage () {
  global $maxSBONUploadFileSize;
  $cap_sbonToVoting=getDbText("sbon_toVoting");
  $cap_buttonSend=getDbText("button_send");
  
  echo <<< EOT
	 <p>

	 <img src="images/ico_pfeillinks.gif" width="13" height="13" border="0" alt="" align="absmiddle">&nbsp;
	 <a href="sbon-bewertung.php"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="black">{$cap_sbonToVoting}</font></a>
	 </p>
	 Maximale Dateigr&ouml;&szlig;e: {$maxSBONUploadFileSize} Bytes!<br>
	 <form action="sbon-bewertung.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="sbonAction" value="doUpload">
      <input type="hidden" name="MAX_FILE_SIZE" value="{$maxSBONUploadFileSize}">
		<input type="file" name="userfile" size="50" maxlength="{$maxSBONUploadFileSize}"><br><br>
		<input type="submit" name="startFileUpload" value="{$cap_buttonSend}">
	 </form>
EOT;
}

function doSBONUpload () {
  global $tablePrefix;
  $cap_sbonToVoting=getDbText("sbon_toVoting");
  $allowedExtensions=array(".gif", ".jpg", ".jpeg");
  $errMessages=array(
                     "Upload war erfolgreich!",
							"Die Dateigr&ouml;sse &uuml;berschreitet die Servereinstellungen!",
							"Die Dateigr&ouml;sse &uuml;berschreitet die Formulareinstellungen!",
							"Die Datei wurde nur teilweise geladen!",
							"Es wurde keine Datei geladen!",
							"Es sind nur folgende Dateitypen erlaubt: "
                     );
  foreach($allowedExtensions as $curExt) {$errMessages[5].="{$curExt}, ";}
  $errMessages[5]=substr($errMessages[5], 0, -2);
  
  echo <<< EOT
  <p>
  <img src="images/ico_pfeillinks.gif" width="13" height="13" border="0" alt="" align="absmiddle">&nbsp;
  <a href="sbon-bewertung.php"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$cap_sbonToVoting}</font></a>
  </p>
EOT;
    $newLocationDir="images/sbon/";
	 
	 $uploadFileExtension=checkExtension($_FILES['userfile']['name'], $allowedExtensions);
	 if(!$uploadFileExtension) {
	   $curError=5;
	 } else {
	   $curError=$_FILES['userfile']['error'];
	 }
	 
 	 if($curError == 0) {
	   $msgColor="#000000";
	 } else {
	   $msgColor="#FF0000";
	 }

	 

	 echo "<font size=\"3\" color=\"{$msgColor}\"><b>{$errMessages[$curError]}</b></font><br>\n";

	 
	 $info.="<br>";
	 $info.="name=".$_FILES['userfile']['name']."<br>";
	 $info.="type=".$_FILES['userfile']['type']."<br>";
	 $info.="size=".$_FILES['userfile']['size']."<br>";
	 $info.="tmp_name=".$_FILES['userfile']['tmp_name']."<br>";
	 $info.="error=".$_FILES['userfile']['error']."<br>\n";

	 if($curError == 0) {

		mysql_query("INSERT INTO {$tablePrefix}sbonImage VALUES ('', '', NOW(), '', '')") or die ("itemView.php;doSBONUpload(.): Database error (".mysql_error().")");
   	$newSBONImageID=mysql_insert_id();
		$newFilename="upload{$newSBONImageID}{$uploadFileExtension}";
      $newLocation="{$newLocationDir}{$newFilename}";
	   $info.="newLocation=$newLocation<br>\n";

		 // this would also overwrite an existing file
  		if(move_uploaded_file($_FILES['userfile']['tmp_name'], $newLocation)) {
		  $info .= "moving OK<br>";
		  chmod ($newLocation, 0644);
		  echo "<br><a href=\"sbon-bewertung.php?sbonID={$newSBONImageID}\"><img src=\"{$newLocation}\" border=\"0\"></a><br>\n";
		  echo "Bild gleich bewerten, einfach auf's Bild klicken!";
		  mysql_query("UPDATE {$tablePrefix}sbonImage SET url='$newFilename' WHERE sbon_id={$newSBONImageID}") or die ("itemView.php;doSBONUpload(.): Database error (".mysql_error().")");
		} else {
		  $info .= "moving NOTOK<br>";
		  mysql_query("REMOVE FROM {$tablePrefix}sbonImage WHERE sbon_id={$newSBONImageID}") or die ("itemView.php;doSBONUpload(.): Database error (".mysql_error().")");
		}
	 }

//	 echo $info;
}


function checkExtension ($fileName, $allowedExtensions) {
  $extFound=false;
  $fileName=strtolower($fileName);
  $fileNameLength=strlen($fileName);
  foreach($allowedExtensions as $ext) {
    if(strpos($fileName, $ext) == ($fileNameLength-strlen($ext))) {$extFound=$ext; break;}
  }
  return $extFound;
}

// --------------------------------------- schmalzbrotOrNot -  END  ---------------------------------------------

  function fitImageDimsInBBox ($imgUrl, $BBoxWidth, $BBoxHeight, $doZoomIn) {
    $imageData=getimagesize($imgUrl);
	 $imgWidth=$imageData[0];
	 $imgHeight=$imageData[1];
	 $BBoxMin=min($BBoxWidth, $BBoxHeight);
	 
	 $xFactor=$BBoxWidth/$imgWidth;
	 $yFactor=$BBoxHeight/$imgHeight;
	 
	 $scaleFactor=min($xFactor,$yFactor);
	 if($scaleFactor > 1 && !$doZoomIn) $scaleFactor=1; // if set to not zoom-in, don't scale
	 
	 $fitImgDim['width'] =round($imgWidth*$scaleFactor ,0);
	 $fitImgDim['height']=round($imgHeight*$scaleFactor,0);
	 
	 return $fitImgDim;
  }

function textToHTMLText ($text) {
    $text=preg_replace("/ï¿½/","&auml;", $text);
    $text=preg_replace("/ï¿½/","&uuml;", $text);
    $text=preg_replace("/ï¿½/","&ouml;", $text);
    $text=preg_replace("/ï¿½/","&Auml;", $text);
    $text=preg_replace("/ï¿½/","&Uuml;", $text);
    $text=preg_replace("/ï¿½/","&Ouml;", $text);
    $text=preg_replace("/\n/","<br>", $text);
    return $text;
}

function addNewsletterSubscriber ($email) {
  global $tablePrefix, $webServerURL;
  $code=getRandomWebCode();
  mysql_query("INSERT INTO {$tablePrefix}newsletterSubscriber VALUES ('', NOW(), '{$email}', '', '{$code}', 'web', '')") or die ("itemView.php;doSBONUpload(.): Database error (".mysql_error().")");
  // newsletter-account is inactive per default
  $nlsub_id=mysql_insert_id();
  
  $subject="Neuer Newsletter Abonent";
  $from="abo-service@mostbauer.com";
  $message ="Hallo lieber Mostbauer.com-Besucher!\n\n";
  $message.="Du hast dich soeben fï¿½r unseren Newsletter eingetragen.\n";
  $message.="Um das zu bestï¿½tigen bitten wir dich folgenden Link zu klicken:\n";
  $message.="{$webServerURL}/ma/newsletter.php?action=subscribe&nlsub_id={$nlsub_id}&code={$code}.\n\n";
  $message.="Abmelden kannst du dich unter folgendem Link:\n";
  $message.="{$webServerURL}/ma/newsletter.php?action=unsubscribe&nlsub_id={$nlsub_id}&code={$code}.\n\n";
  $message.="Danke,\nDas Mostbauer.com-Team.\n";

  mail($email, $subject, $message, "From: ".$from);
  
  echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">";
  echo "Danke f&uuml;r die Anmeldung, eine email wurde an '{$email}' geschickt.<br>";
  echo "Nachdem diese email beantwortet ist, wird dein Newsletter-Account aktiviert.<br><br>";
  echo "Danke,<br>das Mostbauer.com-Team<br><br>";
  echo "Zu den <a href=\"news.htm\">News</a><br><br><br><br><br><br>";
  echo "</font>";
}

function activateNewsletterSubscriber ($nlsub_id, $userCode) {
  global $tablePrefix;
  $selectResult=mysql_query("SELECT code FROM {$tablePrefix}newsletterSubscriber WHERE nlsub_id={$nlsub_id}") or die ("itemView.php;activateNewsletterSubscriber(.): Database error (".mysql_error().")");
  list($dbCode)=mysql_fetch_row($selectResult);
  echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">";

  if($dbCode == $userCode) {
    $updateResult=mysql_query("UPDATE {$tablePrefix}newsletterSubscriber SET active=1 WHERE nlsub_id={$nlsub_id}") or die ("itemView.php;activateNewsletterSubscriber(.): Database error (".mysql_error().")");
	 echo "Die Aktivierung war erfolgreich!<br><br>";
  } else {
	 echo "Die Aktivierung war NICHT erfolgreich!<br>Der angegebene Code passt nicht.<br><br>";
  }
  echo "Du kannst das Fenster jetzt schliessen.<br><br><br><br><br><br>";
  echo "</font>";
}

function sendNewsletterToSubscribers ($subject, $message, $from, $sendToSubscribers) {
  global $tablePrefix, $webServerURL;
  
  print "<h3>Folgende Nachricht wird an alle Mostbauer.com-Newsletter-Subscriber versandt:</h3>\n";
  print "<b>From:</b> {$from}<br>\n";
  print "<b>Subject:</b> {$subject}<br>\n";
  print "<i>".textToHTMLText($message)."</i><br><br>\n";print "<h3>Versenden wird durchgef&uuml;hrt:</h3>";
  print "<p><b>Legende:</b> <br> '+' = erfolgreicht gesendet <br> '-' = Sendung misslungen</p>";
  
  $query="SELECT nlsub_id, email, code FROM {$tablePrefix}newsletterSubscriber WHERE active='1'"; 
  if(!$sendToSubscribers) $query=$query." AND admin='1'";

  $selectResult=mysql_query($query) or die ("itemView.php;sendNewsletterToSubscribers(.): Database error (".mysql_error().")");
  while(list($nlsub_id, $email, $code)=mysql_fetch_row($selectResult)) {
    $realMessage="".$message;
    $realMessage.="\n==============\nAbmelden kannst du dich unter folgendem Link:\n";
    //$realMessage.="{$webServerURL}/ma/newsletter.php?action=unsubscribe&nlsub_id={$nlsub_id}&code={$code}\n"; auskommentiert von Chris weil Mostbauer.com/unterverzeichnis funktioniert momentan nicht
    $realMessage.="http://gewerbeweb.com/mostbauer/ma/newsletter.php?action=unsubscribe&nlsub_id={$nlsub_id}&code={$code}\n";
    $realMessage.="Nach erfolgter Abmeldung wird Deine e-mail Adresse gelï¿½scht und Du bekommst nie wieder Post von uns.\n";
    //$realMessage.="Erneut ANMELDEN kannst Du Dich unter {$webServerURL}/ma/news.htm\n\n"; auskommentiert von Chris weil Mostbauer.com/unterverzeichnis funktioniert momentan nicht
    $realMessage.="Erneut ANMELDEN kannst Du Dich unter http://gewerbeweb.com/mostbauer/ma/news.htm\n\n";
    $realMessage.="Danke,\nDas Mostbauer.com-Team.\n";

    if( myMail($email, $subject, $realMessage, $from) )
         {print "+ # {$email} ... gesendet.<br>\n";}
    else {print "- # {$email} ... <b>NICHT gesendet.</b><br>\n";}
  }
  print "<p><b>Fertig.</b></p>";
}

function myMail($email, $subject, $message, $from) {
  return myMail3($email, $subject, $message, $from);
}

function myMail3($email, $subject, $message, $from) {
  return mail($email, $subject, $message, "From: " . $from);
}

function myMail1($email, $subject, $message, $from) {
	set_time_limit(0);
	$http=new http_class;
	$http->timeout=0;
	$http->data_timeout=0;
	$http->debug=0;
	$http->html_debug=1;
$myUrl ="http://mostbauer.com/derausweg.php";
$error=$http->GetRequestArguments($myUrl,$arguments);
$arguments["PostValues"]=array(
		"email"=>"{$email}",
		"subject"=>"{$subject}",
                "message"=>"Newsletter-Test",
                "from"=>"{$from}"
	);
	flush();
	$error=$http->Open($arguments);

if($error == "") {
  $error=$http->SendRequest($arguments);
  $http->Close();
  if($error <> "") {
    print $error;
  }
  return $error=="";
} else {
  print $error;
}

return false;
}

function myMail2($email, $subject, $message, $from) {
  $myUrl ="http://mostbauer.com/derausweg.php";
  $myData="email={$email}&subject=".urlencode($subject)."&message=Newsletter-Test&from={$from}";
  

  $handle = fopen($myUrl."?".$myData, "r");
//  fwrite($handle, $myData);
  $answer=fread($handle, 100);
  fclose($handle);

  return $answer == "+";

}

function unsubscribeNewsletterSubscriber ($nlsub_id, $userCode) {
  global $tablePrefix;
  $selectResult=mysql_query("SELECT code, email FROM {$tablePrefix}newsletterSubscriber WHERE nlsub_id={$nlsub_id}") or die ("itemView.php;unsubscribeNewsletterSubscriber(.): Database error (".mysql_error().")");
  if(list($dbCode, $email)=mysql_fetch_row($selectResult)) {
	 print "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">";
	 if($dbCode == $userCode) {
//   	mysql_query("DELETE FROM {$tablePrefix}newsletterSubscriber WHERE nlsub_id={$nlsub_id}") or die ("itemView.php;unsubscribeNewsletterSubscriber(.): Database error (".mysql_error().")");
   	mysql_query("UPDATE {$tablePrefix}newsletterSubscriber SET active=-1 WHERE nlsub_id={$nlsub_id}") or die ("itemView.php;unsubscribeNewsletterSubscriber(.): Database error (".mysql_error().")");
		$successText="Die Abmeldung von {$email} war erfolgreich!";
	 } else {
		$successText="Die Abmeldung von {$email} war NICHT erfolgreich!<br>Der angegebene Code passt nicht.";
	 }
  } else {
    $successText="Der angegebene Benutzer wurde bereits entfernt!";
  }

  print "{$successText}<br><br>\n";
  print "Du kannst das Fenster jetzt schliessen.<br><br><br><br><br><br>";
  print "</font>";
}


function viewItemGallery ($ID, $offset) {
  //
  $dirName="images/galerie/{$ID}/";
  if(!file_exists($dirName)) {print "Keine Bilder vorhanden!!<br>"; return;}
  $dir = dir($dirName);
  $i=0;
  $x=3;
  $y=3;
  
  while(false !== ($entry = $dir->read())) {
  	 if($entry != "." && $entry != "..") {
	 	$imgName=$dirName."".$entry;
		$imgDim=fitImageDimsInBBox($imgName, 100, 100, false);
	  	print "<a href=\"$imgName\" target=\"schmalzbrotgalerie_detail\"><img src=\"{$imgName}\" width=".$imgDim['width']." height=".$imgDim['height']." border=0></a>\n";
		$i++;
		

		if($i%$x == 0) print "<br><br>";
	 }
  }
  $dir->close();
}


function viewItemGalleryWithLightbox ($ID, $offset) {
  // images/galerie/ID/ sucht eigentlich im root/dynamic import/images/galerie/ID !
  //
  $dirName="images/galerie/{$ID}/";
  //echo"---".$dirName."---";
			
  if(!file_exists($dirName)) {print "Keine Bilder vorhanden !!!<br>"; return;}
  $dir = dir($dirName);
  $i=0;
  $x=3;
  $y=3;

  
  while(false !== ($entry = $dir->read())) {
  	 if($entry != "." && $entry != "..") {
	 	$imgName=$dirName."".$entry;
		
		/*extrablock!
		$dirdazu="dynamic import/";
		//echo ("++++++++dirdazu".$dirdazu."+++++++");
		$imgName=$dirdazu.$imgName;
		echo ("++++++++neuer".$imgName."+++++++");
		*/

		
		$imgDim=fitImageDimsInBBox($imgName, 100, 100, false);
		//echo("***".$imgName."***###".$entry."###");
		
		//bilddaten (text und copyright) holen
	    $itemResultBild=mysql_query("SELECT * FROM most_bildinfo WHERE bauernid='{$ID}' AND bildname='12410.jpg';") or die ("itemView.php;listGuestbook(\$offset={$offset}): Database error (".mysql_error().")");
	    list($bauernid,$bildname,$titel,$beschreibung,$copyright,$ausblenden)=mysql_fetch_row($itemResultBild);

		//$bildtitel = von DB holen falls vorhanden
		//echo("+++".$titel."+++");
		/*if ($titel == NULL) {
			$titel="Mostbauer.com Galeriebild");
		}*/
		//$titel="Hallo";
		//$bildbeschreibung = von DB holen falls vorhanden
		//if ($beschreibung==null) {$beschreibung="");
		//$copyright = von DB holen falls vorhanden
		//if ($copyright==null) {$copyright="");
		
		//if ($ausblenden="N"){
		//print "<a href=\"$imgName\"  target=\"_blank\" rel=\"lightbox[Mostbauer]\" title='{$titel} - {$beschreibung}'><img src=\"{$imgName}\" width=".$imgDim['width']." height=".$imgDim['height']." border=0></a>\n";
		
		print "<a href=\"$imgName\"  target=\"_blank\" rel=\"lightbox[Mostbauer]\" title=\"M.com Galeriebild\"><img src=\"{$imgName}\" width=".$imgDim['width']." height=".$imgDim['height']." border=0></a>\n";
		//echo ("++++++++alter".$imgName."+++++++");
		//print "<a href=\"$imgName\"  target=\"_blank\" rel=\"lightbox[Mostbauer]\" title=\"M.com Galeriebild\"><img src=\"{$imgName}\" width=".$imgDim['width']." height=".$imgDim['height']." border=0></a>\n";
		
		//}

		$i++;
		

		if($i%$x == 0) print "<br><br>";
	 }
  }
  $dir->close();


  echo("<BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>");
}




//---------------------------------------
//
//---------------------------------------
function eventsZumBauernAnzeigen($view_bauer_ID) {
  global $CmsLinkInfo;
echo <<< EOT
    	<BR>
	 
	 <!----------aussentable events---------------------------------->
        <TABLE cellSpacing=0 cellPadding=0 width=700 bgColor='#003366' border=0>  
             <TR vAlign=top> 
                  <TD>
			
			<!----------innentable1 events---------------------------------->
			<TABLE cellSpacing=1 cellPadding=0 width=700 border=0>
          			<TR> 
              				<TD width=700 bgColor='#0055AA'>
						<FONT face="Verdana, Arial, Helvetica, sans-serif" size=2>
						<IMG height=10 src="images/spacer.gif" width=10 border=0><B>Events bei diesem Bauern</B>
						</FONT>
						<a href="{$CmsLinkInfo}#events" target="_blank"><img src="images/ico_info.gif" width="13" height="13" border="0"></a>
					</TD>
             			</TR>
EOT;


    $dateTime=date("Y-m-d", getCETDateTime());
    global $tablePrefix;

    $sqlStatement="SELECT ID,titel,datum_von,datum_bis,zeitraum,uhrzeit,adresse,hausnr,ort,plz,lokalitï¿½t,bauer_ID,wegbeschreibung,beschreibung,link,veranstalter,kontakt_email,kontakt_tel FROM {$tablePrefix}events WHERE (bauer_ID={$view_bauer_ID}) ORDER BY datum_von";
    $itemResult=mysql_query($sqlStatement);
    
    while(list($ID,$titel,$datum_von,$datum_bis,$zeitraum,$uhrzeit,$adresse,$hausnr,$ort,$plz,$lokalitï¿½t,$bauer_ID,$wegbeschreibung,$beschreibung,$link,$veranstalter,$kontakt_email,$kontakt_tel)=mysql_fetch_row($itemResult)){

    	if ($datum_bis >= $dateTime)  { //einschrï¿½nkung auf ab heute
		$wotagtextvon = wochetagText1($datum_von);
		$wotagtextbis = wochetagText1($datum_bis);

		echo"<TR bgColor=#99CC33>";
			echo"<TD vAlign=top bgColor=#8A8AFF><FONT face='Verdana, Arial, Helvetica, sans-serif' size=2>";
				echo"&nbsp;&nbsp;&nbsp;{$wotagtextvon}, {$datum_von}";
				if ($datum_bis!=$datum_von) {
		    			echo" bis {$wotagtextbis}, {$datum_bis}";
				}
				echo"&nbsp;&nbsp;&nbsp;<strong><a href='events.php#{$ID}'>. :: {$titel} :: .</a></strong>{$zeitraum} {$uhrzeit}";
				

			echo"</font></TD>";


		echo"</TR>";
	  }//end if einschrï¿½nkung auf ab heute


	}//end while



	echo"</Table>";
	//----------innentable1 events---------------------------------->

   echo"	</TD>";
   echo"    </TR>";
   echo"</Table>"; 
   //----------aussentable events---------------------------------->

   }//eventsFï¿½rBauernAnzeigen 




  //-----------------------------------------------------
  // sucht aus einem ï¿½bergebenenn datum den wochentag 
  // und ï¿½bergibt diesen als Text
  // erstellt: 14.05.2005 Chris  (codeverdopplung)
  //----------------------------------------------------
  function wochetagText1 ($datum1) {


	$timestamp1 = strtotime($datum1);
	//echo "timestamp: {$timestamp1}";
	$wochentag = date ( "w", $timestamp1);
	//echo "wochentag: {$wochentag}";
	if ($wochentag == 1) {$wochentagText = "Mo"; }
	if ($wochentag == 2) {$wochentagText = "Di"; }
	if ($wochentag == 3) {$wochentagText = "Mi"; }
	if ($wochentag == 4) {$wochentagText = "Do"; }
	if ($wochentag == 5) {$wochentagText = "Fr"; }
	if ($wochentag == 6) {$wochentagText = "Sa"; }
	if ($wochentag == 0) {$wochentagText = "So"; }
	//echo "wochentagtext: {$wochentagText}";

	return $wochentagText;
   }

  //-----------------------------------------------------
  // Statistikfunktionen
  // erstellt: 07.03.2006 CGint
  //----------------------------------------------------
  function dbstat_mostmeldung () {
    global $tablePrefix;
    $sqlStatement="SELECT count( * ) AS entries, k.itemId AS itemID, b.name AS name FROM `most_kommentar` k, most_bauer b WHERE k.itemID = b.ID GROUP BY k.itemID ORDER BY entries DESC";
    $itemResult=mysql_query($sqlStatement);

    echo "<table class='dbstat_table'><tr class='dbstat_headrow'><td><b>Bauer</b></td><td><b>Mostmeldungen</b></td></tr>";
	
    while(list($entries,$itemID,$name)=mysql_fetch_row($itemResult)){
		echo "<tr><td>{$name}</td><td align='right'>{$entries}</td></tr>";
	}
	
	echo "</table>";
  }
  function dbstat_wap_access () {
    global $tablePrefix;
	$excludeCrawlerWhereClause  = " WHERE ";
	$excludeCrawlerWhereClause .= "LOWER(agent) NOT LIKE '%crawl%'";
	$excludeCrawlerWhereClause .= " AND LOWER(agent) NOT LIKE '%archive.org%'";
	$excludeCrawlerWhereClause .= " AND LOWER(agent) NOT LIKE '%google%'";
	$excludeCrawlerWhereClause .= " AND LOWER(agent) NOT LIKE '%wwwster%'";
	$excludeCrawlerWhereClause .= " AND LOWER(agent) NOT LIKE '%url control%'";
	$excludeCrawlerWhereClause .= " AND LOWER(agent) NOT LIKE '%deepak%'";
	$excludeCrawlerWhereClause .= " AND LOWER(agent) NOT LIKE '%scooter%'";
    $sqlStatement="SELECT SUBSTRING( time, 1, 7  )  AS  MONTH , COUNT(  *  )  AS acccess FROM  `most_wap_logging` {$excludeCrawlerWhereClause}  GROUP  BY  MONTH  ORDER  BY  MONTH  DESC ";
    $itemResult=mysql_query($sqlStatement);
    echo "<table class='dbstat_table'><tr class='dbstat_headrow'><td><b>Monat</b></td><td><b>WAP-Zugriffe</b></td></tr>";
	
	$accessSum = 0;
    while(list($month,$access)=mysql_fetch_row($itemResult)){
		echo "<tr><td>{$month}</td><td align='right'>{$access}</td><td><img src='images/ico_birne_rot.gif' height='15' width='{$access}'></td></tr>";
		$accessSum += $access;
	}
    $allCountResult=mysql_query("SELECT COUNT(  *  )  AS allacccess FROM  `most_wap_logging`");
	list($allaccess)=mysql_fetch_row($allCountResult);
	echo "<tr><td>SUMME:</td><td align='right'>{$accessSum}/{$allaccess}</td><td>&nbsp;</td></tr>";
	
	echo "</table>";
  }
  
  function dbstat_spam () {
    global $tablePrefix;
	
    echo "<table class='dbstat_table'>";
	
	// spam in guestbook
    $sqlStatement="SELECT count( * ) AS number, spam FROM {$tablePrefix}guestbook GROUP BY spam ";
    $itemResult=mysql_query($sqlStatement);

	echo "<tr class='dbstat_headrow'><td><b>Spam</b></td><td><b>G&auml;stebuch</b></td></tr>";
	
    while(list($number,$spam)=mysql_fetch_row($itemResult)){
		echo "<tr><td>{$spam}</td><td align='right'>{$number}</td></tr>";
	}
	
	// spam in vorschlag
    $sqlStatement="SELECT count( * ) AS number, spam FROM {$tablePrefix}suggestion GROUP BY spam ";
    $itemResult=mysql_query($sqlStatement);

	echo "<tr class='dbstat_headrow'><td><b>Spam</b></td><td><b>Most-Vorschlag</b></td></tr>";
	
    while(list($number,$spam)=mysql_fetch_row($itemResult)){
		echo "<tr><td>{$spam}</td><td align='right'>{$number}</td></tr>";
	}
	
	// end
	echo "</table>";
  }

//------------- wetter api


// --------------------------------------------------
// liest die wetterdaten von google fï¿½r die angegebene
// loacation
// --------------------------------------------------

function website_wetter($plz, $land, $icons_src="/", $sprache="de", $ort="") {
/*
Nutzung dieses Scripts nur gestattet, wenn Kommentare in PHP nicht entfernt werden oder ein Link zu folgender Adresse gesetzt wird:
URL: http://www.web-spirit.de/webdesign-tutorial/9/Wetter-auf-eigener-Website-mit-Google-Weahter-API
Beschreibung: Wettervorhersage auf der eigenen Website mit Zugriff auf die Google Weather API
Autor: Sebastian Gollus
Internet: http://www.web-spirit.de
Version: 1.0.200909
*/
	
	$icons_google = "/ig/images/weather/";
	if($ort != "")
	{
		$station = $ort;
	}
	else
	{
		$station = $plz."+".$land;
	}
	$api = simplexml_load_string(utf8_encode(file_get_contents("http://www.google.com/ig/api?weather=".$station."&hl=".$sprache)));
//	$api = simplexml_load_string(utf8_encode(file_get_contents("http://www.google.com/ig/api?weather=4040+Linz+Austria&hl=de")));
	

	
	$wetter = array();
	
	// Allgemeine Informationen
	/*
	$wetter['stadt'] = $api->weather->forecast_information->city->attributes()->data;
	$wetter['datum'] = $api->weather->forecast_information->forecast_date->attributes()->data;
	$wetter['zeit'] = $api->weather->forecast_information->current_date_time->attributes()->data;
	*/
	
	// Aktuelles Wetter
	$wetter[0]['zustand'] = $api->weather->current_conditions->condition->attributes()->data;
	$wetter[0]['temperatur'] = $api->weather->current_conditions->temp_c->attributes()->data;
	$wetter[0]['luftfeuchtigkeit'] = $api->weather->current_conditions->humidity->attributes()->data;
	$wetter[0]['wind'] = $api->weather->current_conditions->wind_condition->attributes()->data;
	$wetter[0]['icon'] = str_replace($icons_google, $icons_src, $api->weather->current_conditions->icon->attributes()->data);
	
	// Wettervorhersage heute, morgen, in zwei und in drei Tagen ($wetter[1] bis $wetter[4])
	$i = 1;
	foreach($api->weather->forecast_conditions as $weather)
	{
		$wetter[$i]['wochentag'] = $weather->day_of_week->attributes()->data;
		$wetter[$i]['zustand'] = $weather->condition->attributes()->data;
		$wetter[$i]['tiefsttemperatur'] = $weather->low->attributes()->data;
		$wetter[$i]['hoechsttemperatur'] = $weather->high->attributes()->data;
		$wetter[$i]['icon'] = str_replace($icons_google, $icons_src, $weather->icon->attributes()->data);
	
		$i++;
	}
	

	return $wetter;
}


// --------------------------------------------------
// gibt das image und die temperatur des tages aus
// Achtung liest jedesmal gesamtes wetter aus
// --------------------------------------------------
function tageswetter($tag_nummer){
  
  $wetter = website_wetter("", "Austria", "/", "de", "Linz");
  //echo ("tagnummer:".$tag_nummer);
  //echo "<strong>".$wetter[2]['wochentag']."</strong><br/>\n";
  //echo $wetter[4]['zustand']."<br/>\n";
  echo "<font face='Arial' size='1'>";
  echo "<img src=\"http://www.google.com/ig/images/weather".$wetter[$tag_nummer]['icon']."\" alt=\"".$wetter[$tag_nummer]['zustand']."\" /><br/>\n";
  if ($tag_nummer=="0"){  //heute
    echo $wetter[0]['temperatur']."&deg;C\n";	
  }else{
    echo $wetter[$tag_nummer]['tiefsttemperatur']." - ".$wetter[$tag_nummer]['hoechsttemperatur']."&deg;C<br/>\n";
  }
  echo "</font>";
}

//
// Liest die Bauernliste je nach gesetztem wochentagCode (nicht gesetzt == alle)
//
function fetch_bauern_region_list($wochentagCode){
	global $weekdayShortStringArray, $tablePrefix;
	
  if(isset($wochentagCode) && strlen($wochentagCode) > 0) {
   $dayField=$weekdayShortStringArray[$wochentagCode];
	 $queryString="SELECT b.ID, r.name FROM {$tablePrefix}bauer as b, {$tablePrefix}tage as t, {$tablePrefix}region as r WHERE b.regionID=r.ID AND b.ID=t.bauerID AND NOT ISNULL(t.{$dayField}) ORDER BY r.ID, b.ID DESC";
  } else {
	 $queryString="SELECT b.ID, r.name FROM {$tablePrefix}bauer as b, {$tablePrefix}region as r WHERE b.regionID=r.ID ORDER BY r.ID, b.ID DESC";
  }

  $itemsListResult=mysql_query("$queryString") or die ("itemView.php;fetch_bauern_region_list(): Database error (".mysql_error().")");;

  $itemsListArray = array();

  while(list($ID, $regionName)=mysql_fetch_row($itemsListResult)) {
  	$itemsListArray[$ID] = $regionName;
  }
  
  return $itemsListArray;
}

function display_weekday_selection($wochentag, $type) {	// type = schmalzbrot, gmap
  global $weekdayStringArray, $weekdayShortStringArray, $CmsLinkOverview;

  $weekDayTodayCode=date("w", getCETDateTime()); // 0 is sunday ... 6 is saturday
  
  $cap_showBauer3=getDbText("bauer_showBauer3");
  
  if ($wochentag == 'heute'){
     $wochentag = $weekDayTodayCode;
  }

  if(isset($wochentag) && $wochentag != "alle") {
    $wochentagCode=$wochentag%7;
  	 if($wochentagCode == $weekDayTodayCode) $weekDayTodaySelected=" selected";
  	 if($wochentagCode == ($weekDayTodayCode+1)%7) $weekDayTomorrowSelected=" selected";
  } else $wochentag="alle"; // no or wrong parameter given
	
  $weekdaySelectStyle = ($type=="gmap" ? " style='height:20px;margin-bottom:5px;'" : "");
  
	?>

<!-- WEEKDAY - SELECT - Start -->

  <div id="weekdaySelect"<?=$weekdaySelectStyle?>>
  <center>
  <div id="weekdaySelectDays">

<?php

  echo '<a target="_parent" id="weekdaySelect_alle" href="'.$CmsLinkOverview.'&wochentag=alle"'.($wochentag == 'alle'?' class="active"':'').($type=='gmap'?' onclick="mbgm_fetchBauern(\'alle\'); return false;"':'').'>'.$cap_showBauer3.'</a>';
  echo "<span>&nbsp;&nbsp;Offen:&nbsp;&nbsp;</span>";
  echo '<a target="_parent" id="weekdaySelect_'.$weekDayTodayCode.'" href="'.$CmsLinkOverview.'"'.($wochentagCode == $weekDayTodayCode?' class="active"':'').($type=='gmap'?' onclick="mbgm_fetchBauern(\''.$weekDayTodayCode.'\'); return false;"':'').'>heute</a>';
  $codeTomorrow=($weekDayTodayCode+1)%7;
 
  echo '<a target="_parent" id="weekdaySelect_'.$codeTomorrow.'" href="'.$CmsLinkOverview.'&wochentag='.$codeTomorrow.'"'.($wochentagCode === $codeTomorrow?' class="active"':'').($type=='gmap'?' onclick="mbgm_fetchBauern(\''.$codeTomorrow.'\'); return false;"':'').'>morgen</a>';
 
  for($i=1; $i < 6; $i++) {

	 $todayCode=($weekDayTodayCode+1+$i)%7;
	 echo '<a target="_parent" id="weekdaySelect_'.$todayCode.'" href="'.$CmsLinkOverview.'&wochentag='.$todayCode.'"'.($wochentagCode === $todayCode?' class="active"':'').($type=='gmap'?' onclick="mbgm_fetchBauern(\''.$todayCode.'\'); return false;"':'').'>'.$weekdayStringArray[$todayCode].'</a>';
  }//for

?>

  </div> <!-- <div id="weekdaySelectDays"> -->
  <?
  if ( $type == "schmalzbrot" ) {
  	
  	/*
  echo(tageswetter("0"));// heute
  echo(tageswetter("1"));//morgen
  echo(tageswetter("2"));//ï¿½bermorgen
  echo(tageswetter("3"));//ï¿½ï¿½morgen
  echo(tageswetter("4"));//ï¿½ï¿½ï¿½morgen
*/
  	
	  echo"<div id='bauernListe'>";
	
	//  echo"<span>Auf dieser Seite zu folgendem Bauern springen:&nbsp;</span>";
	
		$itemsListArray = fetch_bauern_region_list($wochentagCode);
	  echo createMostbauernDropdownList ("betriebeInUebersicht", "jo", FALSE, $itemsListArray);
	
	  echo"</div>";
  }
  ?>
  </center>
  </div> <!-- <div id="weekdaySelect"> -->

  <div id="weekdaySelectReset">
  </div>

<!-- WEEKDAY - SELECT - End -->
	<?
	
	return $wochentagCode;
}



?>
