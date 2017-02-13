<!DOCTYPE html>
<?php

$BASEURL ="https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/"; 

set_time_limit(1000);//inctireasing the time limit.

//Header for access
header('Access-Control-Allow-Origin: *');  

//Identify which service is called. 
$service = strtoupper($_REQUEST["service"]); 
$DbPath = "https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/";
$currentMonthString = date("Y-m-d");
$HistoricDataDbName = "iotp_xopdu8_default_".date("Y-m"); //This will be changed every month automatically.
$allDocuments = "/_all_docs";
$AllSiteData = ""; 
$AscName = "asc";
switch($service)
{
    case "SITEDETAILS": //This will return site filtered by ID 
        $siteID  = $_REQUEST["siteid"]; 
    	$AllSiteData = GetSiteData();
        $site = GetSite($siteID,$AllSiteData);
        print_r($site);
        break;
    case "SITEANDCOMPRESSORS": //This will return the live data for compressors whole data. 
     	$siteID  = $_REQUEST["siteid"]; 
        $compressorID  = $_REQUEST["compressorid"];
    	$data = json_encode(GetSitesAndCompressors($siteID,$compressorID));
        print_r($data);
    	break;
    case "SITESANDCOMPRESSORSANDDATA":
        $siteID  = $_REQUEST["siteid"]; 
        $compressorID  = $_REQUEST["compressorid"];
        $data = json_encode(GetCompressorWithLiveData($siteID,$compressorID));
        print_r($data);
    	break;
    	
    case "COMPRESSORTIMESTAMPRANGE":
    	$siteID  = $_REQUEST["siteid"]; 
        $compressorID  = $_REQUEST["compressorid"];
        $minTimeStamp  = $_REQUEST["mintimestamp"]; 
        $maxTimeStamp = $_REQUEST["maxtimestamp"];
        $data = json_encode(GetCompressorsTimeStampRange($siteID,$compressorID,$minTimeStamp,$maxTimeStamp));
        print_r($data);
    	break;
    case "SITESANDCOMPRESSORSALLDATA": 
     	$siteID  = $_REQUEST["siteid"]; 
        $compressorID  = $_REQUEST["compressorid"];
        $data = json_encode(GetCompressorWithAllLiveData($siteID,$compressorID));
        print_r($data);
    	break;
    case "MENUDATA": //This service gets menu
    	$data = json_encode(GetMenu($DbPath.$HistoricDataDbName.$allDocuments,$DbPath.$HistoricDataDbName));
    	print_r($data);
    	break; 
    case "SITEDATA": //This service gets site and live data 5 records for all compressors.
        if(isset($_REQUEST["siteid"]))
        {
    		$siteID  = $_REQUEST["siteid"]; 
		}
		else
		{
			$err["Error"] = "Site ID parameter not specified.";
			die (json_encode($err)); //terminate the service.
		}
		
    	//Get whole data
    	$data = GetMenu($DbPath.$HistoricDataDbName.$allDocuments,$DbPath.$HistoricDataDbName);
    	
    	//Get specific site with live data.
    	$site = json_encode(SearchSite($data,$siteID,$DbPath.$HistoricDataDbName.$allDocuments,$DbPath.$HistoricDataDbName));
    	print_r($site);
    	break;
    case "COMPRESSORDATA":
    	 if(isset($_REQUEST["siteid"]))
        {
    		$siteID  = $_REQUEST["siteid"]; 
		}
		else
		{
			$err["Error"] = "Site ID parameter not specified.";
			die (json_encode($err)); //terminate the service.
		}
		
		 if(isset($_REQUEST["compressorid"]))
        {
    		$compressorID  = $_REQUEST["compressorid"]; 
		}
		else
		{
			$err["Error"] = "Compressor ID parameter not specified.";
			die (json_encode($err)); //terminate the service.
		}
		
		//Get whole data
    	$data = GetMenu($DbPath.$HistoricDataDbName.$allDocuments,$DbPath.$HistoricDataDbName);
    	
    	//Get specific site with live data.
    	$compressor = json_encode(SearchCompressor($data,$siteID,$compressorID,$DbPath.$HistoricDataDbName.$allDocuments,$DbPath.$HistoricDataDbName));
    	print_r($compressor);
    	
    	break;
    	
    	
} 


