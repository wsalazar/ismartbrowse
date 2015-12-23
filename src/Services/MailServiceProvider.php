<?php
namespace Services;

use Silex\Application;
use Silex\ServiceProviderInterface;

class MailServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
//        $app['service.mailservice'] = $app->share(function($app) {
//            return new \Services\MailService($app['email.host'], $app['email.port'], $app['email.sender'], $app['email.password.sender']);
//        });
    }

    public function boot(Application $app)
    {

    }

}