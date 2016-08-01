<?php namespace Norse;

use Valitron\Validator;

class VisionList extends AuthRoute {

  private $query;
  private $sql;
  private $filters;
  private $sortColumns;
  private $sortSuffix;
  private $ip;
  private $score;

  static private $COMPARISON_OPERATORS = array('=', '>', '<', '>=', '<=', '<>', '!=');

  public function __construct() {
    $this->filters = array();
    $this->sortColumns = array(
      'ip' => 'ip',
      'org' => 'org',
      'intIp' => 'intIp',
      'risk' => 'risk',
      'diff' => 'diff',
      'flagged' => 'flagged',
      'priority' => 'priority',
      'event_count_recent' => 'event_count_recent',
      'event_count_total' => 'event_count_total',
      'has_notes' => 'has_notes',
      'has_pcap' => 'has_pcap',
      'has_files' => 'has_files',
      'ls' => 'ls',
      'default' => 'flagged DESC, sorting_weight DESC, ls'
    );
    $this->sortSuffix = ' a.ls DESC ';
  }

  public function get($app) {
    $this->app = $app;

    if (!$this->authorize('DarkVision', 'access')) {
      $app->error(403, '', NULL, $bounceToLog=true);
    }
    if (!$this->validate($_GET)) {
      $app->error(400);
    }

    $this->buildQuery();
    $results = $this->getResults();
    $this->util->serialize($results, $this->rep);
  }

  private function buildQuery() {
    // Eventually the table will be dynamic as the tables represent different customer ips.
    $select = '

      SELECT inet_ntoa(a.ip) AS ip, a.ip AS intIp, a.sorting_weight, a.allowed_api_call,
      event_count_recent AS intDefcon, event_count_total, flagged, risk, diff, cats,
      org, a.ls,asn,aso,lat,lon,isp,
      cc,rc,priority, locked,
      has_notes, note, b.user as note_user, has_pcap, i.fid as has_files, CONCAT("[",history,"]") as history, z.ip_start as monitored, n.description AS net_description,
      FROM_UNIXTIME(t.ts, "%Y-%c-%d %H:%i") as flagWhen, t.user_name AS flagWho
      FROM cm_'.$this->intAccount.'.ip a
      LEFT OUTER JOIN (SELECT ip,note, user FROM cm_'.$this->intAccount.'.notes group by ip order by ts desc) b on a.ip=b.ip
      LEFT OUTER JOIN cm_'.$this->intAccount.'.history c on a.ip=c.ip
      LEFT OUTER JOIN cm_'.$this->intAccount.'.norse_added z ON a.ip >= z.ip_start AND a.ip <= z.ip_end
      LEFT OUTER JOIN cm_'.$this->intAccount.'.networks n ON a.ip >= n.ip_start AND a.ip <= n.ip_end
      LEFT OUTER JOIN cm_'.$this->intAccount.'.ip2file i ON a.ip = i.ip
      LEFT OUTER JOIN (SELECT ip, ts, user_name FROM cm_'.$this->intAccount.'.activities WHERE `group`="Flag" ORDER BY ts DESC) t on a.ip=t.ip ';

    $limit = '';
    $offset = '';
    $this->rep = $this->util->getIfSet('rep', 'json');

    if ($this->rep === 'json') {
      $limit = 'LIMIT ' . $this->util->getIfSet('limit', 255) . ' ';
      $offset = 'OFFSET ' . $this->util->getIfSet('offset', 0);
    }

    $filters = $this->getFilters();
    $where = ($filters) ? "WHERE $filters " : '';
    $sort = ' group by inet_ntoa(a.ip) ORDER BY ' . $this->getSort() . $this->sortSuffix;
    $this->query = $select . $where . $sort . $limit . $offset;
  }

  private function getFilters() {
    $filter_str = '';
    $filters_json = $this->util->getIfSet('f', []);
    if (empty($filters_json)) {
      return;
    }

    $filters = json_decode($filters_json);
    foreach ($filters as $key => $value) {
      if (method_exists($this, 'getFilter_' . $key)) {
        array_push($this->filters, call_user_func(array($this, 'getFilter_' . $key), $value));
      }
    }

    return implode(' AND ', array_filter($this->filters));

  }

  private function getSort() {
    if (!isset($_GET['sort'])) {
      return 'event_count_recent ' . $this->getSortDir() . ',';
    }
    $v = new Validator($_GET);
    $v->rule('in', 'sort', array_keys($this->sortColumns));
    if (!$v->validate()) {
      $this->app->error(400, 'Invalid sort column ' . $_GET['sort']);
    }
    return $this->sortColumns[$_GET['sort']] . ' ' . $this->getSortDir() . ',';
  }

