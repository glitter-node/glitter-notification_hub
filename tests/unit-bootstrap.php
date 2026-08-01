<?php

require dirname(__DIR__, 4).'/vendor/autoload.php';

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;

$container = new Container;
Container::setInstance($container);
Facade::setFacadeApplication($container);
$container->instance('config', new ConfigRepository([
    'notification-hub' => require dirname(__DIR__).'/config/notification-hub.php',
]));
$container->instance('events', new Dispatcher($container));
$container->instance('translator', new Translator(new ArrayLoader, 'en'));

$connection = new Connection(null);
$resolver = new ConnectionResolver(['default' => $connection]);
$resolver->setDefaultConnection('default');
Model::setConnectionResolver($resolver);
Model::setEventDispatcher($container->make('events'));

spl_autoload_register(static function (string $class): void {
    $prefix = 'Modules\\Glitter\\NotificationHub\\';
    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $path = dirname(__DIR__).'/src/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    if (is_file($path)) {
        require $path;
    }
});
