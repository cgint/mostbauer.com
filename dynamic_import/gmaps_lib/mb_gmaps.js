  google.load("maps", "2.x");

  var mbgm;
  var bauerList = new Object();
  
  // initialize the map using it as map (instead of anfahrt)
  function mbgm_initialize_map_map() {
	  mbgm_initialize_map();
	  mbgm_fetchBauern(wochentag, true);
  }
  
  // initialize the map using it as map (instead of anfahrt)
  function mbgm_initialize_map_anfahrt() {
	  mbgm_initialize_map();
	  mbgm_fetchBauernAnfahrt(anfahrtId);
  }
   
  // initialize google-map-element (can then be used as map or anfahrt)
  function mbgm_initialize_map() {
	  mbgm = new google.maps.Map2(document.getElementById("map"));
	  
	  mbgm.enableDoubleClickZoom();
	  mbgm.enableScrollWheelZoom();
	  
	  mbgm.addControl(new GSmallMapControl(), G_ANCHOR_TOP_LEFT);

	  $.ajaxSetup({ scriptCharset: "iso-8859-15" , contentType: "application/json; charset=iso-8859-15"});
	  
	  $(document).ajaxError(function(e, xhr, settings, exception) {
		  alert('error in: ' + settings.url + ' \\n'+'error:\\n' + exception);
	  }); 
  }
  

  // fetch bauern-list from backend and display it on google-map
  function mbgm_fetchBauern(wochentag, doCenter) {
	if (typeof(doCenter) == 'undefined' ) doCenter = false;
  	mbgm_clearMap();
  	mbgm_selectWochentag(wochentag);
  	$('#actionBoxWorking').show();
  	$.getJSON(gmapsBackendURL, {wochentag: wochentag, doCenter : doCenter}, mbgm_processResponse);
  }

  // fetch bauern-list from backend and display it on google-map
  function mbgm_fetchBauernAnfahrt(bauerId) {
  	mbgm_clearMap();
  	$('#actionBoxWorking').show();
  	$.getJSON(gmapsBackendURL, {anfahrt : bauerId}, mbgm_processResponse);
  }

  // jump to bauer with given id and open html-infowindow
  function mbgm_showBauer(bauerId) {
	  var bauerMarker = bauerList["marker"+bauerId];
//	  var bauerMarkerInfo = bauerList["markerInfo"+bauerId];
	  
	  // done in google-infowindow-show
	  //mbgm_selectBauer(bauerId);
	  
	  if ( typeof(bauerMarker) != "undefined" ) {
		  GEvent.trigger(bauerMarker,'click');
	  } else {
		  alert("unable to highlight given bauer - was not loaded from backend!");
	  }
// click-event does the moving
//	  var bauerLatLng = bauerMarker.getLatLng();
//	  mbgm.setCenter(bauerLatLng);



  }
  
  // do select the given bauer (actionbox and reload detail-div)
  function mbgm_selectBauer(bauerId) {
	  mbgm_highlightBauerInActionBox(bauerId);
	  mbgm_viewBauerInDetailView(bauerId);
  }
  
  // show bauer in detail div
  function mbgm_viewBauerInDetailView(bauerId) {
	  if ( !document.getElementById("bauerDetail") ) return;	// only if this element is present

	  $('#bauerDetail').hide();
	  if(bauerId) {
		  $.get(gmapsBackendURL, {bauerDetail : bauerId}, function(data) {
			    $("#bauerDetail").html(data);
			    $('#bauerDetail').show();
			    mbgm_adaptIframeHeight();
		  });

	  } else {	// if no bauer is selected we have to adapt height to avoid empty space at page-bottom
		  mbgm_adaptIframeHeight();
	  }

  }
  
  // adapt parent-iframe if function is available
  function mbgm_adaptIframeHeight() {
	  if ( (typeof(top) !== 'undefined') && (typeof (top.iFrameHeight) === 'function') ) top.iFrameHeight();
  }
  
  // remove highlighting from active bauer and highligh bauer with given id (no id -> all highlighting removed)
  function mbgm_highlightBauerInActionBox(bauerId) {
	  if ( !document.getElementById("actionBox") ) return;	// only if this element is present
	  // highlight bauer in actionBox
	  $('#actionBox .active').removeClass('active');
	  if(bauerId) {
		  $('#action_'+bauerId).addClass('active');
		  // position the actioBox scroll-position so the bauer-item is visible (after click in map)
		  //alert("bauerId="+bauerId + "/top=" + document.getElementById('actionBox').offsetParent.offsetTop);
		  var itemOffset = document.getElementById('action_'+bauerId).offsetTop-60;	// correction -60 needed (dunno why)
		  var viewTop = $('#actionBox').scrollTop();
		  var viewBottom = viewTop + $('#actionBox').height() - $('#action_'+bauerId).height();
		  
		  // only if not already visible
		  if ( itemOffset < viewTop || itemOffset > viewBottom ) $('#actionBox').scrollTop(itemOffset);
	  }
  }

