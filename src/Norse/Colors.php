<?php namespace Norse;

class Colors {
  public function get($app) {
    $app->set('route', 'Colors');
    echo \View::instance()->render('views/colors.php');
  }

  public function template($app) {
    echo \View::instance()->render('views/colors.php','text/html');
  }
}
