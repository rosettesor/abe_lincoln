<?php namespace Norse\Util;

use Valitron\Validator;

class ConsolidateNorseAdded {
  const
    INSERT_QUERY = 'INSERT IGNORE INTO cm_%s.norse_added (ip_start, ip_end) VALUES (%d, %d) ',

    UPDATE_QUERY = 'UPDATE cm_%s.norse_added SET ip_start=%s, ip_end=%s WHERE ip_start=%s AND ip_end=%s ',

    UPDATE_START_QUERY = 'UPDATE cm_%s.norse_added SET ip_start=%s WHERE ip_start=%s AND ip_end=%s ',

    UPDATE_END_QUERY = 'UPDATE cm_%s.norse_added SET ip_end=%s WHERE ip_start=%s AND ip_end=%s ',

    DELETE_QUERY = 'DELETE FROM cm_%s.norse_added WHERE ip_start=%s AND ip_end=%s '
  ;

  private $params;
  private $cmdb;

  public function __construct() {
    $this->cmdb = \Norse\Util\DB::getInstance('CM');
  }

  public function addNew($app, $accountId, $ipStart, $ipEnd) {
    $this->app = $app;
    $this->validate(array('ips' => $ipStart, 'ipe' => $ipEnd));
    if (!$this->consolidate($accountId, ip2long($this->params['ips']), ip2long($this->params['ipe']))) {
      $this->cmdb->sql2arr(sprintf(self::INSERT_QUERY, $accountId, ip2long($this->params['ips']), ip2long($this->params['ipe'])));
    }
    $this->consolidateExisting($accountId);
  }

  private function validate($params) {
    $v = new Validator($params);
    $v->rule('required', 'ips');
    $v->rule('ip', 'ips');
    $v->rule('required', 'ipe');
    $v->rule('ip', 'ipe');
    if (!$v->validate()) {
      $this->app->error(400);
    }
    $this->params = $params;
  }

  // submitted range is not inside, outside, overlapping, or consecutive to ANY existing range
  private function consolidate($accountId, $ips, $ipe) {
    $sr = intval($ips)-1;
    $er = intval($ipe)+1;
    $na_ranges = $this->cmdb->sql2arr('SELECT ip_start, ip_end FROM cm_' . $accountId . '.norse_added
      WHERE (ip_end>=' . $sr . ' AND NOT ip_start>' . $er . ')
      OR (ip_start<=' . $er . ' AND NOT ip_end<' . $sr . ') ORDER BY ip_start');    
    foreach ($na_ranges as $range) {
      // if submitted range is equal to existing range, don't do anything
      if ( $ips == $range['ip_start'] && $ipe == $range['ip_end'] ) {
        return true;
      }

      // if submitted range is within existing range, delete (if already exists)
      else if ( $ips >= $range['ip_start'] && $ipe <= $range['ip_end'] ) {
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $ips, $ipe));
        return true;
      }

      // if submitted range is outside of (includes) existing range, update existing range with submitted range
      else if ( $ips <= $range['ip_start'] && $ipe >= $range['ip_end'] ) {
        $this->cmdb->sql2arr(sprintf(self::UPDATE_QUERY, $accountId, $ips, $ipe, $range['ip_start'], $range['ip_end']));
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $range['ip_start'], $range['ip_end']));
        return true;
      }

      // if submitted range concatenates on start of existing network range OR if submitted range overlaps start of existing network range, update start of existing range
      else if ( ($ips < $range['ip_start'] && $ipe <= $range['ip_end'] && $ipe >= $range['ip_start']) || (($ipe+1) == $range['ip_start']) ) {
        $this->cmdb->sql2arr(sprintf(self::UPDATE_START_QUERY, $accountId, $ips, $range['ip_start'], $range['ip_end']));
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $ips, $ipe));
        return true;
      }

      // if submitted range concatenates on end of existing network range OR if submitted range overlaps end of existing network range, update end of existing range
      else if ( ($ips >= $range['ip_start'] && $ipe > $range['ip_end'] && $ips <= $range['ip_end']) || (($ips-1) == $range['ip_end']) ) {
        $this->cmdb->sql2arr(sprintf(self::UPDATE_END_QUERY, $accountId, $ipe, $range['ip_start'], $range['ip_end']));
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $ips, $ipe));
        return true;
      }
    }
    return false;
  }

  private function consolidateExisting($accountId) {
    $na_ranges = $this->cmdb->sql2arr('SELECT ip_start, ip_end FROM cm_' . $accountId . '.norse_added ORDER BY ip_start');
    foreach ($na_ranges as $range) {
      $this->consolidate($accountId, $range['ip_start'], $range['ip_end']);
    }
  }
}