  private function getSortDir() {
    $dirs = array('asc', 'desc');
    $v = new Validator($_GET);
    $v->rule('in', 'dir', $dirs);

    if (!$v->validate()) {
      $this->app->error(400, 'Invalid sort direction');
    }

    $dir = strtoupper($this->util->getIfSet('dir', 'asc'));
    return $dir;
  }

  private function getFilter_has_notes($filter) {
    if(!$this->validateBool($filter)) {
      $this->app->error(400, 'has_notes must be boolean');
    }

    if ($filter->v === TRUE) {
      return 'has_notes=1';
    }

    return '';
  }

  private function getFilter_has_pcap($filter) {
    if(!$this->validateBool($filter)) {
      $this->app->error(400, 'has_pcap must be boolean');
    }

    if ($filter->v === TRUE) {
      return 'has_pcap=1';
    }

    return '';
  }

  private function getFilter_has_files($filter) {
    if(!$this->validateBool($filter)) {
      $this->app->error(400, 'has_files must be boolean');
    }

    if ($filter->v === TRUE) {
      return 'has_files=1';
    }

    return '';
  }

  private function getFilter_org($filter) {
    if (strlen($filter->v) > 2) {
      if(!preg_match('/^([a-z0-9. ])+$/i', $filter->v)) {
        $this->app->error(400, 'Org defined is too complex.');
      }
      return 'org like "%'.$filter->v.'%"';
    }

    return '';
  }

  private function getFilter_diff($filter) {
    $filter_str = '';
    $v = new Validator((array) $filter);
    $v->rule('numeric', 'v');
    $v->rule('min', 'v', 0);
    if (!$v->validate()) {
      $this->app->error(400, 'diff must be an integer greater than or equal to 0');
    }

    if ($filter->v >= 0 && $filter->v !== NULL) {
      $filter_str = 'diff > ' . $filter->v;
    }

    return $filter_str;
  }

  private function getFilter_risk($filter) {
    $filter_str = '';
    $v = new Validator((array) $filter);
    $v->rule('numeric', 'v');
    $v->rule('min', 'v', 0);
    $v->rule('in', 'op', self::$COMPARISON_OPERATORS);
    if (!$v->validate()) {
      $this->app->error(400, 'risk must be an integer greater than or equal to 0');
    }

    if ($filter->v >= 0 && $filter->v !== NULL) {
      $filter_str = 'risk '.$filter->op.' ' . $filter->v;
    }

    return $filter_str;
  }

  private function getFilter_flagged($filter) {
    if(!$this->validateBool($filter)) {
      $this->app->error(400, 'flagged must be boolean');
    }
    if ($filter->v === TRUE) {
      return 'flagged>0';
    }

    return '';
  }

  private function getFilter_interesting($filter) {
    $filter_str = '';
    $v = new Validator((array) $filter);
    $v->rule('numeric', 'v');
    $v->rule('min', 'v', 0);
    $v->rule('max', 'v', 1);
    $v->rule('in', 'op', self::$COMPARISON_OPERATORS);
    if (!$v->validate()) {
      $this->app->error(400, 'interesting must be boolean');
    }

    if ($filter->v > 0) {
      //$filter_str = 'interesting '.$filter->op.' ' . $filter->v;
    }

    return $filter_str;
  }

  private function getFilter_event_count_total($filter) {
    $filter_str = '';
    $v = new Validator((array) $filter);
    $v->rule('numeric', 'v');
    $v->rule('min', 'v', 0);
    if (!$v->validate()) {
      $this->app->error(400, 'event_count_total must be an integer greater than or equal to 0');
    }

    if ($filter->v >= 0 && $filter->v !== NULL) {
      $filter_str = 'event_count_total > ' . $filter->v;
    }

    return $filter_str;
  }

  private function getFilter_event_count_recent($filter) {
    $filter_str = '';
    $v = new Validator((array) $filter);
    $v->rule('numeric', 'v');
    $v->rule('min', 'v', 0);
    if (!$v->validate()) {
      $this->app->error(400, 'event_count_recent must be an integer greater than or equal to 0');
    }

    if ($filter->v >= 0 && $filter->v !== NULL) {
      $filter_str = 'event_count_recent > ' . $filter->v;
    }

    return $filter_str;
  }

  private function getFilter_ip($filter) {
    $v = new Validator((array) $filter);
    $v->rule('ip', 'start');
    $v->rule('ip', 'end');
    if (!$v->validate()) {
      $this->app->error(400, 'ip start and end must be valid IP addresses');
    }

    return '' .
      'a.ip >= ' . ip2long($filter->start) . ' ' .
      'AND a.ip <= ' . ip2long($filter->end);
  }