//This function will search for a compressor 
function SearchCompressor($data,$siteID,$compressorID,$DB,$Path)
{
	//1. First get the total site count.
	$SiteCount = count($data["siteDetails"])	;
	$site = "";
	$compressor="";
	
	
	//2. Loop through each site and search based on site id 
	for($i=0;$i<$SiteCount;$i++)
	{
		if($siteID==$data["siteDetails"][$i]["siteId"])
		{
			
			//Site Found!. Return the site.
			$site = $data["siteDetails"][$i];	
			
			
			//Search for the compressor here 
			for($j=0;$j<count($site["compressors"]);$j++)
			{
				if($compressorID==$site["compressors"][$j]["compressorId"])
				{
					
					//Compressor Found.
					$compressor = $site["compressors"][$j];
					return $compressor;
					break;
				}//End of compressor if
			} //end of compressor for loop
		} //end of if siteID check.
	}

	return $compressor;
}





//This function will search for a site 
function SearchSite($data,$siteID,$DB,$Path)
{ 
	//1. First get the total site count.
	$SiteCount = count($data["siteDetails"])	;
	$site = "";
	//2. Loop through each site and search based on site id 
	for($i=0;$i<$SiteCount;$i++)
	{
		if($siteID== $data["siteDetails"][$i]["siteId"])
		{
			//Site Found!. Return the site.
			$site = $data["siteDetails"][$i];	
			break;
		}
	}
	
	//3.Now we map live data with the site. Fetching all the documents.
	$allDocuments = json_decode(file_get_contents($DB),true);   //get allthe documents available.
	
	//4. Loop through all the documents and map the matching one.
	for($j=0;$j<count($allDocuments[rows]);$j++)
	{
		$document = json_decode(file_get_contents($Path."/". $allDocuments[rows][$j]["key"]),true);
		//Now match the site id and map the compressors 
		$compressorID =  $document["data"]["d"]["compressorId"]; 
		
		//Looping through compressors array.
		for($k=0;$k<count($site["compressors"]);$k++)
		{
			if($compressorID== $site["compressors"][$k]["compressorId"])
			{
				//check timestamp if set. 
				if($site["compressors"][$k]["timeStamp"]=="") 
				{
					$site["compressors"][$k]["timeStamp"] =0;
				}
				
				//compare the timestamp and map the latest value. 
				if($document["data"]["d"]["timeStamp"] >= $site["compressors"][$k]["timeStamp"]) //if file is latest, set the new values.
				{
					 $site["compressors"][$k]["timeStamp"]  = $document["data"]["d"]["timeStamp"];
					 $site["compressors"][$k]["compressorName"]  = $document["data"]["d"]["compressorName"];
					 $site["compressors"][$k]["systemPressure"]  = $document["data"]["d"]["systemPressure"];
					 $site["compressors"][$k]["oilPressure"]  = $document["data"]["d"]["oilPressure"];
					 $site["compressors"][$k]["oilTemperature"]  = $document["data"]["d"]["oilTemperature"];
					 $site["compressors"][$k]["stage1Temperature"]  = $document["data"]["d"]["stage1Temperature"];
					 $site["compressors"][$k]["stage1Vibration"]  = $document["data"]["d"]["stage1Vibration"];
					 $site["compressors"][$k]["stage1Pressure"]  = $document["data"]["d"]["stage1Pressure"];
					 $site["compressors"][$k]["stage2Temperature"]  = $document["data"]["d"]["stage2Temperature"];
					 $site["compressors"][$k]["stage2Vibration"]  = $document["data"]["d"]["stage2Vibration"];
					 $site["compressors"][$k]["stage2Pressure"]  = $document["data"]["d"]["stage2Pressure"];
					 $site["compressors"][$k]["warnings"]  = $document["data"]["d"]["warnings"];
					 $site["compressors"][$k]["readyStatus"]  = $document["data"]["d"]["readyStatus"];
					 $site["compressors"][$k]["alarms"]  = $document["data"]["d"]["alarms"];
					 $site["compressors"][$k]["loaded"]  = $document["data"]["d"]["loaded"];
					 $site["compressors"][$k]["remote"]  = $document["data"]["d"]["remote"];
				}
				
			}
		}
	}
	
	
	return $site;
}

