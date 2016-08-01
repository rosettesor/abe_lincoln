<?php namespace Norse;

class Index {
  public function get($app) {
    $user = $app->get('SESSION.user');
    if($user && isset($user->accounts)){
      header('Location: ./darkvision');
    }
    echo \View::instance()->render('views/index.php');
  }
}
