<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

if (getenv('SERVER_CONTEXT') == 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('html_errors', 'On');
    date_default_timezone_set('America/New_York');
}
if (getenv('SERVER_CONTEXT') == 'prod') {
    date_default_timezone_set('America/New_York');
}
require __DIR__.'/../resources/config/prod.php';
require __DIR__.'/../src/app.php';

require __DIR__.'/../src/controllers.php';

$app['http_cache']->run();