function GetSiteData()
{
	 	$siteBaseURL ="https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/sites/";
        $siteURL ="https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/sites/_all_docs";
        $AllSiteData = file_get_contents($siteURL);
        $AllSiteData = json_decode($AllSiteData,true);
        $siteKey =  $AllSiteData["rows"][0]["key"];
        
        $url = $siteBaseURL.$siteKey;
        $sitedata =  file_get_contents($url); 
        return $sitedata;
	
}

function GetSite($siteID,$AllSiteData)
{
	$AllSiteData = json_decode($AllSiteData,true);
	$siteCount = count($AllSiteData["siteDetails"]); 
	
	for($i=0;$i<$siteCount;$i++)
	{
		$currentSite = $AllSiteData["siteDetails"][$i];
		if($currentSite["siteId"]==$siteID)
		{
			 
			return json_encode($currentSite);
		}
	}
	
}

//This function will find compressor in sites and return the comprssor information.
function GetSitesAndCompressors($siteID,$compressorID)
{
    
    
    //First get the site and compressors document
    $sitesAllDocsURL =  "https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/sites/_all_docs";
    $document = json_decode(file_get_contents($sitesAllDocsURL),true);  
    //die("Document is ".$document);
    
    $siteDocURL = "https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/sites/".$document["rows"][0]["key"];
    $sites = json_decode(file_get_contents($siteDocURL),true);
    $siteCount = count($sites["siteDetails"]);
        
    for($i=0;$i<$siteCount;$i++)
    {
        $site_id = $sites["siteDetails"][$i]["siteId"]; 
        
        if($site_id==$siteID) //IF site ID matches, we find the compressor.
        {
            $compressors = $sites["siteDetails"][$i]["compressors"];
            $compressorCount = count($sites["siteDetails"][$i]["compressors"]); 
            
            for($j=0;$j<$compressorCount;$j++)
            {
                $compressor = $sites["siteDetails"][$i]["compressors"][$j]; 
                
                if($compressor["compressorId"]==$compressorID) //Once compressor mathes, we return the compressor JSON.
                {
                    return $compressor;
                }
            }
            
        }
    }
    
    
    
}

