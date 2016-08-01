<?php
session_start();
// THIS FILE CAN BE REPLACED WITH JS LOGIC THAT CALLS IPVIKING_IP DIRECTLY
require_once('../config.php');
require_once('../helpers/ipviking_helper.php');

if(isset($_SESSION['key']) && $_SESSION['key'] != 'changeme'){$strKey = $_SESSION['key'];}else{$_SESSION['key'] = $strKey;}
// USER INPUT FOR THE PAGE
$strIP='8.8.8.8'; if(isset($_GET['ip'])){if(filter_var($_GET['ip'], FILTER_VALIDATE_IP)){$strIP = $_GET['ip'];}}
$arrIP=explode('.', $strIP);
////|||::.. CONFIGURATION OPTIONS ..::|||\\\\
//key and url are in the config file
$arrApiOptions = array('apikey' => $strKey,
	'method' => 'ipview',
	'ip' => $strIP);
////||||||||||||| END CONFIG ||||||||||||\\\\

//make the API call and parse the response 
$objIP = json_decode(fnApiCall($strApiUrl,$arrApiOptions));
//print_r($IPViking_header);
$arrDetections = array();
	if(isset($objIP->response->entries )){
		foreach($objIP->response->entries as $objDetection){
			array_push($arrDetections,$objDetection->category_name);
	}}

$strSQL='update ip set score="'.$objIP->response->risk_factor.'",cc="'.$objIP->response->geoloc->country_code.'",org="'.$objIP->response->geoloc->organization.'",cats="'.implode($arrDetections,',').'" where  ip=inet_aton("'.$strIP.'")';
$mysqli->query($strSQL);

//echo '<br/><br/>'.$strSQL;
?>