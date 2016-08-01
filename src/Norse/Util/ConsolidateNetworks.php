<?php namespace Norse\Util;

use Valitron\Validator;

class ConsolidateNetworks {
  const
    INSERT_QUERY = 'INSERT IGNORE INTO cm_%s.networks (ip_start, ip_end, description) VALUES (%d, %d, "%s") ',

    UPDATE_QUERY = 'UPDATE cm_%s.networks SET ip_start=%s, ip_end=%s WHERE ip_start=%s AND ip_end=%s ',

    UPDATE_START_QUERY = 'UPDATE cm_%s.networks SET ip_start=%s WHERE ip_start=%s AND ip_end=%s ',

    UPDATE_END_QUERY = 'UPDATE cm_%s.networks SET ip_end=%s WHERE ip_start=%s AND ip_end=%s ',

    DELETE_QUERY = 'DELETE FROM cm_%s.networks WHERE ip_start=%s AND ip_end=%s ',

    UPDATE_DESC_QUERY = 'UPDATE cm_%s.networks SET description="%s" WHERE ip_start=%s AND ip_end=%s ',

    GET_DESC_QUERY = 'SELECT description FROM cm_%s.networks WHERE ip_start=%s AND ip_end=%s '
  ;

  private $params;
  private $cmdb;

  public function __construct() {
    $this->cmdb = \Norse\Util\DB::getInstance('CM');
  }

  public function addNew($app, $accountId, $ipStart, $ipEnd, $description = '') {
    $this->app = $app;
    $this->validate(array('ips' => $ipStart, 'ipe' => $ipEnd, 'desc' => $description));
    if (!$this->consolidate($accountId, ip2long($this->params['ips']), ip2long($this->params['ipe']), isset($this->params['desc']) ? $this->params['desc'] : '')) {
      $this->cmdb->sql2arr(sprintf(self::INSERT_QUERY, $accountId, ip2long($this->params['ips']), ip2long($this->params['ipe']), $this->params['desc']));
    }
    $this->combineCClasses($accountId, 50);
  }

  private function validate($params) {
    $v = new Validator($params);
    $v->rule('required', 'ips');
    $v->rule('ip', 'ips');
    $v->rule('required', 'ipe');
    $v->rule('ip', 'ipe');
    if (isset($params['desc'])) {
      $v->rule('text', 'desc');
    }

    // can not add ranges larger than c class (65535 ip's)
    if (!$v->validate() || (ip2long($params['ipe']) - ip2long($params['ips']) > 65535)) {
      $this->app->error(400);
    }
    $this->params = $params;
  }

