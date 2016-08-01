<?php namespace Norse\Util;


class Countries {
  private $arrCountryCodes;

  function __construct() {
    $this->arrCountryCodes = require_once('ccodes.php');
  }

  function cc2Country($strCC){
    if (isset($arrCountryCodes[$strCC])) {
      return $arrCountryCodes[$strCC];
    } else {
      return $strCC;
    }
  }
}
