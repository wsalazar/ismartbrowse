<?php

// include the prod configuration
if (getenv('SERVER_CONTEXT') == 'dev') {
    require 'config_dev.php';
}
if (getenv('SERVER_CONTEXT') == 'prod') {
    require 'config_prod.php';// Local
}
require __DIR__.'/prod.php';
// enable the debug mode
$app['debug'] = true;
