<?php

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Cache\Backend\Memcache as BackendMemcache;

try {

    // Register an autoloader
    $loader = new Loader();
    $loader->registerDirs(array(
        '../app/controllers/',
        '../app/models/',
        '../app/services'
    ))->register();

    // Create a DI
    $di = new FactoryDefault();

    // Setup the view component
    $di->set('view', function () {
        $view = new View();
        $view->setViewsDir('../app/views/');
        return $view;
    });

    $di->set('db', function () {
        return new DbAdapter(array(
            "host"     => "127.0.0.1",
            "username" => "k11",
            "password" => "k34fnk2wjefwe",
            "dbname"   => "k11"
        ));
    });

    $config = array(
        "host"     => "127.0.0.1",
        "username" => "k11",
        "password" => "k34fnk2wjefwe",
        "dbname"   => "k11"
    );

    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($config);

    $di->set('pdoconnecton', $connection);

    // Setup a base URI so that all generated URIs include the "tutorial" folder
    $di->set('url', function () {
        $url = new UrlProvider();
        $url->setBaseUri('/bot/');
        return $url;
    });


    $di->set('modelsCache', function () {

        // Cache data for one day by default
        $frontCache = new FrontendData(
            array(
                "lifetime" => 86400
            )
        );

        // Memcached connection settings
        $cache = new BackendMemcache(
            $frontCache,
            array(
                "host" => "localhost",
                "port" => "11211"
            )
        );

        return $cache;
    });


    // Handle the request
    $application = new Application($di);

    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo "Exception: ", $e->getMessage();
}