function GetCompressorWithLiveData($siteID,$compressorID)
{
	
   
    //deconded array of compressor
    $compressor = GetSitesAndCompressors($siteID, $compressorID);
    
    //Now query the ASC Database and get all the records. 
    $AscURL = "https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/asc/";
    $AscAllDocsURL = $AscURL."_all_docs"; 
    $compressorFound = false;
    $ascdata = json_decode(file_get_contents($AscAllDocsURL),true); 
    $recCount = count($ascdata["rows"]); 
    $compData = array();
    
  	$latestTime =0;  //Required for time stamp logic
    
    for($i=0;$i<$recCount;$i++)
    {
        $recordURL = $AscURL. $ascdata["rows"][$i]["key"];
        
        $recordData = json_decode(file_get_contents($recordURL),true); 
        $site_id = $recordData["data"]["d"]["SiteId"];
        $compressor_id = $recordData["data"]["d"]["CompressorId"]; 
        
       
       
       if($siteID==$site_id && $compressorID==$compressor_id)
       {
       		//Adding some more data to compressor before sending the information back.
       		$compressor["siteId"] = $siteID; 
       		
       		
       		
       		if($latestTime!=0)
       		{
       			if($recordData["data"]["d"]["TimeStamp"] < $latestTime)
       			{
       				continue;
       			}
       		}
       		
       		$latestTime = $recordData["data"]["d"]["TimeStamp"]; 
       		$compressor["TimeStamp"] = $recordData["data"]["d"]["TimeStamp"]; 
       		$compressor["SystemPressure"] = $recordData["data"]["d"]["SystemPressure"]; 
       		$compressor["OilPressure"] = $recordData["data"]["d"]["OilPressure"]; 
       		$compressor["OilTemperature"] = $recordData["data"]["d"]["OilTemperature"]; 
       		$compressor["Stage1Temperature"] = $recordData["data"]["d"]["Stage1Temperature"]; 
       		$compressor["Stage1Vibration"] = $recordData["data"]["d"]["Stage1Vibration"]; 
       		$compressor["Stage1Pressure"] = $recordData["data"]["d"]["Stage1Pressure"]; 
       		$compressor["Stage2Temperature"] = $recordData["data"]["d"]["Stage2Temperature"]; 
       		$compressor["Stage2Vibration"] = $recordData["data"]["d"]["Stage2Vibration"]; 
       		$compressor["Stage2Pressure"] = $recordData["data"]["d"]["Stage2Pressure"]; 
       		$compressor["ReadyStatus"] = $recordData["data"]["d"]["ReadyStatus"]; 
       		$compressor["Alarms"] = $recordData["data"]["d"]["Alarms"]; 
       		$compressor["Loaded"] = $recordData["data"]["d"]["Loaded"]; 
       		$compressor["Remote"] = $recordData["data"]["d"]["Remote"]; 
       		
       		$compressorFound = true; //Setting the return value
       		
       		//return $compressor;
       }
        
    }
    
    return $compressor;
}


//This function will return all data for that compressors 
function GetCompressorWithAllLiveData($siteID,$compressorID)
{
	
    //deconded array of compressor
    $compressor = GetSitesAndCompressors($siteID, $compressorID);
    $CompressorToSend = ""; 
    $CompressorToSend["Compressors"] ="";
    //Now query the ASC Database and get all the records. 
    $AscURL = "https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/asc/";
    $AscAllDocsURL = $AscURL."_all_docs"; 
    $compressorFound = false;
    $ascdata = json_decode(file_get_contents($AscAllDocsURL),true); 
    $recCount = count($ascdata["rows"]); 
    
  	$latestTime =0;  //Required for time stamp logic
    
    for($i=0;$i<$recCount;$i++)
    {
        $recordURL = $AscURL. $ascdata["rows"][$i]["key"];
        
        $recordData = json_decode(file_get_contents($recordURL),true); 
        $site_id = $recordData["data"]["d"]["SiteId"];
        $compressor_id = $recordData["data"]["d"]["CompressorId"]; 
       
       if($siteID==$site_id && $compressorID==$compressor_id)
       {
       		//Adding some more data to compressor before sending the information back.
       		$CompressorToSend["compressors"]["siteId"] = $siteID; 
       		$CompressorToSend["compressors"]["compressorId"] = $compressorID; 
       		$CompressorToSend["compressors"]["compressorName"] = $recordData["data"]["d"]["CompressorName"]; 
       		$mylength = count($CompressorToSend["compressorProperties"]); 
       		
       		//$CompressorToSend["compressorProperties"][$mylength] = $recordData["data"]["d"]; //For all data elements.
       		$CompressorToSend["compressorProperties"][$mylength]["timestamp"] = $recordData["data"]["d"]["TimeStamp"];
       		$CompressorToSend["compressorProperties"][$mylength]["systemPressure"] = $recordData["data"]["d"]["SystemPressure"];
       		$CompressorToSend["compressorProperties"][$mylength]["stage1Temperature"] = $recordData["data"]["d"]["Stage1Temperature"];
       		$CompressorToSend["compressorProperties"][$mylength]["stage2Temperature"] = $recordData["data"]["d"]["Stage2Temperature"];
       		
       		$CompressorFound = true; //Setting the return value
       }
    }
    
    return $CompressorToSend;
	
}

