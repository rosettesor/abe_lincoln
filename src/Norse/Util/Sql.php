<?php namespace Norse\Util;

class Sql {
  public $dbh;

  public function __construct($app) {
    $this->host = $app->get('db.host');
    $this->username = $app->get('db.username');
    $this->password = $app->get('db.password');
    $this->dbname = $app->get('db.dbname');
    $this->connect();
  }

  private function connect() {
    $dbh = new \mysqli($this->host, $this->username, $this->password, $this->dbname);

    if ($dbh->connect_errno) {
      die("Failed to connect to MySQL: (" . $dbh->connect_errno . ") " . $dbh->connect_error);
    }

    $this->dbh = $dbh;
  }

  public function sql2val($strSQL){
    $arrResult = array();
    $this->dbh->real_query($strSQL);
    $res = $this->dbh->use_result();
    if ($res === FALSE) {
      return FALSE;
    } else {
      $row = $res->fetch_array();
      if ($row === '' || $row === FALSE || $row === NULL) {
        return FALSE;
      }
      return $row[0];
    }
  }

  function sql2arr($strSQL) {
    $arrResult = array();
    // TODO: Use of mysqli prepared statements?
    $this->dbh->real_query($strSQL);
    if ($res = $this->dbh->use_result()) {
      while ($row = $res->fetch_assoc()) {
        array_push($arrResult, $row);
      }
    } else {
    }
    return $arrResult;
  }


}
