<?php

require_once("../php.ipviking.class/IPVIKING_API.php");


$arrCategoryCodes=array(
		 '1'=>'Explicit Content'
		,'2'=>'Bogon Unadv'
		,'3'=>'Bogon Unass'
		,'4'=>'Proxy'
		,'5'=>'Botnet'
		,'6'=>'Financial'
		,'7'=>'CyberTerrorism'
		,'8'=>'Identity'
		,'9'=>'BruteForce'
		,'10'=>'Cyber Stalking'
		,'11'=>'Arms'
		,'12'=>'Drugs'
		,'13'=>'Espionage'
		,'14'=>'Music Piracy'
		,'15'=>'Games piracy'
		,'16'=>'Movie piracy'
		,'17'=>'Publishing piracy'
		,'18'=>'StockMarket'
		,'19'=>'Hacked'
		,'20'=>'Information piracy'
		,'21'=>'High risk'
		,'22'=>'HTTP'
		,'31'=>'Malicious Site'
		,'41'=>'Friendly Scanning'
		,'51'=>'Web Attacks'
		,'61'=>'DATA Harvesting'
		,'71'=>'Global Whitelist'
		,'81'=>'Malware'
		,'82'=>'Passive DNS'
	);

function fnApiCall($strApiUrl, $arrOptions){
	$IPViking = new IPvikingRequest($strApiUrl, "POST",$arrOptions);
	$IPViking->setAcceptType('application/json');
	$IPViking->execute();
	$IPViking_header = $IPViking->getResponseInfo();
	$IPViking_body = $IPViking->getResponseBody();
	$strHTTP = $IPViking_header['http_code'];
	$objResponse = json_decode($IPViking_body);
	return json_encode($objResponse->response,JSON_NUMERIC_CHECK);
}
function cat2icon($strCat){
	$arrMap = array('Botnet'=>'norse-icon-bots','Proxy'=>'norse-icon-anonprox','Passive DNS'=>'norse-icon-passivedns','Bogon Unadv'=>'norse-icon-bogons','Malware'=>'norse-icon-malware','HTTP'=>'norse-icon-http','Explicit Content'=>'norse-icon-http','none'=>'norse-icon-nosignature');
	return $arrMap[$strCat];
}
function category2Cat($strCat){
	$arrCategoryCodes=array(
		 '1'=>'Explicit Content'
		,'2'=>'Bogon Unadv'
		,'3'=>'Bogon Unass'
		,'4'=>'Proxy'
		,'5'=>'Botnet'
		,'6'=>'Financial'
		,'7'=>'CyberTerrorism'
		,'8'=>'Identity'
		,'9'=>'BruteForce'
		,'10'=>'Cyber Stalking'
		,'11'=>'Arms'
		,'12'=>'Drugs'
		,'13'=>'Espionage'
		,'14'=>'Music Piracy'
		,'15'=>'Games piracy'
		,'16'=>'Movie piracy'
		,'17'=>'Publishing piracy'
		,'18'=>'StockMarket'
		,'19'=>'Hacked'
		,'20'=>'Information piracy'
		,'21'=>'High risk'
		,'22'=>'HTTP'
		,'31'=>'Malicious Site'
		,'41'=>'Friendly Scanning'
		,'51'=>'Web Attacks'
		,'61'=>'DATA Harvesting'
		,'71'=>'Global Whitelist'
		,'81'=>'Malware'
		,'82'=>'Passive DNS'
	);
	return array_search($strCat,$arrCategoryCodes);
}

function cat2Category($intCat){
	$arrCategoryCodes=array(
		 '1'=>'Explicit Content'
		,'2'=>'Bogon Unadv'
		,'3'=>'Bogon Unass'
		,'4'=>'Proxy'
		,'5'=>'Botnet'
		,'6'=>'Financial'
		,'7'=>'CyberTerrorism'
		,'8'=>'Identity'
		,'9'=>'BruteForce'
		,'10'=>'Cyber Stalking'
		,'11'=>'Arms'
		,'12'=>'Drugs'
		,'13'=>'Espionage'
		,'14'=>'Music Piracy'
		,'15'=>'Games piracy'
		,'16'=>'Movie piracy'
		,'17'=>'Publishing piracy'
		,'18'=>'StockMarket'
		,'19'=>'Hacked'
		,'20'=>'Information piracy'
		,'21'=>'High risk'
		,'22'=>'HTTP'
		,'31'=>'Malicious Site'
		,'41'=>'Friendly Scanning'
		,'51'=>'Web Attacks'
		,'61'=>'DATA Harvesting'
		,'71'=>'Global Whitelist'
		,'81'=>'Malware'
		,'82'=>'Passive DNS'
	);
	$intCat=intval($intCat);
	return '<i class="'.cat2icon($arrCategoryCodes[$intCat]).'"></i> '.$arrCategoryCodes[$intCat];
}