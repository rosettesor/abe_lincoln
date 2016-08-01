<?php

/*
//something to test these with
include('sql_helper.php');
$strAPI = 'http://localhost/darkclient/helpers/process_ip.php';
$mysqli = new mysqli("127.0.0.1", "root", "", "lab");
$arrResult = sql2arr('Select ip from ip limit 10000',$mysqli);
	$arrIPs = array();
	foreach($arrResult as $row){ array_push($arrIPs, $row['ip']); }
	fnProcessIPs($arrIPs,$mysqli,$strAPI);
*/
//----====|| IP FUNCTIONS ||====----\\
function fnProcessIPs($arrURLs,$mysqli,$strAPI){
$intBatchSize=10;
for($i=0;$i<count($arrURLs);$i=$i+$intBatchSize){
		$intStart = time();
		$arrBatch=array();
		for($x=$i;$x<$i+$intBatchSize;$x++){if($x<count($arrURLs)){
			$strURL = $arrURLs[$x];
			array_push($arrBatch,$strURL);
		}}
		multi_ipview($arrBatch,$strAPI);
	}
}

function multi_ipview($nodes,$strAPI){ 
        $mh = curl_multi_init(); 
        $curl_array = array(); 
        foreach($nodes as $i => $url) 
        { 
        	$strURI=$strAPI.'?ip='.$url;
        	//echo $strURI;
            $curl_array[$i] = curl_init($strURI); 
            curl_setopt($curl_array[$i], CURLOPT_HEADER, 0);
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_array[$i], CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYPEER, 0);
            //curl_setopt($curl_array[$i], CURLOPT_FORBID_REUSE, true);
            curl_setopt($curl_array[$i], CURLOPT_MAXCONNECTS, 16);
            //curl_setopt($curl_array[$i], CURLOPT_POST, 1);
            //curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, array('ip'=>$url));
            curl_multi_add_handle($mh, $curl_array[$i]); 
        } 
        $running = NULL; 
        do { 
            usleep(10000); 
            curl_multi_exec($mh,$running); 
        } while($running > 0); 
        
        $res = array(); 
        foreach($nodes as $i => $url) 
        { 
            $res[$url] = curl_multi_getcontent($curl_array[$i]); 
        } 
        
        foreach($nodes as $i => $url){ 
            curl_multi_remove_handle($mh, $curl_array[$i]); 
        } 
        curl_multi_close($mh);   
        //print_r($res);     
        return $res; 
} 