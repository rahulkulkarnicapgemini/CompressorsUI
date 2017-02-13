
/* siteDetailsView.js file:
***	- createSiteDetails: Function to show details of each compressor on each sites
***	- callGetSiteDetails: Ajax GET call to get the site and compressor under those site details
***	- init: To call the site details ajax function
*/

if (typeof IoTDashboard === 'undefined') {
	IoTDashboard = {};
}

IoTDashboard.siteDetailsView = (function($, services){
	
	//Show different compressors properties under each sites 
	var createSiteDetails = function (index, data) {
		var siteDetailsResult = data.siteDetails[index];
		var compDetails = '<div class="compressorStatusContainerCls" style="text-align:center;">'+
				'<div class="row compressorTitleCls">'+
					'<label><h4><b>'+data.siteDetails[index].siteName+'</h4></b></label>'+
				'</div>'+
			'</div>';
		var i, len = siteDetailsResult.compressors.length;
		for(i=0; i<len; i++) {
			var statusImg;
			if(siteDetailsResult.compressors[i].readyStatus == "Ready") {
				statusImg = "app/images/status_green.png";
			}
			else {
				statusImg = "app/images/status_red.png";
			}
			compDetails = compDetails + '<div class="compressorStatusContainerCls">'+
				'<div class="row compressorTitleCls">'+
					'<img src='+statusImg+' class="infoWindowStatusCls"/>&nbsp;&nbsp;'+
					'<label><h5><b>'+siteDetailsResult.compressors[i].compressorName+'</h5></b></label>'+
				'</div>'+
				'<div class="row compressorStatusDetailsCls">'+
					'<div class="col-xs-4 col-sm-2 col-md-2 col-lg-2 statusColCls">'+
						'<label>Ready Status</label><br>'+
						'<label class="statusLabelCls">'+siteDetailsResult.compressors[i].readyStatus+'</label>'+
					'</div>'+
					'<div class="col-xs-4 col-sm-2 col-md-2 col-lg-2 statusColCls">'+
						'<label>Remote</label><br>'+
						'<label class="statusLabelCls">'+siteDetailsResult.compressors[i].remote+'</label>'+
					'</div>'+
					'<div class="col-xs-4 col-sm-2 col-md-2 col-lg-2 statusColCls">'+
						'<label>Loaded</label><br>'+
						'<label class="statusLabelCls">'+siteDetailsResult.compressors[i].loaded+'</label>'+
					'</div>'+
					'<div class="col-xs-4 col-sm-2 col-md-2 col-lg-2 statusColCls">'+
						'<label>Warnings</label><br>'+
						'<label class="statusLabelCls">'+siteDetailsResult.compressors[i].warnings+'</label>'+
					'</div>'+
					'<div class="col-xs-4 col-sm-2 col-md-2 col-lg-2 statusColCls">'+
						'<label>Alarms</label><br>'+
						'<label class="statusLabelCls">'+siteDetailsResult.compressors[i].alarms+'</label>'+
					'</div>'+
				'</div>'+
			'</div>';
		}
		$("#siteDetailsId").empty();
		$("#siteDetailsId").append(compDetails);
	};
	
	////Call siteDetails web service to get the details of different sites and compressors under each sites
	var callGetSiteDetails = function (index) {
		
		var urlPath = services.getSiteDetails();
		$.ajax({
			url: urlPath, 
			method: 'GET',
			timeout: 30000,
			success: function(resultJson){
				$("#wait").hide();
				var result = typeof resultJson != 'object' ? JSON.parse(resultJson) : resultJson;
				createSiteDetails(index, result);
			},
			error: function(error) {
				$("#wait").hide();
				console.log(error);
			}
		});
	};
	
	//call the site details web service
	var init = function (index) {
		callGetSiteDetails(index);
	};
	
	return {
		createSiteDetails: createSiteDetails,
		callGetSiteDetails: callGetSiteDetails,
		init: init
	};
	
}($, IoTDashboard.services));


    