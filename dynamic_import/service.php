<?php
  require("itemView.php");
?>
<html>
<head>
<title>www.Mostbauer.com - Mostbauern f&uuml;r d' Stodleit von Linz</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" content="Mostbauer, Mostbauern, MostbauernEckn, Eck,Most, Linz, Urfahr, Leonding, Altenberg, Ansfelden, Ebelsberg, Rufling, Pasching, Schmalzbrot, Brettljause, Gastgarten,Mostschank, Fleischbrot, Kartoffelk&auml;sbrot, Stadler, Winkler,Rohrwieser,P&uuml;hringer,Jagerhuber, Eichbauer in Moos,Mur z' Moos,Sommer, Zum 13er Turm, TamesbergerW&ouml;rndl, Rieder zu Ried,Exenschl&auml;ger, Waldschenke,Mayr z' Imberg,Heidi, Kurt, Schneiderbauer, Hammer, Mirellenst&uuml;berl, Reichinger, Schatzbauer, Riener, St&ouml;ger, Mostbauer z'Linz, Aichhorn, Leitner, Weinstadl">
<meta name="description" content="Wie ich zum Mostbauer.com ? Endlich alle Mostbauern für d'Stodleit von Linz auf an Blick ! Damit steht dem urbanen Kulinarium in der nahen Natur nix im Weg ...">
<meta name="robots" content="noindex">
</head>
<body bgcolor="#FFFFFF" link="#FFFFFF" vlink="#FFFFFF" alink="#006600">

	<table width="800" border="0" cellpadding="0" cellspacing="0" >
        <tr> 
          <td width="36" valign="top">&nbsp;</td>
          <td width="764" valign="top"> <font size="1"><br>
            </font> <p><font face="Verdana, Arial, Helvetica, sans-serif" size="4"><b>Servicebereich<br>
              </b></font><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Dieser 
              Bereich steht nur für die auf Mostbauer.com gelisteten Bauern 
              zu Verfügung.</font></p>
            <table width="720" border="0" cellspacing="0" cellpadding="0" bgcolor="#00CC00">
              <tr valign="top"> 
                <td> <table width="725" border="0" cellpadding="0" cellspacing="1">
                    <tr bgcolor="#99CC33"> 
                      <td valign="top" bgcolor="#99FF00"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><img src="../images/spacer.gif" width="10" height="10" border="0"></b></font>
					  <b>Was möchtest Du tun ?</b></font></td>
                    </tr>
                    <tr bgcolor="#ACFF59"> 
                      <td valign="top" bgcolor="#ACFF59"> <br> <table width="701" border="0" cellspacing="0" cellpadding="0">
                          <tr> 
                            <td width="11"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><img src="../images/spacer.gif" width="10" height="10" border="0"></b></font></td>
                            <td width="676" valign="top"> 
								<FORM action='service.php?performAction=ACTION' method="post">				