// do something on ajax update-response
function mbgm_processResponse(jsonResponse) {
  	$('#actionBoxWorking').hide();
	mbgm_updateMap(jsonResponse);

	if ( firstRequest && typeof(initBauerId) != "undefined" ) {
		mbgm_showBauer(initBauerId);
	}
	
	firstRequest=false;
}

//clear wochentag-active class and set current
function mbgm_selectWochentag(wochentag) {
	mbgm_selectBauer();	// deselect
	
	$('#weekdaySelect .active').removeClass('active');
	$('#weekdaySelect_'+wochentag).addClass('active');
}

// clear map-content
function mbgm_clearMap() {
	// clear google map
	mbgm.clearOverlays();
	
	// hide region-entries and bauern-actionItems
	$('#actionBox li').hide();
}

// update all (set center/zoom, paint markers, paint polygons (not-yet-implemented))
function mbgm_updateMap(updateInfo) {
	// paint markers
	if ( typeof(updateInfo.marker) !== "undefined" ) {
		for (var i = 0; i < updateInfo.marker.length; i++) {
			var markerInfo = updateInfo.marker[i];
			var marker = mbgm_createMarker(markerInfo);
			mbgm.addOverlay( marker );

			// store marker for later use (mbgm_showBauer)
			bauerList["markerInfo"+markerInfo.id] = markerInfo;
			bauerList["marker"+markerInfo.id] = marker;
			
			// display action-entry for this bauer 
			if ( markerInfo.status == "open" ) {
				$('#region_'+markerInfo.region).show();
				$('#action_'+markerInfo.id).show();
			}
		}
	}
	// paint polygons
	if ( typeof(updateInfo.polygon) !== "undefined" ) {
		var linePoints = [];
		for (var i = 0; i < updateInfo.polygon.length; i++) {
			var polygonInfo = updateInfo.polygon[i];
			linePoints.push(new GLatLng(polygonInfo.latitude, polygonInfo.longitude));
		}
		var gPolygon = new GPolyline(linePoints);
		var polygonBounds = gPolygon.getBounds();
		var polygonCenter = polygonBounds.getCenter();
		mbgm.addOverlay(gPolygon);
	}
	// set center
	if ( updateInfo.center.doCenter === "true" ) {
		if( typeof updateInfo.center.first !== "undefined" ) {
			// center for anfahrt (first and last point visible)
			var zoom = updateInfo.center.zoom;
			if ( zoom === "bounds" ) zoom = mbgm.getBoundsZoomLevel(polygonBounds); // zoom to fit polygon-bounds
			else zoom = parseInt(zoom);	// fixed zoom
			mbgm.setCenter(polygonCenter, zoom);
		} else {
			// center for wochentag - custom center and zoom
			mbgm.setCenter(new google.maps.LatLng(updateInfo.center.lat, updateInfo.center.lng), parseInt(updateInfo.center.zoom));
		}
	}
}

// create single marker-object from backend-marker-info
function mbgm_createMarker(markerInfo) {
	var markerIcon = new GIcon(G_DEFAULT_ICON, markerInfo.iconUrl);
	markerIcon.shadow = "";
	markerIcon.iconSize = new GSize(markerInfo.iconSizeX, markerInfo.iconSizeY);
	markerIcon.iconAnchor = new GPoint(markerInfo.iconAnchorX, markerInfo.iconAnchorY);
	var markerOptions = {icon : markerIcon, title : markerInfo.name};
	var markerLatLng = new google.maps.LatLng(markerInfo.lat, markerInfo.lng);
	var marker = new GMarker(markerLatLng, markerOptions);
    marker.bindInfoWindowHtml( mbgm_getHtmlInfoContent(markerInfo), {noCloseOnClick : false} );

    GEvent.addListener(marker, "infowindowopen"	, function() {mbgm_selectBauer(markerInfo.id);}) 
    GEvent.addListener(marker, "infowindowclose", function() {mbgm_selectBauer();}) 
	
    return marker;
}

// create (layout) html-popup-info from backend-marker-info
function mbgm_getHtmlInfoContent(markerInfo) {
	return "<div class='bauerhtmlinfo'>" + markerInfo.htmlInfo + "<br/><a href='" + markerInfo.linkURL + "'>Link</a></div>";
}