  // submitted range is not inside, outside, overlapping, or consecutive to ANY existing range
  private function consolidate($accountId, $ips, $ipe, $desc='') {
    $sr = intval($ips)-1;
    $er = intval($ipe)+1;
    $networkranges = $this->cmdb->sql2arr('SELECT ip_start, ip_end, description FROM cm_' . $accountId . '.networks
      WHERE (ip_end>=' . $sr . ' AND NOT ip_start>' . $er . ')
      OR (ip_start<=' . $er . ' AND NOT ip_end<' . $sr . ') ORDER BY ip_start');
    foreach ($networkranges as $range) {
      // if submitted range is equal to existing range, don't do anything
      if ( $ips == $range['ip_start'] && $ipe == $range['ip_end'] ) {
        return $this->updateDesc($range['description'], $desc, $accountId, $ips, $ipe);
      }

      // if submitted range is within existing range, delete (if already exists)
      else if ( $ips >= $range['ip_start'] && $ipe <= $range['ip_end'] ) {
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $ips, $ipe));
        return $this->updateDesc($range['description'], $desc, $accountId, $range['ip_start'], $range['ip_end']);
      }

      // if submitted range is outside of (includes) existing range, update existing range with submitted range
      else if ( $ips <= $range['ip_start'] && $ipe >= $range['ip_end'] ) {
        $this->cmdb->sql2arr(sprintf(self::UPDATE_QUERY, $accountId, $ips, $ipe, $range['ip_start'], $range['ip_end']));
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $range['ip_start'], $range['ip_end']));
        return $this->updateDesc($range['description'], $desc, $accountId, $ips, $ipe);
      }

      // if submitted range concatenates on start of existing network range OR if submitted range overlaps start of existing network range, update start of existing range
      else if ( ($ips < $range['ip_start'] && $ipe <= $range['ip_end'] && $ipe >= $range['ip_start']) || (($ipe+1) == $range['ip_start']) ) {
        $this->cmdb->sql2arr(sprintf(self::UPDATE_START_QUERY, $accountId, $ips, $range['ip_start'], $range['ip_end']));
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $ips, $ipe));
        return $this->updateDesc($range['description'], $desc, $accountId, $ips, $range['ip_end']);
      }

      // if submitted range concatenates on end of existing network range OR if submitted range overlaps end of existing network range, update end of existing range
      else if ( ($ips >= $range['ip_start'] && $ipe > $range['ip_end'] && $ips <= $range['ip_end']) || (($ips-1) == $range['ip_end']) ) {
        $this->cmdb->sql2arr(sprintf(self::UPDATE_END_QUERY, $accountId, $ipe, $range['ip_start'], $range['ip_end']));
        $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $ips, $ipe));
        return $this->updateDesc($range['description'], $desc, $accountId, $range['ip_start'], $ipe);
      }
    }
    return false;
  }

  private function updateDesc($currentDesc, $newDesc, $aId, $s, $e) {
    if ($newDesc && $currentDesc !== $newDesc) {
      if ($currentDesc) {
        $description = explode(", ", $currentDesc);
        if (!in_array($newDesc, $description)) {
          $this->cmdb->sql2arr(sprintf(self::UPDATE_DESC_QUERY, $aId, $currentDesc . ', ' . $newDesc, $s, $e));
        }
      } else {
        $this->cmdb->sql2arr(sprintf(self::UPDATE_DESC_QUERY, $aId, $newDesc, $s, $e));
      }
    }
    return true;
  }

  public function consolidateExisting($accountId) {
    $networkranges = $this->cmdb->sql2arr('SELECT ip_start, ip_end, description FROM cm_' . $accountId . '.networks ORDER BY ip_start');
    foreach ($networkranges as $range) {
      $this->consolidate($accountId, $range['ip_start'], $range['ip_end'], $range['description']);
    }
    $this->combineCClasses($accountId, 50);
  }

  private function combineCClasses($accountId, $perc) {
    // if a certain number of IPs within a class C are covered, just use the whole C class
    // don't make this assumption with larger blocks.
    $nr = $this->cmdb->sql2arr('SELECT ip_start, ip_end, description FROM cm_' . $accountId . '.networks ORDER BY ip_start');
    for ($i=0; $i<count($nr) ; ++$i) { // loop through all rows
      $range = $nr[$i]['ip_end'] - $nr[$i]['ip_start'] + 1; // range is how many ip's in that row
      $s = explode('.', long2ip($nr[$i]['ip_start'])); // converting long to ip, and getting each octet
      $e = explode('.', long2ip($nr[$i]['ip_end']));
      // if row is within the same c class (the first two octets are matching),
      // then loop through next rows to see if in same c class
      if ($range<65536 && $s[0] == $e[0] && $s[1] == $e[1]) {
        $addthese = array();
        for ($a=1; $a<(count($nr)-$i); ++$a) {
          if ($nr[$i+$a]['ip_end'] > ip2long($e[0].'.'.$e[1].'.255.255')) { // if outside of c class, skip
            break;
          } else { // if its within the same c class, add the diff to range
            $range += ($nr[$i+$a]['ip_end'] - $nr[$i+$a]['ip_start'] + 1);
            $addthese[] = $nr[$i+$a];
          }
        }
        // if the range meets the minimum perc, combine the rows into c class and remove existing rows
        // otherwise just leave as is
        if (isset($addthese) && count($addthese)>0 && $range >= $perc/100*65535) {
          $addthese[] = $nr[$i];
          $this->cmdb->sql2arr(sprintf(self::INSERT_QUERY, $accountId, ip2long($s[0].'.'.$s[1].'.0.0'), ip2long($e[0].'.'.$e[1].'.255.255'), $nr[$i]['description']));
          foreach ($addthese as $at) {
            $this->cmdb->sql2arr(sprintf(self::DELETE_QUERY, $accountId, $at['ip_start'], $at['ip_end']));
            $curDesc = $this->cmdb->sql2arr(sprintf(self::GET_DESC_QUERY, $accountId, ip2long($s[0].'.'.$s[1].'.0.0'), ip2long($e[0].'.'.$e[1].'.255.255')));
            $this->updateDesc($curDesc[0]['description'], $at['description'], $accountId, ip2long($s[0].'.'.$s[1].'.0.0'), ip2long($e[0].'.'.$e[1].'.255.255'));
          }
          $this->combineCClasses($accountId, $perc);
          break;
        }
      }
    }
  }
}