<?php
				/*echo'-ss?'.$textsuccessfullysaved.'-';
				if($textsuccessfullysaved==1){
					echo"<FORM action='service.php' method='post'>";
					$betrieb=NULL;
				}
				*/
				echo"<FORM action='service.php?performAction=ACTION' method='post'>";
				
				//echo 'HELLO betrieb-'.$betrieb.'-HELLO2';
				//echo 'HELLO pass-'.$passwort.'-HELLO2';
				//echo 'HELLO3 pass'.htmlspecialchars($_POST["passwort"]).'HELLO4';

				//passwort  richtig  - dann speichern und erfolgsmneldung			           
				if(isset($passwort)){  				      
				   $itemResult2=mysql_query("SELECT passwort FROM most_bauer WHERE ID=".$betrieb) or die ("Fehler beim Laden der Daten.");				      
				   list($passwortOrig)=mysql_fetch_row($itemResult2);				      
				   if ($passwort==$passwortOrig){				          
				     //alten text auslesen für infomail		
					  $itemResult=mysql_query("SELECT text3 FROM most_bauer WHERE ID=".$betrieb) or die ("Fehler beim Laden der Sprüche");				      		
					  list($alterText)=mysql_fetch_row($itemResult);
					 
					  				          
				      $sqlstatement="UPDATE most_bauer SET text3='".$neuerText."' WHERE ID=".$betrieb;      //speichern 			                  
				      //echo ($sqlstatement);      				          
				      mysql_query($sqlstatement);
				      
   				      echo "<font face='Verdana, Arial, Helvetica, sans-serif' size='2'>";					  
					  echo "<B>&nbsp;&nbsp;Ihr neuer Text wurde erfolgreich gespeichert !</B>";
					  $textsuccessfullysaved=1;
					 
					  //mailnotiz an uns
					  	$toUser=$from="most-service@mostbauer.com";
	 					$subject="Update erfolgt: 'Was der Bauer selber sagt!'";
					    $message="Bauer: ".getItemName($betrieb)."\n---alter Text---\n{$alterText}\n\n\n---neuer Text---\n{$neuerText}\n---";
	 					mail($toUser, $subject, $message, "From: ".$from);
					  				      
					  }else{
					  //echo "hallo";
					  
					  echo "<BR><font face='Verdana, Arial, Helvetica, sans-serif' size='2'>";					  
					  echo "&nbsp;&nbsp;Das Passwort war nicht korrekt!<BR>";
					  echo "</font><B><font size='2' face='Verdana, Arial, Helvetica, sans-serif' color=red>";
					  echo "&nbsp;&nbsp;Ihr neuer Text konnte NICHT gespeichert werden!</B><font size='2' face='Verdana, Arial, Helvetica, sans-serif' color=black>";
					  $textnotsaved=1;
				       }//passwort is ok				     
					   }//isset passwort
					
					//echo'betrieb:'.$betrieb;
				    if(isset($betrieb)){				      
					//Betriebsbezeichnung   				      
					$itemResult=mysql_query("SELECT name FROM most_bauer WHERE ID=".$betrieb) or die ("Fehler beim Laden der Daten.");				      
					list($name)=mysql_fetch_row($itemResult);
				      echo <<< EOT
                                      <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 				      
									  &nbsp; Mein Betrieb: &nbsp;<B>$name</B> (Nr.:$betrieb)<input type="hidden" name="betrieb" value=$betrieb>
EOT;
				      }else{
				      echo"<p><font face='Verdana, Arial, Helvetica, sans-serif' size='2'>&nbsp; Mein Betrieb:";                                     				      
					  $selectName="betrieb";				      
					  $behaviourType="lo";				      
					  //echo $behaviourType;				      
					  echo createMostbauernDropdownList($selectName,$behaviourType);				      
					  //echo"$abc";
				     }			
	?> 


	<?php 	
					//echo 'whatIwantToDo'.$whatIwantToDo.'-';
					if ($textnotsaved==1){
						$whatIwantToDo=1;
					}
								
				    if (isset($performAction) ){				       
					switch ($whatIwantToDo) {					     
					  case 1:				      		
					  //bestehenden Text einlesen   				      
					  //echo 'betrieb vor texteinlesen'.$betrieb.'---';		
					  $itemResult=mysql_query("SELECT text3 FROM most_bauer WHERE ID=".$betrieb) or die ("Fehler beim Laden der Sprüche");				      		
					  list($text3)=mysql_fetch_row($itemResult);
				      
					  if ($textnotsaved==1){
					 	//altenText nehmen
					  	$text3=$neuerText;		
					  }		
							
							echo <<< EOT
							
					        <BR>
				      		&nbsp;&nbsp;Sie k&ouml;nnen Ihren Text f&uuml;r die gr&uuml;ne Box &quot;was der Bauer sagt&quot; hier &auml;ndern:<br>
				      		&nbsp;&nbsp;<textarea name="neuerText" rows="10" cols="50">$text3</textarea>
                            <BR>
				      		&nbsp;&nbsp;Passwort: 
                            <input name="passwort" type="password" id="passwort">
				      		<BR><BR>&nbsp;&nbsp;<I>Hinweis:</I> Sollten Sie noch kein Passwort haben für Ihren Betrieb,<BR>&nbsp;&nbsp;einfach mail an <A HREF="mailto:team@Mostbauer.com"><font color=black>service@Mostbauer.com</font></A>
EOT;
						break;					    
						case 2:						
						echo "<BR> Diese Funktion steht noch nicht vollständig zu verfügung";						
						break;					    
						case 3:						
						echo "<BR> Diese Funktion steht noch nicht vollständig zu verfügung";	
						break;
					    default:						
						//echo "Keinen Menüpunkt gewählt. ";				      
						echo "<BR><BR>&nbsp;&nbsp;<a href='service.php'><font color=black><strong>Zur Service-Startseite...</strong></font></a>";				      
						}  //end switch
				    }else{     				      
					//else perform action nicht gesetzt
				      		echo <<< EOT
							
							 <BR>
					   <Table><tr><td>&nbsp;&nbsp;</td>
					   <td>
					  <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><BR>Ich m&ouml;chte:<br>                                      
					  </td></tr><tr><td valign=top><input name="whatIwantToDo" type="radio" value="1" checked></td><td valign=top><font size="2">unseren Text f&uuml;r die gr&uuml;ne Box &quot;was der Bauer sagt&quot; &auml;ndern   
					  </font><font size="1"><br>zB.: Beschreibung vom Hof, und welche besonderen Schmakerl es gibt; etc.      <br>       				      
					  </td></tr><tr><td valign=top><input type="radio" name="whatIwantToDo" value="2" disabled></td><td valign=top><font size="2" color="#006600"> (inaktiv) pflegen von wann bis wann wir Saison haben (reguläre Saison)<br>
					  </td></tr><tr><td valign=top><input type="radio" name="whatIwantToDo" value="3" disabled></td><td valign=top><font size="2" color="#006600">(inaktiv) Wichtige informationen für die Gäste eingeben. <BR> 
					  </font><font size="1" color="#006600">zB.: Neuigkeiten, Zeitraum wo während der Saison geschlossen ist; Hoffest ankündigen; etc.     <br>
					  </font>                                     
					  <br>
					  </td></tr></table>
					  <BR><font size="2">Bei Fragen, kontaktier uns bitte einfach: <a href='mailto:team@Mostbauer.com'><font color=black>team@Mostbauer.com</font></a>
EOT;
				     }	

	


		                         echo'  <br></font></p><p>';
								if($textsuccessfullysaved!=1){
								    echo"<input type='submit' name='Submit' value='  OK  '>";
								}

	?> 
                                </p>
                                </form>
                              <br>
							  </td>
                            <td width="14"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><img src="../images/spacer.gif" width="10" height="10" border="0"></b></font></td>
                          </tr>
                        </table>
                        <br> </td>
                    </tr>
                  </table></td>
              </tr>
            </table> 
            <br>
          </td>
        </tr>
      </table>

</body>
</html>

