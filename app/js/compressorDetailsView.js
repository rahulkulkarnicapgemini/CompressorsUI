
/* compressorDetailsView.js file:
***	- renderChart: To render the historic charts and table based on the date selection
***	- plotSystemPressureGauge: To configure and render the Pressure gauge widget to show system pressure value
***	- plotStage1TempGauge: To configure and render the Temperature gauge widget to show stage 1 temperature value
***	- plotStage2TempGauge: To configure and render the Temperature gauge widget to show stage 2 temperature value
***	- createCompressorOverview: To show the compressor details/properties in tabular format
***	- showCompressorStatusDetails: To show the compressor status details
***	- showCompressorDetails: To call all the function to show the compressor detail view
***	- callGetCompressorDetails: Ajax call to get compressor details
***	- callGetCompressorProperties: Ajax call to get compressor properties details
***	- downloadCSV: Function to download the historic chart data as csv file
***	- convertJSONToCSV:Function to convert the historic json data to csv data 
***	- onClickDownloadCSV: Function to call when download CSV button is clicked
***	- getDate: To get the data in mm/dd/yy format for the datepicker fields
***	- onClickUpdateData: Function to call when update data button is clicked
***	- init: Ajax calls to get compressor properties data(real-time & historical) and add event handlers to configure and open datepicker
*/

if (typeof IoTDashboard === 'undefined') {
	IoTDashboard = {};
}

IoTDashboard.compPropertiesJSON = {};
IoTDashboard.compPropertiesData  = {
									compressorId:"",
									compressorName:"",
									compressorProperties: []
								};
