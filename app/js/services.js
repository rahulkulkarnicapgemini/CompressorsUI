/* services.js file:
***	- getSiteDetails: Web service URL to get site & compressor details 
***	- getCompressorDetails: Web service URL to get specific compressor details 
***	- getCompressorPropertiesTime: Web service URL to get the specific compressor properties details against timestamp
*/

if (typeof IoTDashboard === 'undefined') {
	IoTDashboard = {};
}

IoTDashboard.services = (function($){
	
	//Service url to return the site and compressor details
	var getSiteDetails = function () {
		var urlPath = 'app/stubs/siteDetails.json'; //stub url
		//var urlPath = 'http://samplephpmysql.mybluemix.net/siteservice.php?service=SITEDATA'; //bluemix web service url
		return urlPath;
	};
	
	//Service url to return only the selected compressor details
	var getCompressorDetails = function (siteId, compId) {
		var urlPath = 'app/stubs/compressor'+compId+'.json'; //stub url
		//var urlPath = ''; //bluemix web service url
		return urlPath;
	};
	
	//Service url to return only the selected compressor properties details against timestamp
	var getCompressorPropertiesTime = function (siteId, compId, fromDate, toDate) {
		var urlPath = 'app/stubs/compressorProperties'+compId+'.json'; //stub url
		//var urlPath = ''; //bluemix web service url
		return urlPath;
	};
	
	return {
		getSiteDetails: getSiteDetails,
		getCompressorDetails: getCompressorDetails,
		getCompressorPropertiesTime: getCompressorPropertiesTime
	};
	
}($));


    