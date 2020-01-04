<?php
$settings = parse_ini_file(getenv("TTNMAPPER_HOME")."/settings.conf",true);


if($settings['theming']['gateway_online'] === NULL) {
  $gatewayMarkerOnline = "/resources/gateway_dot.png";
} else {
  $gatewayMarkerOnline = "/resources/".$settings['theming']['gateway_online'];
}

if($settings['theming']['gateway_online_not_mapped'] === NULL) {
  $gatewayMarkerOnlineNotMapped = "/resources/gateway_dot_green.png";
} else {
  $gatewayMarkerOnlineNotMapped = "/resources/".$settings['theming']['gateway_online_not_mapped'];
}

if($settings['theming']['gateway_offline'] === NULL) {
  $gatewayMarkerOffline = "/resources/gateway_dot_red.png";
} else {
  $gatewayMarkerOffline = "/resources/".$settings['theming']['gateway_offline'];
}

if($settings['theming']['gateway_single_channel'] === NULL) {
  $gatewayMarkerSingleChannel = "/resources/gateway_dot_yellow.png";
} else {
  $gatewayMarkerSingleChannel = "/resources/".$settings['theming']['gateway_single_channel'];
}


if($settings['theming']['gateway_icon_size_x'] === NULL) {
  $gatewayIconSizeX = "20";
} else {
  $gatewayIconSizeX = $settings['theming']['gateway_icon_size_x'];
}

if($settings['theming']['gateway_icon_size_y'] === NULL) {
  $gatewayIconSizeY = "20";
} else {
  $gatewayIconSizeY = $settings['theming']['gateway_icon_size_y'];
}

if($settings['theming']['gateway_icon_anchor_x'] === NULL) {
  $gatewayIconAnchorX = "10";
} else {
  $gatewayIconAnchorX = $settings['theming']['gateway_icon_anchor_x'];
}

if($settings['theming']['gateway_icon_anchor_y'] === NULL) {
  $gatewayIconAnchorY = "10";
} else {
  $gatewayIconAnchorY = $settings['theming']['gateway_icon_anchor_y'];
}

if($settings['theming']['gateway_popup_anchor_x'] === NULL) {
  $gatewayPopupAnchorX = "0";
} else {
  $gatewayPopupAnchorX = $settings['theming']['gateway_popup_anchor_x'];
}

if($settings['theming']['gateway_popup_anchor_y'] === NULL) {
  $gatewayPopupAnchorY = "0";
} else {
  $gatewayPopupAnchorY = $settings['theming']['gateway_popup_anchor_y'];
}


if($settings['theming']['map_start_lat'] === NULL) {
  $mapStartLat = "48.209661";
} else {
  $mapStartLat = $settings['theming']['map_start_lat'];
}

if($settings['theming']['map_start_lon'] === NULL) {
  $mapStartLon = "10.251494";
} else {
  $mapStartLon = $settings['theming']['map_start_lon'];
}

if($settings['theming']['map_start_zoom'] === NULL) {
  $mapStartZoom = "6";
} else {
  $mapStartZoom = $settings['theming']['map_start_zoom'];
}


if($settings['theming']['cluster_gateways'] === NULL) {
  $clusterGateways = true;
} else {
  $clusterGateways = $settings['theming']['cluster_gateways'];
}

if($settings['theming']['marker_cluster_radius'] === NULL) {
  $markerClusterRadius = "40";
} else {
  $markerClusterRadius = $settings['theming']['marker_cluster_radius'];
}

?>


var gatewayMarkerOnline = L.icon({
  iconUrl: "<?php echo $gatewayMarkerOnline; ?>",
  iconSize:     [<?php echo $gatewayIconSizeX; ?>, <?php echo $gatewayIconSizeY; ?>], // size of the icon
  iconAnchor:   [<?php echo $gatewayIconAnchorX; ?>, <?php echo $gatewayIconAnchorY; ?>], // point of the icon which will correspond to marker\'s location
  popupAnchor:  [<?php echo $gatewayPopupAnchorX; ?>, <?php echo $gatewayPopupAnchorY; ?>] // point from which the popup should open relative to the iconAnchor
});