IoTDashboard.compressorDetailsView = (function($, services){

	//To plot the historic chart and table
	var renderChart = function (data) {
		var systemPressureData = [];
		var stage1TempData = [];
		var stage2TempData = [];
		var i, len = data.compressors.compressorProperties.length;
		var tableContent='<tr>'+
				'<td>Date Time</td>'+
				'<td>System Pressure</td>'+
				'<td>Stage 1 Temperature</td>'+
				'<td>Stage 2 Temperature</td>'+
			'</tr>';
		var compProperties = data.compressors.compressorProperties;
		IoTDashboard.compPropertiesData.compressorId = data.compressors.compressorId;
		IoTDashboard.compPropertiesData.compressorName = data.compressors.compressorName;
		IoTDashboard.compPropertiesData.compressorProperties = [];
		for(i=0; i<len; i++) {
			var timestamp = parseInt(compProperties[i].timestamp)*1000; //convert epoch time to timestamp
			var d = new Date(timestamp);
			IoTDashboard.compPropertiesData.compressorProperties.push(data.compressors.compressorProperties[i]);
			systemPressureData.push({x: timestamp, y: parseFloat(compProperties[i].systemPressure)});
			stage1TempData.push({x: timestamp, y: parseFloat(compProperties[i].stage1Temperature)});
			stage2TempData.push({x: timestamp, y: parseFloat(compProperties[i].stage2Temperature)});
			var datestring = d.getDate()  + "/" + (d.getMonth()+1) + "/" + d.getFullYear() + " " +d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
			tableContent = tableContent + '<tr>'+
				'<td>'+datestring+'</td>'+
				'<td>'+compProperties[i].systemPressure+'</td>'+
				'<td>'+compProperties[i].stage1Temperature+'</td>'+
				'<td>'+compProperties[i].stage2Temperature+'</td>'+
			'</tr>';
		}
		$("#compressorPropertiesTbId").empty();
		
		var chart = new CanvasJS.Chart("chartContainerId",
		{
			title:{
				text: "Compressor Properties",
				fontSize: 30
			},
			animationEnabled: true,
			axisX:{

				gridColor: "Silver",
				tickColor: "silver",
				valueFormatString: "DD MMM HH:mm",
				labelAngle: -50       
			},                        
			toolTip:{
				shared:true
			},
			theme: "theme2",
			axisY: {
				gridColor: "Silver",
				tickColor: "silver"
			},
			legend:{
				verticalAlign: "center",
				horizontalAlign: "right"
			},
			data: [
			{        
				type: "line",
				xValueType: "dateTime",
				showInLegend: true,
				lineThickness: 2,
				name: "System Pressure",
				markerType: "square",
				color: "#F08080",
				dataPoints: systemPressureData
			},
			{        
				type: "line",
				xValueType: "dateTime",
				showInLegend: true,
				name: "Stage 1 Temperature",
				color: "#20B2AA",
				lineThickness: 2,
				dataPoints: stage1TempData
			},
			{        
				type: "line",
				xValueType: "dateTime",
				showInLegend: true,
				name: "Stage 2 Temperature",
				color: "#9BBB58",
				lineThickness: 2,
				dataPoints: stage2TempData
			}
			],
			legend:{
				cursor:"pointer",
				itemclick:function(e){
				  if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
					e.dataSeries.visible = false;
				  }
				  else{
					e.dataSeries.visible = true;
				  }
				  chart.render();
				}
			  }
			});
		if(IoTDashboard.compPropertiesData.compressorProperties.length > 0) {
			$("#chartContainerId").show("slow", function(){
				chart.render();
			});
			$("#compressorPropertiesTbId").append(tableContent);
		}
		else {
			alert("No data available. Change the data range.");
		}
	};
	
	var plotSystemPressureGauge = function (data) {
		$("#pressureGaugeLabelId").html(data.compressors.systemPressure+" psi");
		var opts = { 
			lines: 12, 
			angle: 0.15, 
			lineWidth: 0.44, 
			pointer: { 
				length: 0.9, 
				strokeWidth: 0.035, 
				color: '#000000' 
			},
			limitMax: 'false', 
			percentColors: [[0.0, "#a9d70b" ], [0.50, "#f9c802"], [1.0, "#ff0000"]],
			strokeColor: '#E0E0E0',
			generateGradient: true
		};
		var target = document.getElementById('pressureGauge0');
		var gauge = new Gauge(target).setOptions(opts);
		gauge.maxValue = 210;
		gauge.animationSpeed = 1;
		gauge.set(data.compressors.systemPressure);
	};
	
	//To plot the stage 1 Temperature gauge
	var plotStage1TempGauge = function (data) {
		$("#tempGauge1LabelId").html(data.compressors.stage1Temperature+" &deg;C");
		$(".tempGauge0").tempGauge({
			width:150, 
			borderColor: "#696969",
			borderWidth:2,
			defaultTemp: 150,
			maxTemp: 520,
			minTemp: 0,
			fillColor: "#32cd32",
			labelSize: 12,
			showLabel:true, 
			showScale : true
		});
	};
	
	//To plot the stage 2 Temperature gauge
	var plotStage2TempGauge = function (data) {
		$("#tempGauge2LabelId").html(data.compressors.stage2Temperature+" &deg;C");
		$(".tempGauge1").tempGauge({
			width:150, 
			borderColor: "#696969",
			borderWidth:2,
			defaultTemp: 0,
			maxTemp: 520,
			minTemp: 0,
			fillColor: "#ffd700",
			labelSize: 12,
			showLabel:true, 
			showScale : true
		});
	};
	
	//To show compressor overview table 
	var createCompressorOverview = function (data) {
		$("#systemPressureTrId").html(data.compressors.systemPressure+" psi");
		$("#oilPressureTrId").html(data.compressors.oilPressure+" psi");
		$("#oilTempTrId").html(data.compressors.oilTemperature+" &deg;C");
		$("#stage1TempTrId").html(data.compressors.stage1Temperature+" &deg;C");
		$("#stage1VibrationTrId").html(data.compressors.stage1Vibration+" mil");
		$("#stage1PressureTrId").html(data.compressors.stage1Pressure+" psi");
		$("#stage2TempTrId").html(data.compressors.stage2Temperature+" &deg;C");
		$("#stage2VibrationTrId").html(data.compressors.stage2Vibration+" mil");
		$("#stage2PressureTrId").html(data.compressors.stage2Pressure+" psi");
	};
	
	//To show compressor status details 
	var showCompressorStatusDetails = function (data) {
		$("#compressorTitleId").html('<h5><b>'+data.compressors.compressorName+'</b></h5>');
		if(data.compressors.readyStatus == "Ready") {
			$('#imgCompressorStatusId').attr('src','app/images/status_green.png');
		}
		else {	
			$('#imgCompressorStatusId').attr('src','app/images/status_red.png');
		}
		$("#chkReadyStatusId").text(data.compressors.readyStatus);
		$("#chkRemoteId").text(data.compressors.remote);
		$("#chkLoadedId").text(data.compressors.loaded);
		$("#chkWarningId").text(data.compressors.warnings);
		$("#chkAlarmsId").text(data.compressors.alarms);
	};
	
	//To call all the functions to show the compressor details view
	var showCompressorDetails = function(data) {
		$("#compressorDetailId").tabs();
		showCompressorStatusDetails(data);
		plotSystemPressureGauge(data);
		plotStage1TempGauge(data);
		plotStage2TempGauge(data);
		createCompressorOverview(data);
	};
	
	//Ajax call to get the compressor details of respective compressor based on the site Id and compressor Id
	var callGetCompressorDetails = function (siteId, compId) {
		var urlPath = services.getCompressorDetails(siteId, compId);
		$.ajax({
			url: urlPath,
			method: 'GET',
			timeout: 30000,
			success: function(resultJson){
				$("#wait").hide();
				var result = typeof resultJson != 'object' ? JSON.parse(resultJson) : resultJson;
				IoTDashboard.compPropertiesJSON = result;
				showCompressorDetails(result);
			},
			error: function(error) {
				$("#wait").hide();
				console.log(error);
			}
		});
	};
	
	//Ajax call to get the historic compressor data
	var callGetCompressorProperties = function (siteId, compId, fromDate, toDate) {
		$("#wait").show();
		var urlPath = services.getCompressorPropertiesTime(siteId, compId, fromDate, toDate);
		$.ajax({
			url: urlPath, 
			method: 'GET',
			timeout: 30000,
			success: function(resultJson){
				$("#wait").hide();
				var data = typeof resultJson != 'object' ? JSON.parse(resultJson) : resultJson;
				if(data.compressors.compressorProperties.length <= 100000) { //1 lac data points can be plotted on the charts
					renderChart(data);
				}
				else {
					alert("Data points are more than 1 lac. Please reduce the selected date range values.");
				}
			},
			error: function(error) {
				$("#wait").hide();
				console.log(error);
			}
		});
	};
	
	//Function to download the historic file as .csv
	var downloadCSV = function (content, fileName, mimeType) {
		var a = document.createElement('a');
		var mimeType = mimeType || 'application/octet-stream';

		if (navigator.msSaveBlob) { // IE10
			return navigator.msSaveBlob(new Blob([content], { type: mimeType }), fileName);
		} 
		else if ('download' in a) { //html5 A[download]
			a.href = 'data:' + mimeType + ',' + encodeURIComponent(content);
			a.setAttribute('download', fileName);
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			return true;
		}
		else { //do iframe dataURL download (old ch+FF):
			var f = document.createElement('iframe');
			document.body.appendChild(f);
			f.src = 'data:' + mimeType + ',' + encodeURIComponent(content);

			setTimeout(function() {
			  document.body.removeChild(f);
			}, 333);
			return true;
		}
	};
	
	//Function to convert JSON date to CSV data
	var convertJSONToCSV = function (objArray) {
        var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
        var str = 'Date Time,System Pressure,Stage 1 Temperature,Stage 2 Temperature\r\n';
		
        for (var i = 0; i < array.length; i++) {
			var line = '';
            for (var index in array[i]) {
                if (line != '') line += ','
				if(index == "timestamp") {
					var d = new Date(parseInt(array[i][index])*1000); //convert epoch time to timestamp
					var datestring = d.getDate()  + "/" + (d.getMonth()+1) + "/" + d.getFullYear() + " " +d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
					line += datestring;
				}
				else {
					line += array[i][index];
				}
            }
            str += line + '\r\n';
        }
        return str;
    };

	//On click of download csv button call downloadCSV function
	var onClickDownloadCSV = function () {
		if(IoTDashboard.compPropertiesData.compressorProperties.length > 0) {
			var csvContent = convertJSONToCSV(IoTDashboard.compPropertiesData.compressorProperties);
			downloadCSV(csvContent, IoTDashboard.compPropertiesData.compressorName+'.csv', 'text/csv');
		}
		else {
			alert("No data available. Change the data range.");
		}
	};
	
	//Returns date in a mm/dd/yy format
	var getDate = function ( element ) {
		var date, dateFormat = "mm/dd/yy";
		try {
			date = $.datepicker.parseDate( dateFormat, element.value );
		} catch( error ) {
			date = null;
		}
		return date;
    };
	
	//On click of update data button plot the charts and table
	var onClickUpdateData = function () {
		var siteId = IoTDashboard.compPropertiesJSON.compressors.siteId;
		var compId = IoTDashboard.compPropertiesJSON.compressors.compressorId;
		var fromDate = $( "#from" ).val();
		var toDate = $( "#to" ).val();
		if(fromDate != "" && toDate != "") {
			var epochFromDate = (Date.parse(fromDate))/1000; //convert timestamp to epoch time
			var epochToDate = (Date.parse(toDate))/1000;
			callGetCompressorProperties(siteId, compId, epochFromDate, epochToDate);
		}
		else {
			alert("Selected date range is invalid.");
		}
	};	
	
	//Call to get compressor details and initialize add event handle for date range fields
	var init = function (siteId, compId) {
		callGetCompressorDetails(siteId, compId);
		//IoTDashboard.timerId = setInterval(function(){ callGetCompressorDetails(siteId, compId) }, 5000);
		
		from = $( "#from" )
			.datepicker({
				//defaultDate: "+1w",
				changeMonth: true,
				numberOfMonths: 1,
				changeYear: true
			})
			.on( "change", function() {
			  to.datepicker( "option", "minDate", getDate( this ) );
			}),
      
		to = $( "#to" )
			.datepicker({
				//defaultDate: "+1w",
				changeMonth: true,
				numberOfMonths: 1,
				changeYear: true
			})
			.on( "change", function() {
				from.datepicker( "option", "maxDate", getDate( this ) );
			});
    };
	
	return {
		renderChart: renderChart,
		plotSystemPressureGauge: plotSystemPressureGauge,
		plotStage1TempGauge: plotStage1TempGauge,
		plotStage2TempGauge: plotStage2TempGauge,
		createCompressorOverview: createCompressorOverview,
		showCompressorStatusDetails: showCompressorStatusDetails,
		showCompressorDetails: showCompressorDetails,
		callGetCompressorDetails: callGetCompressorDetails,
		callGetCompressorProperties: callGetCompressorProperties,
		downloadCSV: downloadCSV,
		convertJSONToCSV: convertJSONToCSV,
		onClickDownloadCSV: onClickDownloadCSV,
		getDate: getDate,
		onClickUpdateData: onClickUpdateData,
		init: init
	};
}($, IoTDashboard.services));
	