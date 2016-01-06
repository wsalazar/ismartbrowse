<?php

// include the prod configuration
if (getenv('SERVER_CONTEXT') == 'dev') {
    require __DIR__.'/config_dev.php';
}
if (getenv('SERVER_CONTEXT') == 'prod') {
    require __DIR__.'/config_prod.php';// Local
}
require __DIR__.'/prod.php';
// enable the debug mode
$app['debug'] = true;
