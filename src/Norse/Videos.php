<?php namespace Norse;

class Videos {
  public function get($app) {
    $app->set('route', 'Videos');
    echo \View::instance()->render('views/videos.php');
  }

  public function template($app) {
    echo \View::instance()->render('views/videos.php','text/html');
  }
}
