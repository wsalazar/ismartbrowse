<?php
// include the prod configuration

require __DIR__.'/config.php';

// Local
$app['locale'] = 'fr';
$app['session.default_locale'] = $app['locale'];
$app['translator.messages'] = array(
    'fr' => __DIR__.'/../resources/locales/fr.yml',
);
// Cache
$app['cache.path'] = __DIR__ . '/../cache';

// Http cache
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

// Twig cache
$app['twig.options.cache'] = $app['cache.path'] . '/twig';

// Assetic
$app['assetic.enabled']              = true;
$app['assetic.path_to_cache']        = $app['cache.path'] . '/assetic' ;

$app['assetic.path_to_web']          = __DIR__ . '/../../web/assets';
$app['assetic.input.path_to_assets'] = __DIR__ . '/../assets';

$app['assetic.input.path_to_css']       = $app['assetic.input.path_to_assets'] . '/less/style.less';
$app['assetic.output.path_to_css']      = 'css/styles.css';
$app['assetic.input.path_to_js']        = array(
    __DIR__.'/../../vendor/twitter/bootstrap/js/bootstrap-tooltip.js',
    __DIR__.'/../../vendor/twitter/bootstrap/js/*.js',
    $app['assetic.input.path_to_assets'] . '/js/script.js',
);
$app['assetic.output.path_to_js']       = 'js/scripts.js';

$app['config.input.path_to_config']     = __DIR__ . '/config_prod.php';

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => DB_HOST,
    'dbname'   => DB_NAME,
    'user'     => USER,
    'password' => PASSWORD,
);

$app['swiftmailer.options'] = array(
    'host' => EMAIL_HOST,
    'port' => EMAIL_PORT,
    'username' => EMAIL,
    'password' => EMAIL_PASS,
    'encryption' => '',
    'auth_mode' => '',
);

// User
$app['security.users'] = array('username' => array('ROLE_USER', 'password'));
