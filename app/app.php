<?php

//use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\VarDumperServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

$app->register(new HttpFragmentServiceProvider());
$app->register(new MonologServiceProvider());
$app->register(new RoutingServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new SessionServiceProvider());

// Routing
$app->get('/', 'Georoadbook\Controller\Controller::indexAction')
    ->bind('index');

$app->post('/upload', 'Georoadbook\Controller\Controller::uploadAction')
    ->bind('/upload');

$app->get('/roadbook/{id}.html', 'Georoadbook\Controller\Controller::editAction')
    ->bind('edit');

$app->post('/save', 'Georoadbook\Controller\Controller::saveAction')
    ->bind('save');

$app->post('/delete', 'Georoadbook\Controller\Controller::deleteAction')
    ->bind('delete');

$app->post('/export', 'Georoadbook\Controller\Controller::exportAction')
    ->bind('export');

$app->get('/login', 'Georoadbook\Controller\Controller::loginAction')
    ->bind('login');

$app['monolog.name']    = 'georoadbook';
$app['monolog.logfile'] = __DIR__ . '/logs/' . $app['monolog.name'] . '.log';

//header('Content-type: text/html; charset=utf-8');

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/templates',
    'twig.options' => [
        'cache' => false,
        'debug' => $app['debug']
    ]
));

$app->extend('twig', function($twig, $app) {
    $twig->addGlobal('suffix_css_js', $app['suffix_css_js']);

    if ($app['debug']) {
        $twig->addExtension(new Twig_Extension_Debug());
    }
    return $twig;
});

if ($app['debug']) {
    $app->register(new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => __DIR__ . '/cache/profiler',
    ));
}

