
/* siteOverviewView.js file:
***	- onInfoBtnClick: Function to load respective site Detail view based on the menu list item clicked
***	- plotSitesOnMap: Plot site markers on the map and assign info window details
***	- callGetSiteDetails: Ajax GET call to get the site and compressor under those site details
***	- init: To call the site details ajax function
*/

if (typeof IoTDashboard === 'undefined') {
	IoTDashboard = {};
}

//On info window site details button clicked load the site details view 
function onInfoBtnClick(index) {
	$( "#siteMenuId" ).hide();
	$("#headerTitleId").text("Site Details");
	window.location.hash = "#siteDetails?id="+index;
}

IoTDashboard.siteOverviewView = (function($, services){
	
	// Plot the site markers on the map, assign their info window popup and attach click event on info window site details button
	var plotSitesOnMap = function (siteData) {
		var latitude = siteData.siteDetails[0].latitude;
		var longitude = siteData.siteDetails[0].longitude;
		var map = new google.maps.Map(document.getElementById('siteOverviewMapId'),{
			zoom: 7,
			center: new google.maps.LatLng(latitude, longitude),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		
		var i, siteLen = siteData.siteDetails.length;
		for (i = 0; i < siteLen; i++) {  
			var latitude = siteData.siteDetails[i].latitude;
			var longitude = siteData.siteDetails[i].longitude;
			var siteName = siteData.siteDetails[i].siteName;
			
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(latitude, longitude),
				icon: 'app/images/site_marker.png',
				map: map
			});
			
			var infowindow = new google.maps.InfoWindow();
			var redStatusImg = 'app/images/status_red.png';
			var greenStatusImg = 'app/images/status_green.png';
			
			var j, compLen = siteData.siteDetails[i].compressors.length;
			var compDiv = "";
			for (j = 0; j < compLen; j++) {  		
				if(siteData.siteDetails[i].compressors[j].readyStatus == "Ready") {
					compDiv = compDiv+'<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8 infoWindowColCls">'+
							'<img src='+greenStatusImg+' class="infoWindowStatusCls">&nbsp;&nbsp;'+
							siteData.siteDetails[i].compressors[j].compressorName+'</img></div>';
				}
				else {
					compDiv = compDiv+'<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8 infoWindowColCls">'+
							'<img src='+redStatusImg+' class="infoWindowStatusCls">&nbsp;&nbsp;'+
							siteData.siteDetails[i].compressors[j].compressorName+'</img></div>';
				}
			}
				
			var content = '<div class="infoWindowContainerCls">'+
					'<div class="row infoWindowHeaderCls">'+
						'<label>'+siteName+'</label>'+
					'</div>'+
					'<div class="row infoWindowContentCls">'+compDiv+
						'<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 infoWindowColCls">'+
							'<button type="button" class="infoWindowBtnCls" onclick="onInfoBtnClick('+i+')">Site Details</button>'+
						'</div>'+
					'</div>'+
				'</div>';
				
			google.maps.event.addListener(marker,'click', ( function (marker, content, infowindow){ 
				return function() {
					infowindow.setContent(content);
					infowindow.open(map,marker);
				};
			})(marker, content, infowindow));
		}
	};
	
	//Call siteDetails webservice to get the details of different sites and compressors under each sites and plot the sites on the map
	var callGetSiteDetails = function () {
		var urlPath = services.getSiteDetails();
		$.ajax({
			url: urlPath, 
			method: 'GET',
			timeout: 30000,
			success: function(resultJson){
				$("#wait").hide();
				var result = typeof resultJson != 'object' ? JSON.parse(resultJson) : resultJson;
				plotSitesOnMap(result);
			},
			error: function(error) {
				$("#wait").hide();
				console.log(error);
			}
		});
	};
	
	//call the site details webservice
	var init = function () {
		callGetSiteDetails();
	};
	
	return {
		plotSitesOnMap: plotSitesOnMap,
		callGetSiteDetails: callGetSiteDetails,
		init: init
	};
	
}($, IoTDashboard.services));


    