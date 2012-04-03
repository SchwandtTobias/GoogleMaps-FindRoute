//Need Geocodes Static
var LocFHE = new google.maps.LatLng(50.984748, 11.041603);
var LocFHEStone = new google.maps.LatLng(50.985992, 11.037303);
var LocFHEGreen = new google.maps.LatLng(50.990631, 11.056892);
var LocFHESchlu = new google.maps.LatLng(50.984758, 11.023284);

var TargetLocArray = new Array();
TargetLocArray[0] = LocFHE;
TargetLocArray[1] = LocFHEGreen;
TargetLocArray[2] = LocFHEStone;
TargetLocArray[3] = LocFHESchlu;

var TargetStationArray = new Array();
TargetStationArray[0] = "Hanseplatz/Fachhochschule, Erfurt";
TargetStationArray[1] = "Erfurt, Leipziger Strasse";
TargetStationArray[2] = "Erfurt, Steinplatz";
TargetStationArray[3] = "Erfurt, Schluetterstasse";


var TargetImgArray = new Array();
TargetImgArray[0] = 'typo3conf/ext/gmfindroute/pi1/img/altonaer.png';
TargetImgArray[1] = 'typo3conf/ext/gmfindroute/pi1/img/leipziger.png';
TargetImgArray[2] = 'typo3conf/ext/gmfindroute/pi1/img/steinplatz.png';
TargetImgArray[3] = 'typo3conf/ext/gmfindroute/pi1/img/schlueter.png';


var TargetLoc = LocFHE;
var TravelMode = google.maps.DirectionsTravelMode.DRIVING;

//GeoCode Browser
var initialLocation;
var geocoder;

//Images
var imgFHE = 'typo3conf/ext/gmfindroute/pi1/img/logo_fhe_50_marker.png';

//Directions Service and Renderer
var directionDisplay;
var directionsService = new google.maps.DirectionsService();

//Marker Browser
var MarkBrowser;



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
      zoom: 6,
      disableDefaultUI: true,
      navigationControl: true,
      scaleControl: true,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
	
	//Create map and set to Div
    map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);
    
    //Set marker
    for (var iLocs = 0; iLocs < TargetLocArray.length; ++iLocs)
	{
		new google.maps.Marker({
		      position: TargetLocArray[iLocs], 
		      map: map, 
		      title:"Fachhochschule Erfurt",
		      icon: TargetImgArray[iLocs]
		  });
	}
    
    
    //Set Renderer
    directionsDisplay.setMap(map);
    
    initialLocation = LocFHE;
	map.setCenter(initialLocation);
    
    // Try W3C Geolocation (Preferred)
	if(navigator.geolocation) {
		
	    browserSupportFlag = true;
	  
	    navigator.geolocation.getCurrentPosition(function(position) {
	    initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
	    map.setCenter(initialLocation);
	    
	    handleFoundGeolocation();
		
		// added by sl
		calcRoute();
	    
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
		alert("Bitte deinen Standort eintragen.");
		return;
	}
	
	directionsDisplay.setMap(map);
	
	
	var start = initialLocation;
	
	//Set endpoint
	  var request = {
	    origin:start, 
	    destination:TargetLoc,
	    travelMode: TravelMode
	  };
	
	  directionsService.route(request, function(result, status) {
	    if (status == google.maps.DirectionsStatus.OK) {
	      directionsDisplay.setDirections(result);
	      
	    //Catch Address for Browse
	      document.getElementById("route_distance").innerHTML = result.routes[0].legs[0].distance.text;
		  document.getElementById("route_time").innerHTML = result.routes[0].legs[0].duration.text;
	      document.getElementById("map_information").setAttribute("style", "display: block;");
	    }
	  });
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
		  
		  //added by sl
		  calcRoute();
          
        } else {
          alert("Es konnte keine Abfrage gestartet wegen wegen: " + status + ". Bitte wenden sie sich and ihren Systemadministrator.");
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
  
  //added by sl
  calcRoute();
}


function OnChangeTravelMode(mode)
{
	TravelMode = google.maps.DirectionsTravelMode.DRIVING;
	
	document.getElementById('DRIVING').removeAttribute("class");
	document.getElementById('WALKING').removeAttribute("class");
	document.getElementById('BICYCLING').removeAttribute("class");
	
	/*
	  if(document.getElementById("mode").value == "WALKING")
	  {
		  TravelMode = google.maps.DirectionsTravelMode.WALKING;
	  }
	  else if(document.getElementById("mode").value == "BICYCLING")
	  {
		  TravelMode = google.maps.DirectionsTravelMode.WALKING;
	  }*/
	  
	// changed by sl
	if(mode == "WALKING")
	{
		TravelMode = google.maps.DirectionsTravelMode.WALKING;
	}
	else if(mode == "BICYCLING")
	{
		TravelMode = google.maps.DirectionsTravelMode.WALKING;
	}
	
	document.getElementById(mode).setAttribute("class", "active");
	
	calcRoute();
}