  private function getFilter_ipNotIn($filter) {
      $v = new Validator((array) $filter);
      $v->rule('array', 'v');
      if (!$v->validate()) {
          $this->app->error(400, 'ipNotIn Should be an array');
      }
      if (!empty($filter->v )) {
          return 'a.ip not in (' . implode(',', $filter->v) . ') ';
      }
      return '' ;
  }

  private function getFilter_net_desc($filter) {
    // query cm_XX.networks for ip_start and ip_end with that description name
    if ($filter->v != '') {
      $q = 'SELECT ip_start, ip_end FROM cm_' .$this->intAccount. '.networks WHERE description="'.$filter->v.'";';
      $results = \Norse\Util\DB::getInstance('CM')->sql2arr($q);

      $all_desc = '';
      for ($i=0; $i < count($results); $i++) {
        if ($i == (count($results) - 1)) {
          $all_desc .= 'a.ip BETWEEN ' . $results[$i]['ip_start'] . ' AND ' . $results[$i]['ip_end'] . ' ';
        } else {
          $all_desc .= 'a.ip BETWEEN ' . $results[$i]['ip_start'] . ' AND ' . $results[$i]['ip_end'] . ' OR ';
        }
      }

      return $all_desc;
    }
  }

  private function getFilter_dismiss($filter) {
    if(!$this->validateBool($filter)) {
      $this->app->error(400, 'flagged must be boolean');
    }
    if ($filter->v === TRUE) {
      return 'priority=2';
    } else {
      return 'priority<2';
    }
  }

  private function validateBool($filter) {
    $v = new Validator((array) $filter);
    $v->rule('in', 'v', array(TRUE, FALSE), TRUE);
    return $v->validate();
  }

  private function getResults() {
    $cid = md5($this->query);
    $cached = $this->app->get('visionlist_' . $cid);

    if ($this->app->get('environment') === 'prod' && $cached) {
      return $cached;
    }

    // cache results for an hour.
    $results = \Norse\Util\DB::getInstance('CM')->sql2arr($this->query);
    $this->app->set('visionlist_' . $cid, $results, 3600);
    return $results;
  }

  private function validate($params) {
    $v = new Validator($params);
    $v->rule('numeric', 'limit');
    $v->rule('numeric', 'offset');
    $v->rule('in', 'rep', array('json', 'csv'));
    return $v->validate();
  }

  public function getRanges($app) {
    $this->app = $app;

    if (!$this->authorize('DarkVision', 'access')) {
      $app->error(403, '', NULL, $bounceToLog=true);
    }

    $this->buildRangeQuery();
    // echo '<pre>' . $this->query . '</pre>';
    \Norse\Util\DB::getInstance('CM')->query('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
    $results = \Norse\Util\DB::getInstance('CM')->sql2arr($this->query);
    $this->util->serialize($results, 'json');
  }

  private function buildRangeQuery() {
    $this->query = 'SELECT DISTINCT description FROM cm_' .$this->intAccount. '.networks WHERE description > "";';
  }

  public function forceScore($app) {
    $this->app = $app;
    $this->app->clear('CACHE');
    if (!$this->authorize('DarkVision', 'create network') || !$this->authorize('DarkVision', 'delete network')) {
      $app->error(403, '', NULL, $bounceToLog=true);
    }
    $this->validateScore($app->get('BODY'));
    // update in ip table and lock row
    \Norse\Util\DB::getInstance('CM')->query('UPDATE cm_' . $this->intAccount . '.ip SET risk=' . $this->score . ', locked=1 WHERE ip=inet_aton("' . $this->ip . '")');
    \Norse\Util\Activity::log('Update', 'Risk score for "' . $this->ip . '" manually set to ' . $this->score, 'Lock');
  }

  public function unlockScore($app) {
    $this->app = $app;
    $this->app->clear('CACHE');
    if (!$this->authorize('DarkVision', 'create network') || !$this->authorize('DarkVision', 'delete network')) {
      $app->error(403, '', NULL, $bounceToLog=true);
    }
    $this->validateScore($app->get('BODY'));
    // unlock row
    \Norse\Util\DB::getInstance('CM')->query('UPDATE cm_' . $this->intAccount . '.ip SET locked=0 WHERE ip=inet_aton("' . $this->ip . '")');
    \Norse\Util\Activity::log('Update', 'Risk score for "' . $this->ip . '" unlocked', 'Lock');
  }

  private function validateScore($body) {
    parse_str($body, $params);
    if (!$params) {
      $this->app->error(400);
    }
    $v = new Validator($params);
    $v->rule('ip', 'ip');
    $v->rule('numeric', 'score');
    if (!$v->validate()) {
      $this->app->error(400);
    }
    $this->ip = $params['ip'];
    $this->score = $params['score'];
  }

}