//This function returns compressors with time stamp range as specified. 
function GetCompressorsTimeStampRange($siteID,$compressorID,$minTimeStamp,$maxTimeStamp)
{
	//deconded array of compressor
    $compressor = GetSitesAndCompressors($siteID, $compressorID);
    $CompressorToSend = ""; 
    $CompressorToSend["Compressors"] ="";
    //Now query the ASC Database and get all the records. 
    $AscURL = "https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com/asc/";
    $AscAllDocsURL = $AscURL."_all_docs"; 
    $compressorFound = false;
    $ascdata = json_decode(file_get_contents($AscAllDocsURL),true); 
    $recCount = count($ascdata["rows"]); 
    
  	$latestTime =0;  //Required for time stamp logic
    
    for($i=0;$i<$recCount;$i++)
    {
        $recordURL = $AscURL. $ascdata["rows"][$i]["key"];
        
        $recordData = json_decode(file_get_contents($recordURL),true); 
        $site_id = $recordData["data"]["d"]["SiteId"];
        $compressor_id = $recordData["data"]["d"]["CompressorId"]; 
       
       if($siteID==$site_id && $compressorID==$compressor_id)
       {
       		//Timestamp check before we proceed. 
       		if($recordData["data"]["d"]["TimeStamp"] < $minTimeStamp ||  $recordData["data"]["d"]["TimeStamp"] > $maxTimeStamp ) // not fitting in the range
       		{
       			continue; //Cannot add this record.
       		}
       		
       		//Adding some more data to compressor before sending the information back.
       		$CompressorToSend["compressors"]["siteId"] = $siteID; 
       		$CompressorToSend["compressors"]["compressorId"] = $compressorID; 
       		$CompressorToSend["compressors"]["compressorName"] = $recordData["data"]["d"]["CompressorName"]; 
       		$mylength = count($CompressorToSend["compressorProperties"]); 
       		
       		//$CompressorToSend["compressorProperties"][$mylength] = $recordData["data"]["d"]; //For all data elements.
       		$CompressorToSend["compressorProperties"][$mylength]["timestamp"] = $recordData["data"]["d"]["TimeStamp"];
       		$CompressorToSend["compressorProperties"][$mylength]["systemPressure"] = $recordData["data"]["d"]["SystemPressure"];
       		$CompressorToSend["compressorProperties"][$mylength]["stage1Temperature"] = $recordData["data"]["d"]["Stage1Temperature"];
       		$CompressorToSend["compressorProperties"][$mylength]["stage2Temperature"] = $recordData["data"]["d"]["Stage2Temperature"];
       		
       		$CompressorFound = true; //Setting the return value
       }
    }
    
   return $CompressorToSend;
}

	function GetMenu($DB,$Path)
	{
		$RtnData=""; //return value
		$siteCount =0; //total number of sites
		$compressorAdded = false; //Set the compressor Found property to false. 
		$siteExists = false; //if site already exists in returndata.	
		$currentDoument ="";
		//1. Fetch all the documents. 
		$allDocuments = json_decode(file_get_contents($DB),true);   //get allthe documents available.
		
		//2. Looping through the documents 
		for($i=0;$i<$allDocuments["total_rows"];$i++)
		{
			$docPath = $Path."/". $allDocuments["rows"][$i]["key"]; 
			$currentDoument = json_decode(file_get_contents($docPath),true); // Retrieving the record from database. 
			
			//3. Set the site count value.
			//first check if any data is added.
			if($RtnData=="")
			{
				$siteCount =0; //There is no site added.
				
			}
			else
			{
				$siteCount = count($RtnData["siteDetails"]); //there are sites added.
			}
			
			
			//4. If no sites are added, directly add one. 
			if($siteCount==0) //No site is available. Add the first site here
			{
					//5. Add site information here
					$RtnData["siteDetails"][$siteCount]["siteId"] = $currentDoument["data"]["d"]["siteId"];
					$RtnData["siteDetails"][$siteCount]["siteName"] = $currentDoument["data"]["d"]["siteName"];
					$RtnData["siteDetails"][$siteCount]["latitude"] = $currentDoument["data"]["d"]["latitude"];
					$RtnData["siteDetails"][$siteCount]["longitude"] = $currentDoument["data"]["d"]["longitude"];
					
					//Add the compressor information here. This should be the first compressor 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["compressorId"] =  $currentDoument["data"]["d"]["compressorId"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["compressorName"] =  $currentDoument["data"]["d"]["compressorName"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["readyStatus"] =  $currentDoument["data"]["d"]["readyStatus"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["alarms"] =  $currentDoument["data"]["d"]["alarms"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["loaded"] =  $currentDoument["data"]["d"]["loaded"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["remote"] =  $currentDoument["data"]["d"]["remote"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["warnings"] =  $currentDoument["data"]["d"]["warnings"]; 
					
					//Adding extra data 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["timeStamp"]  = $currentDoument["data"]["d"]["timeStamp"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["systemPressure"]  = $currentDoument["data"]["d"]["systemPressure"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["oilPressure"]  = $currentDoument["data"]["d"]["oilPressure"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["oilTemperature"]  = $currentDoument["data"]["d"]["oilTemperature"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage1Temperature"]  = $currentDoument["data"]["d"]["stage1Temperature"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage1Vibration"]  = $currentDoument["data"]["d"]["stage1Vibration"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage1Pressure"]  = $currentDoument["data"]["d"]["stage1Pressure"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage2Temperature"]  = $currentDoument["data"]["d"]["stage2Temperature"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage2Vibration"]  = $currentDoument["data"]["d"]["stage2Vibration"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage2Pressure"]  = $currentDoument["data"]["d"]["stage2Pressure"];
					
					$siteExists = true;
					$compressorAdded = true;
			}  //end of if condition for sitecount check. End of 4.
			
			else //5.if Site Count is not zero.
			{
				//6. Here atleast one site is added. We loop through added sites and verify if current document site is there. 
				$siteExists = false;
				$compExists = false;
				
				
				
				//7.Loop around sites and check if the current document site is available
				for($j=0;$j<$siteCount;$j++) //If there are records, i.e. RtnData already has data inserted.
				{
					//8. First check if site is added. 
					
					
					if($RtnData["siteDetails"][$j]["siteId"] == $currentDoument["data"]["d"]["siteId"])
					{
						$siteExists = true;
						
						//Now check for compressor. 
						$compCount  = count($RtnData["siteDetails"][$j]["compressors"]);
						
						
						//9. Check if there are any compressors. 
						if($compCount==0) //if there are no compressors, just add one.
						{
							$RtnData["siteDetails"][$j]["compressors"][0]["compressorId"] =  $currentDoument["data"]["d"]["compressorId"]; 
							$RtnData["siteDetails"][$j]["compressors"][0]["compressorName"] =  $currentDoument["data"]["d"]["compressorName"]; 
							$RtnData["siteDetails"][$j]["compressors"][0]["readyStatus"] =  $currentDoument["data"]["d"]["readyStatus"]; 
							$RtnData["siteDetails"][$j]["compressors"][0]["alarms"] =  $currentDoument["data"]["d"]["alarms"]; 
							$RtnData["siteDetails"][$j]["compressors"][0]["loaded"] =  $currentDoument["data"]["d"]["loaded"]; 
							$RtnData["siteDetails"][$j]["compressors"][0]["remote"] =  $currentDoument["data"]["d"]["remote"]; 
							$RtnData["siteDetails"][$j]["compressors"][0]["warnings"] =  $currentDoument["data"]["d"]["warnings"]; 
							
							
							//Adding extra data 
							$RtnData["siteDetails"][$j]["compressors"][0]["timeStamp"]  = $currentDoument["data"]["d"]["timeStamp"];
							$RtnData["siteDetails"][$j]["compressors"][0]["systemPressure"]  = $currentDoument["data"]["d"]["systemPressure"];
							$RtnData["siteDetails"][$j]["compressors"][0]["oilPressure"]  = $currentDoument["data"]["d"]["oilPressure"];
							$RtnData["siteDetails"][$j]["compressors"][0]["oilTemperature"]  = $currentDoument["data"]["d"]["oilTemperature"];
							$RtnData["siteDetails"][$j]["compressors"][0]["stage1Temperature"]  = $currentDoument["data"]["d"]["stage1Temperature"];
							$RtnData["siteDetails"][$j]["compressors"][0]["stage1Vibration"]  = $currentDoument["data"]["d"]["stage1Vibration"];
							$RtnData["siteDetails"][$j]["compressors"][0]["stage1Pressure"]  = $currentDoument["data"]["d"]["stage1Pressure"];
							$RtnData["siteDetails"][$j]["compressors"][0]["stage2Temperature"]  = $currentDoument["data"]["d"]["stage2Temperature"];
							$RtnData["siteDetails"][$j]["compressors"][0]["stage2Vibration"]  = $currentDoument["data"]["d"]["stage2Vibration"];
							$RtnData["siteDetails"][$j]["compressors"][0]["stage2Pressure"]  = $currentDoument["data"]["d"]["stage2Pressure"];
							
							$compressorAdded = true;
							$compExists = true;
						}//End of compCount =0 . End of 9 
						else //10. if already compressors are available.
						{
							//11. First check if compressor is already available. 
							//12.looping through added ccompressors and verifying the current one.
							for($k=0;$k<$compCount;$k++)
							{
								if($RtnData["siteDetails"][$j]["compressors"][$k]["compressorId"]==$currentDoument["data"]["d"]["compressorId"])
								{
									
									//Verify the time stamp and update the parameters to latest. 
									$ts = $RtnData["siteDetails"][$j]["compressors"][$k]["timeStamp"];
									if($ts=="")
									{
										$ts =0; 
									}
									
									if($currentDoument["data"]["d"]["timeStamp"] > $k) //if document is most recent, update remaining records.
									{
										$RtnData["siteDetails"][$j]["compressors"][$k]["timeStamp"]=$currentDoument["data"]["d"]["timeStamp"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["systemPressure"]=$currentDoument["data"]["d"]["systemPressure"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["oilPressure"]=$currentDoument["data"]["d"]["oilPressure"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["oilTemperature"]=$currentDoument["data"]["d"]["oilTemperature"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["stage1Temperature"]=$currentDoument["data"]["d"]["stage1Temperature"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["stage1Vibration"]=$currentDoument["data"]["d"]["stage1Vibration"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["stage1Pressure"]=$currentDoument["data"]["d"]["stage1Pressure"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["stage2Temperature"]=$currentDoument["data"]["d"]["stage2Temperature"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["stage2Vibration"]=$currentDoument["data"]["d"]["stage2Vibration"];
										$RtnData["siteDetails"][$j]["compressors"][$k]["stage2Pressure"]=$currentDoument["data"]["d"]["stage2Pressure"];
									}
									
									$compressorAdded = true;
									$compExists = true;
									break;
								}
							}// End of 12. for loop check for compressors. 
							
							
							//13.Add the copressor if its not there.
							if($compExists==false) // if compressor is not available, just add it.
							{
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["compressorId"] =  $currentDoument["data"]["d"]["compressorId"]; 
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["compressorName"] =  $currentDoument["data"]["d"]["compressorName"]; 
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["readyStatus"] =  $currentDoument["data"]["d"]["readyStatus"]; 
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["alarms"] =  $currentDoument["data"]["d"]["alarms"]; 
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["loaded"] =  $currentDoument["data"]["d"]["loaded"]; 
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["remote"] =  $currentDoument["data"]["d"]["remote"]; 
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["warnings"] =  $currentDoument["data"]["d"]["warnings"]; 
								
								
								//Adding extra data 
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["timeStamp"]  = $currentDoument["data"]["d"]["timeStamp"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["systemPressure"]  = $currentDoument["data"]["d"]["systemPressure"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["oilPressure"]  = $currentDoument["data"]["d"]["oilPressure"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["oilTemperature"]  = $currentDoument["data"]["d"]["oilTemperature"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["stage1Temperature"]  = $currentDoument["data"]["d"]["stage1Temperature"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["stage1Vibration"]  = $currentDoument["data"]["d"]["stage1Vibration"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["stage1Pressure"]  = $currentDoument["data"]["d"]["stage1Pressure"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["stage2Temperature"]  = $currentDoument["data"]["d"]["stage2Temperature"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["stage2Vibration"]  = $currentDoument["data"]["d"]["stage2Vibration"];
								$RtnData["siteDetails"][$j]["compressors"][$compCount]["stage2Pressure"]  = $currentDoument["data"]["d"]["stage2Pressure"];
									
								
								$compressorAdded = true;
								$compExists = true;
							} //End of 13. if compressor is not there in existing compressors list.
							
							
						} //End oft 10. End of else i.e. compressors are available for existing site.
					}//End of if site id comparison
					
					 //11. If the site does not exists in existing RtnSites, add a new site.
				if($siteExists==false) //If the site does not exists in the added sites.
				{
					
					//Add site information here
					$RtnData["siteDetails"][$siteCount]["siteId"] = $currentDoument["data"]["d"]["siteId"];
					$RtnData["siteDetails"][$siteCount]["siteName"] = $currentDoument["data"]["d"]["siteName"];
					$RtnData["siteDetails"][$siteCount]["latitude"] = $currentDoument["data"]["d"]["latitude"];
					$RtnData["siteDetails"][$siteCount]["longitude"] = $currentDoument["data"]["d"]["longitude"];
					
					//Add the compressor information here. This should be the first compressor 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["compressorId"] =  $currentDoument["data"]["d"]["compressorId"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["compressorName"] =  $currentDoument["data"]["d"]["compressorName"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["readyStatus"] =  $currentDoument["data"]["d"]["readyStatus"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["alarms"] =  $currentDoument["data"]["d"]["alarms"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["loaded"] =  $currentDoument["data"]["d"]["loaded"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["remote"] =  $currentDoument["data"]["d"]["remote"]; 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["warnings"] =  $currentDoument["data"]["d"]["warnings"]; 
					
					
					
					//Adding extra data 
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["timeStamp"]  = $currentDoument["data"]["d"]["timeStamp"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["systemPressure"]  = $currentDoument["data"]["d"]["systemPressure"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["oilPressure"]  = $currentDoument["data"]["d"]["oilPressure"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["oilTemperature"]  = $currentDoument["data"]["d"]["oilTemperature"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage1Temperature"]  = $currentDoument["data"]["d"]["stage1Temperature"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage1Vibration"]  = $currentDoument["data"]["d"]["stage1Vibration"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage1Pressure"]  = $currentDoument["data"]["d"]["stage1Pressure"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage2Temperature"]  = $currentDoument["data"]["d"]["stage2Temperature"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage2Vibration"]  = $currentDoument["data"]["d"]["stage2Vibration"];
					$RtnData["siteDetails"][$siteCount]["compressors"][0]["stage2Pressure"]  = $currentDoument["data"]["d"]["stage2Pressure"];	
	
					
					
					
					$siteExists = true;
					$compressorAdded = true;
					
				}
				 } //end of for loop for RtnData added sites.
			 } //End of else if $siteCount is greater than zero. End of 5. 
		} //End of for allDocuments loop. End of 2.
		//die("<br />***************The End******************************"); //Remove this line after testing
		return $RtnData;
	}


?>