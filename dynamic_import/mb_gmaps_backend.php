<?php
require("itemView.php");


	// global definitions
	$iconOpen = "http://mostbauer.com/dynamic_import/images/mbgm_ico_open.gif";			// Der Bauer hat an dem Tag offen
	$iconNotOpen = "http://mostbauer.com/dynamic_import/images/mbgm_ico_notopen.gif";	// Der Bauer hat an dem Tag zu
	$iconClosed = "http://mostbauer.com/dynamic_import/images/mbgm_ico_closed.gif";		// Der Bauer ist geschlossen (gibt's nimma)
	$iconWillNicht = "http://mostbauer.com/dynamic_import/images/mbgm_ico_closed.gif";		// Der Bauer will nicht gelistet werden
	$iconSizeX = 18;
	$iconSizeY = 23;
	$iconAnchorX = 04;
	$iconAnchorY = 16;
	$statusCodeClosed = "2";
	$statusCodeWillNicht = "3";
	$statusOpen = "open";
	$statusNotOpen = "notopen";
	$statusClosed = "closed";
	$statusWillNicht = "willnicht";
	
	$bauerUrlStart = "http://mostbauer.com/index.php?option=com_wrapper&Itemid=2&bauerPageType=schmalzbrotdetail&bauerID=";

	if ( isset($_REQUEST["bauerDetail"]) ) {
		returnBauerDetailDiv($_REQUEST["bauerDetail"]);
	} else {
		$returnData = fetchReturnData($_REQUEST);
		if ( isset($_REQUEST["format"]) && strtolower($_REQUEST["format"]) === "kml")
			returnKmlData($returnData);
		else
			returnJsonData($returnData);
	}
	

	
	
	
// fetch data to be returned to the client
function fetchReturnData($requestParams) {
	if( isset($_REQUEST["wochentag"]) ) {
		// display bauern according to wochentag
		$doCenter = isset($_REQUEST['doCenter']) ? $_REQUEST['doCenter'] : "true";
		$centerData = array("lat" => 48.306074, "lng" => 14.286293, "zoom" => 10, "doCenter" => $doCenter);
		$markerData = fetchMarkerData($_REQUEST["wochentag"]);
		return $returnData = array(
										"center" => $centerData,
										"marker" => $markerData,
										);  
	} else if( isset($_REQUEST["anfahrt"]) ) {
		// display anfahrt for given bauer
		$polygonData = fetchPolygonAnfahrtData($_REQUEST["anfahrt"]);
		$markerData = array();
		$markerData[] = getBauerMarkerDataOpen($_REQUEST["anfahrt"]);
		$firstPolygonPoint = reset($polygonData);
		$lastPolygonPoint = end($polygonData);
		// center and zoom so the first and last point are visible (always center)
		$centerData = array("first" => $firstPolygonPoint, "last" => $lastPolygonPoint, "zoom" => "bounds", "doCenter" => "true");
		return $returnData = array(
									"center" => $centerData,
									"marker" => $markerData,
									"polygon" => $polygonData,
									);  
	}
	
	
}

// fetch bauern-marker return data
function fetchMarkerData($wochentag) {
	
	if($wochentag != "alle") {
    $wochentagCode=$wochentag%7;
	}
	
	$itemsListArrayAll = fetch_bauern_region_list("");							// all bauern 
	$itemsListArrayOpen= fetch_bauern_region_list($wochentagCode);	// only open bauern
				 
	$markerData = array();
	// add bauern markers
	foreach($itemsListArrayAll as $ID => $regionName) {
    $markerData[] = getBauerMarkerData($ID, $itemsListArrayOpen);
	}
	
	return $markerData;
}

// generate anfahrt-polygon for given bauer-id
function fetchPolygonAnfahrtData($ID) {
	global $tablePrefix; 
	
	$polygonData = array();
	
	$itemResult=mysql_query("SELECT anfahrtpolygon FROM ".$tablePrefix."bauer WHERE ID={$ID}") or die ("mb_gmaps_backend.php;fetchPolygonAnfahrtData({$ID}): Database error (".mysql_error().")");
  list($anfahrtpolygon)=mysql_fetch_row($itemResult);
  
  foreach(explode("|", $anfahrtpolygon) as $curPolyData) {
  	list($latitude, $longitude) = explode(",", $curPolyData);
  	$polygonData[] = array("latitude" => $latitude, "longitude" => $longitude);
  }
		
	return $polygonData;
}

// return bauer-marker-data with bauer defined as open
function getBauerMarkerDataOpen($ID) {
	$itemsListArrayOpen = array($ID => "");
	return getBauerMarkerData($ID, $itemsListArrayOpen);
}

