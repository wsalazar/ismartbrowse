<?php

// include the prod configuration
if (getenv('SERVER_CONTEXT') == 'dev') {
    require __DIR__.'../config/config_dev.php';
}
if (getenv('SERVER_CONTEXT') == 'prod') {
    require __DIR__.'../config/config_prod.php';
}require __DIR__.'/prod.php';

// enable the debug mode
$app['debug'] = true;
