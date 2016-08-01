<?php
function valid_ip_cidr($cidr, $must_cidr = true)
{
    if (!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\/[0-9]{1,2})?$/", $cidr))
    {
        $return = false;
    } else
    {
        $return = true;
    }
    if ($return == true)
    {
        $parts = explode("/", $cidr);
        $ip = $parts[0];
        $netmask = $parts[1];
        $octets = explode(".", $ip);
        foreach ($octets as $octet)
        {
            if ($octet > 255)
            {
                $return = false;
            }
        }
        if ((($netmask != "") && ($netmask > 32) && !$must_cidr) || (($netmask == ""||$netmask > 32) && $must_cidr))
        {
            $return = false;
        }
    }
    return $return;
}

function cidrToRange($cidr) {
  $ips = array();
  $cidr = explode('/', $cidr);
  $intStart = (ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1])));
  $intEnd = (ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1;
  for($ip=$intStart; $ip<=$intEnd; $ip++){
  	array_push($ips, long2ip($ip));
  }
  return $ips;
}