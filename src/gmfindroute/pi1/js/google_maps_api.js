var TargetLoc;
var TravelMode = google.maps.DirectionsTravelMode.DRIVING;

//GeoCode Browser
var initialLocation;
var geocoder;

//Images
var txtAlertPlace = 'Bitte deinen Standort eintragen.';

//Directions Service and Renderer
var directionDisplay;
var directionsService = new google.maps.DirectionsService();

//Marker Browser
var MarkBrowser;


//Events
var EventClickArray = new Array();


//Standard settings
var _mapZoom = 6;
var _disableDefaultUI = true;
var _navigationControl = true;
var _scaleControl = true;


//Initialize Google Map with Start and Endpoint
function initialize() {
	//Init GeoCoder
	geocoder = new google.maps.Geocoder();
	
	var myOptions4Route = {
      suppressMarkers: true
    };
	
	//Renderer for Directions
	directionsDisplay = new google.maps.DirectionsRenderer(myOptions4Route);
	
	//Some options needed for Design
	var myOptions = {
      zoom: _mapZoom,
      disableDefaultUI: _disableDefaultUI,
      navigationControl: _navigationControl,
      scaleControl: _scaleControl,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
	
	//Create map and set to Div
    map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);
    
    //Set marker
	
    for (var iLocs = 0; iLocs < TargetLocArray.length; ++iLocs)
	{
		EventClickArray[iLocs] = new google.maps.Marker({
		      position: TargetLocArray[iLocs], 
		      map: map, 
		      title: TargetTitleArray[iLocs],
		      icon: TargetMarkerArray[iLocs]
		  });
	}
    
    
    //Set Renderer
    directionsDisplay.setMap(map);
    
    initialLocation = TargetLoc;
	map.setCenter(initialLocation);
    
    // Try W3C Geolocation (Preferred)
	if(navigator.geolocation) {
		
	    browserSupportFlag = true;
	  
	    navigator.geolocation.getCurrentPosition(function(position) {
	    initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
	    map.setCenter(initialLocation);
	    
	    handleFoundGeolocation();
	    
	  }, function() {
	    handleNoGeolocation();
	  });
	} else {
	  handleNoGeolocation();
	}
	
	function handleFoundGeolocation() {
		MarkBrowser = new google.maps.Marker({
		      position: initialLocation, 
		      map: map
		  });
		 
		 var TargetLoc = TargetLocArray[document.getElementById("target").value];
		 
		 //Catch Address for Browser
			if (geocoder) {
			  geocoder.geocode({'latLng': initialLocation}, function(results, status) {
			  if (status == google.maps.GeocoderStatus.OK) {
				  if (results[1]) {
				    document.getElementById('city_name').value = results[1].formatted_address;
				    document.getElementById("train_link").href= "http://reiseauskunft.bahn.de/bin/query.exe/dn?S=" + results[1].formatted_address + "&Z=" + TargetStationArray[document.getElementById("target").value];
				  }
			  } else {
				  alert("Geocoder failed due to: " + status);
			  }
			  });
			  
					
			}
			document.getElementById("google_link").href = "http://maps.google.de/maps?saddr=" + initialLocation + "&daddr=" + TargetLoc;
	}
}

function calcRoute() {	
	
	if(document.getElementById('city_name').value == "") 
	{
		alert(txtAlertPlace);
		return;
	}
	
	directionsDisplay.setMap(map);
	
	
	var start = initialLocation;
	
	//Set endpoint
	  var request = {
	    origin: start, 
	    destination: TargetLoc,
	    travelMode: TravelMode
	  };
	
	  directionsService.route(request, function(result, status) {
	    if (status == google.maps.DirectionsStatus.OK) {
	      directionsDisplay.setDirections(result);
	      
	    //Catch Address for Browse
	      document.getElementById("route_distance").innerHTML = result.routes[0].legs[0].distance.text;
	      document.getElementById("map_information").setAttribute("style", "display: block;");
	    }
	  });
}


function ClickMarkerToSetTarget(value)
{
	document.getElementById("target").value = value;
	OnChangeTarget();
	calcRoute();
}

function OnChangeInput()
{
	//Change destination? No problem!
	//Catch Location in TextField & SetMarker
	address = document.getElementById("city_name").value;
	
	if (geocoder) {
      geocoder.geocode( { 'address': address }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
         
        	noGeoLocation = false;
          initialLocation = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
          
          if(MarkBrowser)
        	  {
		          MarkBrowser.setMap(null);
					  
        	  }
          
          MarkBrowser = new google.maps.Marker({
  		      position: initialLocation, 
  		      map: map, 
  		      title:address
  		  });
		  
		  document.getElementById("map_information").setAttribute("style", "display: none;");
		  directionsDisplay.setMap(null);
		  map.setZoom(6);
		  map.setCenter(initialLocation);
		  
		  //Set links
		  OnChangeTarget();
          
        } else {
          alert("Sorry. Error: " + status);
        }
      });
    }
}


function OnChangeTarget()
{
  TargetLoc = TargetLocArray[document.getElementById("target").value];
  
  //Show length for this route
  document.getElementById("google_link").href = "http://maps.google.de/maps?saddr=" + initialLocation + "&daddr=" + TargetLoc;
  document.getElementById("train_link").href= "http://reiseauskunft.bahn.de/bin/query.exe/dn?S=" + document.getElementById('city_name').value + "&Z=" + TargetStationArray[document.getElementById("target").value];
}


function OnChangeTravelMode()
{
	TravelMode = google.maps.DirectionsTravelMode.DRIVING;
	
	  if(document.getElementById("mode").value == "WALKING")
	  {
		  TravelMode = google.maps.DirectionsTravelMode.WALKING;
	  }
	  else if(document.getElementById("mode").value == "BICYCLING")
	  {
		  TravelMode = google.maps.DirectionsTravelMode.WALKING;
	  }
}





