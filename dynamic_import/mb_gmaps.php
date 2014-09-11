<?php
require("itemView.php");
?>

<?
if( !isset($_REQUEST["include"]) ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Mostbauer.com GoogleMaps Integration Page</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link rel="stylesheet" type="text/css" href="mb_gmaps.css" />
	<link rel="stylesheet" type="text/css" href="styles.css" />
	<script type="text/javascript">
		// set page-parameters
		var firstRequest=true;
		var gmapsBackendURL = "mb_gmaps_backend.php";
		<?php
			if ( isset($_REQUEST["ID"]) ) {
				$wochentag = "alle";
				?>
					var initBauerId="<?=$_REQUEST["ID"]?>";
				<?
			} else {
				$wochentag = $_REQUEST["wochentag"];
			}
			if ($wochentag == 'heute'){
				$weekDayTodayCode=date("w", getCETDateTime()); // 0 is sunday ... 6 is saturday
				$wochentag = $weekDayTodayCode;
		  }
		?>
		var wochentag="<?=$wochentag?>";
	</script>
	<script type="text/javascript" src="gmaps_lib/jquery-1.4.1.min.js"></script>
	<script src="http://www.google.com/jsapi?key=ABQIAAAAdOT6QtejptiTMh6F6lauoRQ0jeGsySTJPOlA8He0i_oJxGVfSRRnfaFf-7toSO2C3rZORmi9kANJhQ" type="text/javascript"></script> 
	<script type="text/javascript" src="gmaps_lib/mb_gmaps.js"></script>
	<script type="text/javascript">
		google.setOnLoadCallback(mbgm_initialize_map_map);
	</script>
</head>
<?php
	$topLocation = isset($_REQUEST["ID"]) ? $CmsLinkMapBase.$_REQUEST["ID"] : $CmsLinkMapWTagBase.$_REQUEST["wochentag"];
?>
<body onload="if ( this == top ) document.location = '<?=$topLocation?>';" onunload="GUnload()">
<?
 } else {
?>
	<link rel="stylesheet" type="text/css" href="http://mostbauer.com/dynamic_import/mb_gmaps.css" />
	<link rel="stylesheet" type="text/css" href="http://mostbauer.com/dynamic_import/styles.css" />
	<script type="text/javascript">
		// set page-parameters
		var firstRequest=true;
		var gmapsBackendURL = "http://www.mostbauer.com/dynamic_import/mb_gmaps_backend.php";
		<?php
			if ( isset($_REQUEST["ID"]) ) {
				$wochentag = "alle";
				?>
					var initBauerId="<?=$_REQUEST["ID"]?>";
				<?
			} else {
				$wochentag = $_REQUEST["wochentag"];
			}
			if ($wochentag == 'heute'){
				$weekDayTodayCode=date("w", getCETDateTime()); // 0 is sunday ... 6 is saturday
				$wochentag = $weekDayTodayCode;
		  }
		?>
		var wochentag="<?=$wochentag?>";
	</script>
	<script type="text/javascript" src="http://mostbauer.com/dynamic_import/gmaps_lib/jquery-1.4.1.min.js"></script>
	<script src="http://www.google.com/jsapi" type="text/javascript"></script> 
	<script type="text/javascript" src="http://mostbauer.com/dynamic_import/gmaps_lib/mb_gmaps.js"></script>
	<script type="text/javascript">
		google.setOnLoadCallback(mbgm_initialize_map_map);
	</script>
<?
}
?>
<div id="pageSurrounding">
<table width="566" border="0" cellpadding="0" cellspacing="0">

  <tr valign="top"> 

    <td class="top_headline">Karte</td>

  </tr>

  <tr valign="top"> 

    <td background="images/higru.jpg" valig="top"> 

      <table border="0" cellspacing="0" cellpadding="0">

        <tr> 

          <td width="36" height="418" valign="top">&nbsp;</td>

          <td height="418" valign="top">
<?php
	$wochentagCode = display_weekday_selection($wochentag, "gmap");
?>

<div id="actionBox">
	<div id="actionBoxWorking">Do mua&szlig; i gach nochfrog'n ...</div>
<?php
$prevRegionName = "";
$itemsListArray = fetch_bauern_region_list(""); // fetch all
	foreach($itemsListArray as $ID => $regionName) {
    $itemResult=mysql_query("SELECT id, name, famName, regionID FROM ".$tablePrefix."bauer WHERE ID={$ID}") or die ("mb_gmaps_backend.php;fetchReturnData(): Database error (".mysql_error().")");
    list($id, $name, $famName, $regionID)=mysql_fetch_row($itemResult);
    
    // generate region-item if neccessary
    if ( $regionName != $prevRegionName ) {
    	if ( $prevRegionName != "" ) {
    		?>
    		</ul></li></ul> <!-- close current region -->
    		<?  		
    	}
			?>
				<ul>
					<li id="region_<?=$regionID?>" class="region"><?=$regionName?></li>
			<?
			$prevRegionName = $regionName;
   	}
		?>
			<li id="action_<?=$id?>" class="action" onclick="mbgm_showBauer(<?=$id?>);"><?=$name . ($famName == "" ? "" : "<br/><span class='famName'>" . $famName) . "</span>"?></li>
		<?
	}
  ?>
    </ul></li></ul> <!-- close last region -->
  <?  		
?>
</div>

<div id="map"></div>
<div id="bauerDetailSurround"><div id="bauerDetail"></div></div>
          </td>

        </tr>

      </table>

    </td>

  </tr>

</table>
</div>
<?
if( !isset($_REQUEST["include"]) ) {
?>
</body>
</html>
<?
}
?>
