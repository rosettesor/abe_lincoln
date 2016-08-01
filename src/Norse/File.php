<?php namespace Norse;

use Valitron\Validator;
use Norse\Util\ZipStream;

class File extends AuthRoute {
  private $app;
  private $sql;
  private $fileId;
  private $query;
  private $filePath;

  public function __construct() {
    $this->sql = Util\DB::getInstance('CM');
  }

  public function get($app, $args) {
    $this->app = $app;
    if (!$this->authorize('DarkVision', 'access')) {
        $app->error(403, '', NULL, $bounceToLog=true);
    }

    $this->validate($args);
    $this->buildQuery();
    $this->filePath = $app->get('zipfiles');

    $filearr = $this->sql->sql2arr($this->query)[0];

    if (empty($filearr)) {
      $this->app->error(404);
    }

    if ($filearr['hash']) {
      $zip = new ZipStream($filearr['fn'] . '.zip');
      $zip->add_file($filearr['fn'], file_get_contents($this->filePath . $filearr['hash'])); //add a file to it
      $zip->finish();
    } else {
      $zip = new ZipStream($filearr['fn'] . '.zip');
      $zip->add_file($filearr['fn'], file_get_contents($this->filePath . $filearr['fn'])); //add a file to it
      $zip->finish();
    }
  }

  private function validate($args) {
    $v = new Validator($args);
    $v->rule('numeric', 'id');
    $v->rule('min', 'id', 1);

    if (!$v->validate()) {
      $this->app->error(400, 'Parameter id must be a number greater than 0');
    }

    $this->fileId = $args['id'];
  }

  private function buildQuery() {
    $this->query = '' .
      'SELECT fn, hash ' .
      'FROM cm_'.$this->intAccount.'.files ' .
      'WHERE id=' . $this->fileId;
  }

}
