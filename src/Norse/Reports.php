<?php namespace Norse;

class Reports extends AuthRoute {
  // public function get($app) {
  //   if (!$this->authorize('DarkVision', 'access')) {
  //       $app->error(403, '', NULL, $errorMessage=true);
  //   }
  //   $app->set('route', 'Reports');
  //   $app->set('menu', $this->renderMenu($app, $this->getMenu()));
  //   if ($this->authorize('DarkVision', 'update file')) {
  //       $app->set('fileupdateaccess', true);
  //   }
  //   if ($this->authorize('DarkVision', 'create file')) {
  //       $app->set('filecreateaccess', true);
  //   }
  //   if ($this->authorize('DarkVision', 'delete file')) {
  //       $app->set('filedeleteaccess', true);
  //   }
  //   echo \View::instance()->render('views/reports.php');
  // }

  public function clearCache($app) {
    if (!$this->authorize('DarkVision', 'create file')) {
        $app->error(403, '', NULL, $errorMessage=true);
    }
    $app->clear('CACHE');

    $path = 'tmp/cache/'.$this->intAccount. '/'.date('Y-m-d').'/';
    if(is_dir($path)){
      foreach (scandir($path) as $item) {
        if ($item == '.' || $item == '..') { continue; } else { unlink($path.$item); }
      }
      rmdir($path);
    }

    $msg = new \stdClass();
    $msg->message = 'Caches cleared successfully';
    $this->util->serialize($msg);
  }

  public function template($app) {

    $fileupdateaccess = $filecreateaccess = $filedeleteaccess = false;
    if ($this->authorize('DarkVision', 'update file')) {
        $fileupdateaccess = true;
    }
    if ($this->authorize('DarkVision', 'create file')) {
        $filecreateaccess = true;
    }
    if ($this->authorize('DarkVision', 'delete file')) {
        $filedeleteaccess = true;
    }

    echo \View::instance()->render('views/templates/reports.php','text/html',array(
        'fileupdateaccess'=>$fileupdateaccess,
        'filecreateaccess'=>$filecreateaccess,
        'filedeleteaccess'=>$filedeleteaccess)
    );

  }
}
