<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

if (getenv('SERVER_CONTEXT') == 'dev') {
    require __DIR__.'/../resources/config/config_dev.php';
}
if (getenv('SERVER_CONTEXT') == 'prod') {
    require __DIR__.'/../resources/config/config_prod.php';
}

require __DIR__.'/../resources/config/prod.php';
require __DIR__.'/../src/app.php';

require __DIR__.'/../src/controllers.php';

$app['http_cache']->run();