var gatewayMarkerOnlineNotMapped = L.icon({
  iconUrl: "<?php echo $gatewayMarkerOnlineNotMapped; ?>",
  iconSize:     [<?php echo $gatewayIconSizeX; ?>, <?php echo $gatewayIconSizeY; ?>],
  iconAnchor:   [<?php echo $gatewayIconAnchorX; ?>, <?php echo $gatewayIconAnchorY; ?>],
  popupAnchor:  [<?php echo $gatewayPopupAnchorX; ?>, <?php echo $gatewayPopupAnchorY; ?>]
});

var gatewayMarkerOffline = L.icon({
  iconUrl: "<?php echo $gatewayMarkerOffline; ?>",
  iconSize:     [<?php echo $gatewayIconSizeX; ?>, <?php echo $gatewayIconSizeY; ?>],
  iconAnchor:   [<?php echo $gatewayIconAnchorX; ?>, <?php echo $gatewayIconAnchorY; ?>],
  popupAnchor:  [<?php echo $gatewayPopupAnchorX; ?>, <?php echo $gatewayPopupAnchorY; ?>]
});

var gatewayMarkerSingleChannel = L.icon({
  iconUrl: "<?php echo $gatewayMarkerSingleChannel; ?>",
  iconSize:     [<?php echo $gatewayIconSizeX; ?>, <?php echo $gatewayIconSizeY; ?>],
  iconAnchor:   [<?php echo $gatewayIconAnchorX; ?>, <?php echo $gatewayIconAnchorY; ?>],
  popupAnchor:  [<?php echo $gatewayPopupAnchorX; ?>, <?php echo $gatewayPopupAnchorY; ?>]
});

// Gateway markers are clustered together
var clusterGateways = "<?php echo $clusterGateways; ?>";
var gatewayMarkers = L.markerClusterGroup({
  maxClusterRadius: <?php echo $markerClusterRadius; ?>
});

// When less than this number of gateways are in view we display the full resolution coverage
var layerSwapGwCount = 300;
// If les than this number is shown we display a lower resolution coverage, ie. circles
var layerHideGwCount = 2000;
// Above this number we only display the gateway markers.

// The location to which the map will zoom to for a new user.
var initialCoords = [<?php echo $mapStartLat; ?>, <?php echo $mapStartLon; ?>];
var initialZoom = <?php echo $mapStartZoom; ?>;




function addBackgroundLayers() {
  // https: also suppported.
  var Esri_WorldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
    fadeAnimation: false
  });

  // https: also suppported.
  var Stamen_TonerLite = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}.{ext}', {
    attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    subdomains: 'abcd',
    minZoom: 0,
    maxZoom: 20,
    ext: 'png',
    fadeAnimation: false
  });


  var OpenStreetMap_Mapnik_Grayscale = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    fadeAnimation: false,
    className: 'toGrayscale'
  });

  // https: also suppported.
  var Esri_WorldShadedRelief = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Shaded_Relief/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri',
    maxZoom: 13,
    fadeAnimation: false
  });

  // https: also suppported.
  var OpenStreetMap_Mapnik = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    fadeAnimation: false
  });

  switch (findGetParameter("layer")) {
    case "mapnik":
      OpenStreetMap_Mapnik.addTo(map);
      break;
    case "mapnik_grayscale":
      OpenStreetMap_Mapnik_Grayscale.addTo(map);
      break;
    case "terrain":
      Esri_WorldShadedRelief.addTo(map);
      break;
    case "satellite":
      Esri_WorldImagery.addTo(map);
      break;
    default:
      // use default layer
      Stamen_TonerLite.addTo(map);
  }

  L.control.layers({
    "Stamen TonerLite": Stamen_TonerLite,
    "OSM Mapnik Grayscale": OpenStreetMap_Mapnik_Grayscale,
    "Terrain": Esri_WorldShadedRelief, 
    "OSM Mapnik": OpenStreetMap_Mapnik,
    "Satellite": Esri_WorldImagery
  })
  .addTo(map);
}