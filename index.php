<?php

use Norse\Util\Sql;

require_once(__DIR__ . '/vendor/autoload.php');

$app = \Base::Instance();
$app->set('AUTOLOAD', 'src/;vendor/');
$app->config('./local.cfg.ini');
$app->set('CACHE', TRUE);
$app->set('ESCAPE', FALSE);
$app->set('DEBUG', 2);
$app->set('JAR.expire', time() + (12*3600)); // SESSION timeout 12 hours.

// Static Routes.
$app->route('GET /', 'Norse\Index->get');
$app->route('GET /colors', 'Norse\Colors->get');
$app->route('GET /videos', 'Norse\Videos->get');

// API Routes.
// $app->route('GET /visionlist', 'Norse\VisionList->get');
// $app->route('GET /ranges', 'Norse\VisionList->getRanges');
$app->run();