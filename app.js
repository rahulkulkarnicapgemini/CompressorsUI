/* app.js file:
***	- onMenuBtnClick: Function to load the site Details view on click of site Details menu button 
***	- createSiteMenu: Create the accordion menu list comprising of sites and compressors 
***   and attach click event handlers on the list items.
***	- callGetSiteDetails: Ajax GET call to get the site and compressor under those site details
***	- init: To call the Ajax function and load the site Overview view
***	- $(document).ready(): Start of the application, call to init and attach event handler on menu and home button 
***	- $(window).on('hashchange', function() {}): Load specific view as per window.location.hash value i.e. browser path hash value
*/

if (typeof IoTDashboard === 'undefined') {
	IoTDashboard = {};
}

IoTDashboard.menuListClickedIndex = 0; //holds the menu list clicked index
IoTDashboard.timerId; //holds the timerId for setInterval ajax calls

//on Site Details menu button click load the specific site Details view
function onMenuBtnClick(index) {
	$( "#siteMenuId" ).hide();
	$("#headerTitleId").text("Site Details");
	window.location.hash = "#siteDetails?id="+IoTDashboard.menuListClickedIndex;
	$("#siteDetailsMenuBtnId").removeClass("showMenuBtn"); //hide the siteDetails button
}

IoTDashboard.app = (function($, services, siteOverviewView, siteDetailsView, compressorDetailsView){
	
	//Dynamically create the menu items as accordion list and attach click events on site and compressor list items  
	var createSiteMenu = function (data) {
		var list = $("#siteMenuId").append('<ul></ul>').find('ul');
		var siteDetailsBtn = '<button type="button" class="siteDetailsMenuBtnCls" id="siteDetailsMenuBtnId" onclick="onMenuBtnClick()">Site Details</button>';
		var siteItem = '';
		var siteLen = data.siteDetails.length;
		for (var i = 0; i < siteLen; i++) {
			siteItem = siteItem + '<li id="'+data.siteDetails[i].siteId+'" name="'+data.siteDetails[i].siteName+'"><h3>'+data.siteDetails[i].siteName+'</h3>';
			var compItem = '';
			var compLen = data.siteDetails[i].compressors.length;
			for (var j = 0; j < compLen; j++) {
				compItem = compItem + '<li id="'+data.siteDetails[i].compressors[j].compressorId+'"><a>'+data.siteDetails[i].compressors[j].compressorName+'</a></li>';
			}
			siteItem = siteItem + '<ul>'+compItem+'</ul></li>';	
		}
		list.empty();
		list.append(siteItem+siteDetailsBtn);
		
		$("#siteMenuId h3").click(function(event){
		//slide up all the link lists
			$("#siteMenuId ul ul").slideUp();
			//slide down the link list below the h3 clicked - only if its closed
			if(!$(this).next().is(":visible"))
			{
				$(this).next().slideDown();
			}
			IoTDashboard.menuListClickedIndex = $(this).parent().index();
			$("#siteDetailsMenuBtnId").addClass("showMenuBtn"); //show the siteDetails button
		});
		
		//On selected compressor load the compressor details view
		$("#siteMenuId ul ul li").click(function(event){
			if (event.stopPropagation){
				event.stopPropagation();
			}
			else if(window.event){
				window.event.cancelBubble=true;
			}
			var idVal = $(this).attr('id');
			var siteIdVal = $(this).parents('li').attr('id');
			$( "#siteMenuId" ).hide();
			$("#headerTitleId").text("Compressor Details");
			window.location.hash = "#compressorDetails?id="+idVal+"&siteId="+siteIdVal;
			$("#siteDetailsMenuBtnId").removeClass("showMenuBtn"); //hide the siteDetails button
		});
	};
	
	//Call siteDetails web service to get the details of different sites and compressors under each sites and create the accordion menu 
	var callGetSiteDetails = function () {
		var urlPath = services.getSiteDetails();
		$.ajax({
			url: urlPath, 
			method: 'GET',
			timeout: 30000,
			success: function(resultJson){
				$("#wait").hide();
				var result = typeof resultJson != 'object' ? JSON.parse(resultJson) : resultJson;
				createSiteMenu(result);
			},
			error: function(error) {
				$("#wait").hide();
				console.log(error);
			}
		});
	};
	
	//callGetSiteDetails function and change the window.location.hash to load siteOverview.html
	var init = function () {
		callGetSiteDetails();
		$( "#siteMenuId" ).hide();
		$("#headerTitleId").text("Site Overview");
		window.location.hash = "#siteOverview?id=0";
    };
	
	//start of the application: when DOM is ready this event is fired
	$(document).ready(function () {
		init();
		//Attach click event to the Header menu icon
		$( '.menu-btn' ).click(function(){
			$( "#siteMenuId" ).stop().animate({
				width: 'toggle'
			},50);
		});
		//Attach click event to the Header home icon 
		$( '.home-btn' ).click(function(){
			$( "#siteMenuId" ).hide();
			/*Set the header title and change the window.location.hash to #siteOverview 
			which triggers 'hashchange' event to load siteOverview.html*/
			$("#headerTitleId").text("Site Overview");
			window.location.hash = "#siteOverview?id=0";
		});
	});
	
	//Load the specific view as per the window.location.hash value and call the init function of specific view
	$(window).on('hashchange', function(event) {
		var locationHash = window.location.hash;
		var viewName = locationHash.split("?");
		clearInterval(IoTDashboard.timerId);
		switch (viewName[0]) {
			case "#siteOverview" : 
				$( "#mainViewContainerId" ).load( "app/partials/siteOverview.html", function() {
					$("#wait").show();
					$(".home-btn").hide();
					siteOverviewView.init();
					//IoTDashboard.timerId = setInterval(function(){ siteOverviewView.init() }, 60000);
				});
				break;
			
			case "#siteDetails":
				$( "#mainViewContainerId" ).load( "app/partials/siteDetails.html", function() {
					$("#wait").show();
					$(".home-btn").show();
					var index = viewName[1].split("id=")[1];
					siteDetailsView.init(index);
					//IoTDashboard.timerId = setInterval(function(){ siteDetailsView.init(index) }, 5000);
				});
				break;
				
			case "#compressorDetails":
				$( "#mainViewContainerId" ).load( "app/partials/compressorDetails.html", function() { 
					$("#wait").show();
					$(".home-btn").show();
					var siteId = viewName[1].split("siteId=")[1];
					var compId = viewName[1].substr(viewName[1].indexOf("id=")).split('&')[0].split('=')[1];
					compressorDetailsView.init(siteId,compId);
				});
				break;
				
			default: 
				$( "#mainViewContainerId" ).load( "app/partials/siteOverview.html", function() {
					$("#wait").show();
					$(".home-btn").hide();
					siteOverviewView.init();
					//IoTDashboard.timerId = setInterval(function(){ siteOverviewView.init() }, 60000);
				});
				break;
		}
	});
	
	return {
		createSiteMenu: createSiteMenu,
		callGetSiteDetails:callGetSiteDetails,
		init: init
	};
	
}($, IoTDashboard.services, IoTDashboard.siteOverviewView, IoTDashboard.siteDetailsView, IoTDashboard.compressorDetailsView));


    