// generate marker-data for given bauer-id
function getBauerMarkerData($ID, $itemsListArrayOpen=array()) {
	global $tablePrefix, $bauerUrlStart, 
				 $iconOpen, $iconNotOpen, $iconClosed, $iconWillNicht, $iconSizeX, $iconSizeY, $iconAnchorX, $iconAnchorY,
				 $statusCodeClosed, $statusCodeWillNicht,
				 $statusOpen, $statusNotOpen, $statusClosed, $statusWillNicht;

	$itemResult=mysql_query("SELECT * FROM ".$tablePrefix."bauer WHERE ID={$ID}") or die ("mb_gmaps_backend.php;getBauerMarkerData({$ID}): Database error (".mysql_error().")");
  list($id, $name, $famName, $wegBeschr, $bild1, $bild2, $web, $email, $telefon, $adresse, $regionID, $zeiten, $strCoord, $wandCoord, $status, $infoText, $infoText2, $karte, $passwort, $latitude, $longitude, $anfahrtpolygon, $lastupdate, $lastupdatewho)=mysql_fetch_row($itemResult);

  $curStatus = $statusNotOpen;
  $curIcon   = $iconNotOpen;
    
  if ( $status === $statusCodeClosed ) {
    $curStatus = $statusClosed;
    $curIcon   = $iconClosed;
  } else if ( $status === $statusCodeWillNicht ) {
    $curStatus = $statusWillNicht;
    $curIcon   = $iconWillNicht;
  } else if ( array_key_exists($ID, $itemsListArrayOpen) ) {
    $curStatus = $statusOpen;
    $curIcon   = $iconOpen;
	} 
    
	return array(
					"id" => $id,
					"name" => $name,
					"status" => $curStatus,
					"region" => $regionID,
					"lat" => $latitude,
					"lng" => $longitude,
					"iconUrl" => $curIcon,
					"iconSizeX" => $iconSizeX,
					"iconSizeY" => $iconSizeY,
					"iconAnchorX" => $iconAnchorX,
					"iconAnchorY" => $iconAnchorY,
					"htmlInfo" => getHtmlInfoText($name, $famName, $infoText, $infoText2, $curStatus),
					"linkURL" => $bauerUrlStart . $id
					);
}

// generate html-code for bauer-popup
function getHtmlInfoText($name, $famName, $infoText, $infoText2, $status) {
	global $statusNotOpen, $statusWillNicht, $statusClosed;
	
	$bauerBezeichnung = $name . ($famName == "" ? "" : " - " . $famName);
	
	$returnString  = "";
	if ( $status == $statusNotOpen ) $returnString .= "<span class='notopennote'>Am gew&auml;hlten Tag nicht offen.</span><br/>";
	if ( $status == $statusWillNicht ) $returnString .= "<span class='notopennote'>Auf Wunsch des Bauern nicht gelistet.</span><br/>";
	if ( $status == $statusClosed ) $returnString .= "<span class='notopennote'>Dieser Bauer ist leider schon geschlossen.</span><br/>";
	$returnString .= "<span class='bauerheadline'>$bauerBezeichnung</span><br/>";
	$returnString .= "<span class='bauerinfo'>$infoText2</span>";
	
	return $returnString;
}

// returns the bauer-detail-box (div only for partial ajax-update on google-maps-page)
function returnBauerDetailDiv($bauerId) {
	header('Content-Type: text/html; charset=iso-8859-15');
	viewItemDetail($bauerId, "list");
}

// return data as JsonString
function returnJsonData($returnData) {
	header('Content-Type: application/json; charset=iso-8859-15');
	echo assocArray2json($returnData);
}

// return data as KML-representation
function returnKmlData($returnData) {
	//header("Content-Type: application/vnd.google-earth.kml+xml; encoding=utf-8");
	echo assocArray2kml($returnData);
}

// convert an assoc-array-tree into json representation
function assocArray2json($array) {
	if ( isNonAssocArray($array) )
		$returnString = "[";
	else
		$returnString = "{";

	$firstElem = true;
	
	foreach ($array as $key => $value) {
		// add item delimiter
		if ( $firstElem ) {
			$firstElem = false;
		} else {
			$returnString .= ", ";
		}
		
		// add key (no keys on non-assoc-arrays)
		if( !isNonAssocArray($array) ) {
			$returnString .= "\"" . $key . "\" : ";
		}
		
		// add value or recursion if array
		if ( is_array($value) ) {
			$returnString .= assocArray2json($value);
		} else {
			$valueUTF8 = mb_convert_encoding($value, "UTF-8");
			$valueJSONUTF8 = json_encode($valueUTF8);
			$valueJSONISO = mb_convert_encoding($valueJSONUTF8, "ISO-8859-15", "UTF-8");
			$returnString .= $valueJSONISO;
		}
	}
	
	if ( isNonAssocArray($array) )
		$returnString .= "]";
	else
		$returnString .= "}";
	
	return $returnString;
}

// convert an assoc-array-tree into kml representation
function assocArray2kml($array) {
	return "not-yet-implemented";
}

// how can we find out that the given array is not an assoc array ???
// THIS IS A WILD HACK (checking first key for being numeric -> non-assoc ...)
function isNonAssocArray($array) {
	$keys = array_keys($array);
	$firstKey = $keys[0];
	return is_numeric($firstKey);
}

?>
