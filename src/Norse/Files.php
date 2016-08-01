<?php namespace Norse;

use Valitron\Validator;

class Files extends AuthRoute {
  private $query;
  private $id;
  private $desc;
  private $limit;
  private $offset;
  private $fn;

  public function __construct() {}

  public function get($app, $params) {
    if (!$this->authorize('DarkVision', 'access')) {
        $app->error(403, '', NULL, $bounceToLog=true);
    }
    if (!$this->validate_get($_GET)) {
      $app->error(400);
    }

    $this->buildQuery();
    $results = \Norse\Util\DB::getInstance('CM')->sql2arr($this->query);
    return $this->util->serialize($results, 'json');
  }

  private function validate($params) {
    $v = new Validator($params);
    $v->rule('required', 'id');
    $v->rule('numeric', 'id');
    $this->id = $params['id'];
    return $v->validate();
  }

  private function validate_get($params) {
    $v = new Validator($params);
    $v->rule('required', 'offset');
    $v->rule('numeric', 'offset');
    $v->rule('required', 'limit');
    $v->rule('numeric', 'limit');
    $this->offset = $params['offset'];
    $this->limit = $params['limit'];
    return $v->validate();
  }

  private function buildQuery() {
    $select = 'SELECT id,fn,ts,type,description,user ' .
      'FROM cm_'.$this->intAccount.'.files order by ts desc ';
    $limit = 'LIMIT ' . $this->limit . ' ';
    $offset = 'OFFSET ' . $this->offset . ' ';
    $this->query = $select . $limit . $offset;
  }

  public function add($app,$params){
    if (!$this->authorize('DarkVision', 'access')) {
      $app->error(403, '', NULL, $bounceToLog=true);
    }

    $strFn=$_FILES['file']['name'];
    $arrFn=explode('.', $strFn);
    $strType=$arrFn[count($arrFn)-1];
    $strPath=$app->get('zipfiles');
    $hashFn = md5($strFn.time());
    if(in_array($strType, array('pdf','anb','mtgx','docx','zip'))){
      $success=move_uploaded_file($_FILES['file']['tmp_name'], $strPath.$hashFn);
      if($success){
        $q='INSERT INTO cm_'.$this->intAccount.'.files (fn,ts,type,user,hash) values ("'.$strFn.'",ADDTIME(now(), "-7:00:00"),"'.$strType.'", "'.$this->user->name.'", "'. $hashFn .'" )';
        $results = \Norse\Util\DB::getInstance('CM')->sql2arr($q);
        \Norse\Util\Activity::log('Add', '"' .$strFn. '" added to Reports', 'Files');
        return $this->util->serialize($_FILES, 'json');
      }
    } else {
      return $this->util->serialize('FAIL', 'json');
    }
  }

  public function update($app,$params){
    if (!$this->authorize('DarkVision', 'update file')) {
      $app->error(403, '', NULL, $bounceToLog=true);
    }

    $this->updateValidate($app->get('BODY'));
    $this->buildUpdateQuery();
    $results = \Norse\Util\DB::getInstance('CM')->sql2arr($this->query);
    $this->util->serialize(array('id' => $this->id, 'desc' => $this->desc), 'json');
  }

  public function delete($app,$params){
    if (!$this->authorize('DarkVision', 'delete file')) {
      $app->error(403, '', NULL, $bounceToLog=true);
    }

    parse_str($app->get('BODY'), $params);
    if (!$this->validate($params)) {
      $app->error(400);
    }

    $this->preBuildDeleteQuery();
    $this->buildDeleteQuery($app);

    $msg = new \stdClass();
    $msg->message = 'Successfully removed file';
    $this->util->serialize($msg);
  }

  private function updateValidate($body) {
    // Error out on malformed JSON.
    if (!$params = json_decode($body, TRUE)) {
      $this->app->error(400);
    }

    $v = new Validator($params);
    $v->rule('required', 'id');
    $v->rule('numeric', 'id');
    $v->rule('text', 'desc');

    if (!$v->validate()) {
      $this->app->error(400);
    }

    $this->id = $params['id'];
    $this->desc = $params['desc'];
  }

  private function validateExists($params) {
    $v = new Validator($params);
    $v->rule('required', 'file');
    $v->rule('text', 'file');
    $this->fn = $params['file'];
    return $v->validate();
  }

  private function buildUpdateQuery() {
    $this->query = 'UPDATE cm_'.$this->intAccount.'.files SET description="'.$this->desc.'" WHERE id='.$this->id.';';
  }

  private function preBuildDeleteQuery() {
    // get ip's affected by removing file; update has_files in ip table if no other associated files for each ip
    $ips = \Norse\Util\DB::getInstance('CM')->sql2arr('SELECT ip FROM cm_' .$this->intAccount. '.ip2file WHERE fid=' .$this->id);
    foreach ($ips as $ip) {
      $count = \Norse\Util\DB::getInstance('CM')->sql2arr('SELECT COUNT(*) FROM cm_' .$this->intAccount. '.ip2file WHERE ip=' .$ip['ip']);
      if ($count[0]['COUNT(*)'] < 2) {
        \Norse\Util\DB::getInstance('CM')->sql2arr('UPDATE cm_' . $this->intAccount. '.ip SET has_files=0 WHERE ip=' .$ip['ip']);
      }
    }
    // remove from ip2file
    \Norse\Util\DB::getInstance('CM')->sql2arr('DELETE FROM cm_' .$this->intAccount. '.ip2file WHERE fid=' .$this->id);
  }

  private function buildDeleteQuery($app) {
    // remove from files table
    $this->fn = \Norse\Util\DB::getInstance('CM')->sql2arr('SELECT fn, hash FROM cm_' .$this->intAccount. '.files WHERE id=' .$this->id);
    \Norse\Util\Activity::log('Remove', '"' .$this->fn[0]['fn']. '" removed from Reports', 'Files');
    $this->query = 'DELETE FROM cm_' .$this->intAccount. '.files WHERE id=' .$this->id. ';';
    \Norse\Util\DB::getInstance('CM')->sql2arr($this->query);
    // $strPath=$app->get('zipfiles'); // if we want to actually unlink the file in the dir
    // unlink($strPath.$this->fn[0]['hash']);
  }

}
/*
ALTER TABLE `files` CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
*/
