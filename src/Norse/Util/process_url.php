<?php
session_start();
// THIS FILE CAN BE REPLACED WITH JS LOGIC THAT CALLS IPVIKING_IP DIRECTLY
require_once('../config.php');
require_once('../helpers/ipviking_helper.php');

if(isset($_SESSION['key']) && $_SESSION['key'] != 'changeme'){$strKey = $_SESSION['key'];}else{$_SESSION['key'] = $strKey;}
// USER INPUT FOR THE PAGE
$strIP='8.8.8.8'; if(isset($_GET['url'])){if(filter_var($_GET['url'], FILTER_VALIDATE_URL)){$strURL = $_GET['url'];}}
////|||::.. CONFIGURATION OPTIONS ..::|||\\\\
//key and url are in the config file
$arrApiOptions = array('apikey' => $strKey,
	'method' => 'urlview',
	'url' => $strURL);
////||||||||||||| END CONFIG ||||||||||||\\\\

//make the API call and parse the response 
$IPViking_json = json_decode(fnApiCall($strApiUrl,$arrApiOptions));
$strIP = $IPViking_json->response->ip;
$strRisk = $IPViking_json->response->suggested_classification;
$strReason = $IPViking_json->response->decision_basis;
$strRelation = floatval($IPViking_json->response->similarity_precent);
$mysqli->query($strSQL);
if($strRisk == 'OK'){ $strAnalysis = check_alive($strURL); }
$strSQL="update url set ip='$strIP',risk='$strRisk',reason='$strReason',relation='$strRelation',HTTP='$strHTTP',analysis='$strAnalysis' where url='$strURL' ";
//echo '<br/><br/>'.$strSQL;
?>