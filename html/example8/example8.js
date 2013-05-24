
if (window.applicationCache != undefined) {
		// api can use
	if (applicationCache.status == applicationCache.UPDATEREADY) {
		// 以更新
		console.log('已更新，等待重新載入');
	} else {
		console.log('applicationCache.status = ' + applicationCache.status);
	}
} else {
		console.log('無法使用 api');
}

//Initialize
$(function() {
	
});

/*
 * Gmap Test
 */

var geocoder;
var map;
var markers = Array();
var infos = Array();

function initialize() {
	// prepare Geocoder
	geocoder = new google.maps.Geocoder();

	// set initial position (New York)
	var myLatlng = new google.maps.LatLng(40.7143528,-74.0059731);

	var myOptions = { // default map options
		zoom: 14,
		center: myLatlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	map = new google.maps.Map($('#gmap_canvas')[0], myOptions);
}

// clear overlays function
function clearOverlays() {
	if (markers) {
		for (i in markers) {
			markers[i].setMap(null);
		}
		markers = [];
		infos = [];
	}
}

// clear infos function
function clearInfos() {
	if (infos) {
		for (i in infos) {
			if (infos[i].getMap()) {
				infos[i].close();
			}
		}
	}
}

// find address function
function findAddress() {
	var address = $('#gmap_where').val();

	// script uses our 'geocoder' in order to find location by address name
	geocoder.geocode( { 'address': address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) { // and, if everything is ok

			// we will center map
			var addrLocation = results[0].geometry.location;
			map.setCenter(addrLocation);

			// store current coordinates into hidden variables
			//document.getElementById('lat').value = results[0].geometry.location.$a;
			//document.getElementById('lng').value = results[0].geometry.location.ab;
			$('#lat').val(results[0].geometry.location.$a);
			$('#lng').val(results[0].geometry.location.ab);

			// and then - add new custom marker
			var addrMarker = new google.maps.Marker({
				position: addrLocation,
				map: map,
				title: results[0].formatted_address,
				icon: 'marker.png'
			});
		} else {
			alert('Geocode was not successful for the following reason: ' + status);
		}
	});
}

// find custom places function
function findPlaces() {

	// prepare variables (filter)
	var type = $('#gmap_type').val();
	var radius = $('#gmap_radius').val();
	var keyword = $('#gmap_keyword').val();

	var lat = $('#lat').val();
	var lng = $('#lng').val();
	var cur_location = new google.maps.LatLng(lat, lng);

	// prepare request to Places
	var request = {
		location: cur_location,
		radius: radius,
		types: [type]
	};
	if (keyword) {
		request.keyword = [keyword];
	}

	// send request
	service = new google.maps.places.PlacesService(map);
	service.search(request, createMarkers);
}

// create markers (from 'findPlaces' function)
function createMarkers(results, status) {
	if (status == google.maps.places.PlacesServiceStatus.OK) {

		// if we have found something - clear map (overlays)
		clearOverlays();

		// and create new markers by search result
		for (var i = 0; i < results.length; i++) {
			createMarker(results[i]);
		}
	} else if (status == google.maps.places.PlacesServiceStatus.ZERO_RESULTS) {
		alert('Sorry, nothing is found');
	}
}

// creare single marker function
function createMarker(obj) {

	// prepare new Marker object
	var mark = new google.maps.Marker({
		position: obj.geometry.location,
		map: map,
		title: obj.name
	});
	markers.push(mark);

	// prepare info window
	var infowindow = new google.maps.InfoWindow({
		content: '<img src="' + obj.icon + '" /><font style="color:#000;">' + obj.name + 
		'<br />Rating: ' + obj.rating + '<br />Vicinity: ' + obj.vicinity + '</font>'
	});

	// add event handler to current marker
	google.maps.event.addListener(mark, 'click', function() {
		clearInfos();
		infowindow.open(map,mark);
	});
	infos.push(infowindow);
}

// initialization
google.maps.event.addDomListener(window, 'load', initialize);

/*
 * Audio Test 
 */
var audio_context;
var recorder;

function __log(e, data) {
	log.innerHTML += "\n" + e + " " + (data || '');
}

function startUserMedia(stream) {
	var input = audio_context.createMediaStreamSource(stream);
	__log('Media stream created.');
	
	input.connect(audio_context.destination);
	__log('Input connected to audio context destination.');
	
	recorder = new Recorder(input);
	__log('Recorder initialised.');
}

function startRecording(button) {
	recorder && recorder.record();
	//button.disabled = true;
	//button.nextElementSibling.disabled = false;
	__log('Recording...');
}

function stopRecording(button) {
	recorder && recorder.stop();
	//button.disabled = true;
	//button.previousElementSibling.disabled = false;
	__log('Stopped recording.');
	
	// create WAV download link using audio data blob
	createDownloadLink();
	
	recorder.clear();
}

function createDownloadLink() {
	recorder && recorder.exportWAV(function(blob) {
		var url = URL.createObjectURL(blob);
		var li = document.createElement('li');
		var au = document.createElement('audio');
		var hf = document.createElement('a');
		
		au.controls = true;
		au.src = url;
		hf.href = url;
		hf.download = new Date().toISOString() + '.wav';
		hf.innerHTML = hf.download;
		li.appendChild(au);
		li.appendChild(hf);
		recordingslist.appendChild(li);
	});
}

window.onload = function init() {
	try {
		// webkit shim
		window.AudioContext = window.AudioContext || window.webkitAudioContext;
		//navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia;
		// add mozilla & microsoft by wenkai 20130227
		navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
		window.URL = window.URL || window.webkitURL;
		
		audio_context = new AudioContext;
		__log('Audio context set up.');
		__log('navigator.getUserMedia ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
	} catch (e) {
		alert('No web audio support in this browser!');
	}
	
	navigator.getUserMedia({audio: true}, startUserMedia, function(e) {
		__log('No live audio input: ' + e);
	});
};

