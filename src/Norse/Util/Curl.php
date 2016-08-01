<?php namespace Norse\Util;

class Curl {
  public function url2file($strURL, $strPath){
    $fp = fopen($strPath, 'w');
    $ch = curl_init($strURL);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return "Download Complete";
  }
}
