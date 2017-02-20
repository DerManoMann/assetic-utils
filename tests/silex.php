<?php

use Assetic\AssetWriter;
use Assetic\AssetManager;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Radebatz\Assetic\Factory\Worker\VersioningWorker;
use Radebatz\Assetic\LazyAssetWriter;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

// use twig
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__.'/Resources/twig',
]);

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', []);
})->bind('home');

// assetic debug
$debug = false;

// use basic css - should be red!!
$app->get('/basic/', function () use ($app) {
    // basic factory...
    $factory = new AssetFactory(__DIR__.'/Resources/assets');
    $factory->setAssetManager($am = new LazyAssetManager($factory));
    $factory->setDebug($debug);
    // here prefix is the (absolute) context path of the asset url
    $factory->addWorker(new VersioningWorker('/assetic-utils/tests/Resources/webassets/'));

    $app['twig']->addExtension(new AsseticExtension($factory));

    $response = $app['twig']->render('basic.twig', []);

    return $response;
})->bind('basic');

// precompile listed assets
$app->get('/refresh-web-assets/', function () use ($app) {
    $factory = new AssetFactory(__DIR__.'/Resources/assets');
    $factory->setAssetManager($am = new LazyAssetManager($factory));
    $factory->setDebug($debug);

    // loop through all your templates
    foreach (['fancyred' => 'plugins/fancy/css/fancy_red.css'] as $name => $resource) {
        $asset = $factory->createAsset($resource);
        $am->set($name, $asset);
    }

    $writer = new LazyAssetWriter(__DIR__.'/Resources/webassets', [], $debug);
    $writer->writeManagerAssets($am);

    return 'all refreshed';
})->bind('refresh-web-assets');

// precompile based on resources referenced in templates
$app->get('/process-templates/', function () use ($app) {
    // basic factory...
    $factory = new AssetFactory(__DIR__.'/Resources/assets');
    $factory->setAssetManager($am = new LazyAssetManager($factory));
    $factory->setDebug($debug);
    $factory->addWorker(new VersioningWorker('Resources/webassets/'));

    $app['twig']->addExtension(new AsseticExtension($factory));

    // enable loading assets from twig templates
    $am->setLoader('twig', new TwigFormulaLoader($app['twig']));

echo '<pre>';
    // loop through all your templates
    foreach (glob(__DIR__.'/Resources/twig/*.twig') as $template) {
        echo $template.PHP_EOL;
        $resource = new TwigResource($app['twig.loader'], basename($template));
        $am->addResource($resource, 'twig');
    }

    $writer = new LazyAssetWriter(__DIR__.'/Resources/webassets', [], $debug);
    $writer->writeManagerAssets($am);

    return 'all parsed';
})->bind('process-templates');

$app->run();
