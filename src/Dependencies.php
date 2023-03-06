<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use GAState\Web\Slim\Env                                    as Env;
use GAState\Web\Slim\SlimAppFactory                         as DefaultSlimAppFactory;
use GAState\Web\Slim\SlimAppFactoryInterface                as SlimAppFactory;
use GAState\Web\Slim\Cache\AppCacheFactoryInterface         as AppCacheFactory;
use GAState\Web\Slim\Cache\FileAppCacheFactory              as DefaultAppCacheFactory;
use GAState\Web\Slim\Emitter\LaminasResponseEmitter         as DefaultResponseEmitter;
use GAState\Web\Slim\Emitter\ResponseEmitterInterface       as ResponseEmitter;
use GAState\Web\Slim\Error\ErrorHandler                     as DefaultErrorHandler;
use GAState\Web\Slim\Error\ShutdownHandler                  as DefaultShutdownHandler;
use GAState\Web\Slim\Log\LoggerFactoryInterface             as LoggerFactory;
use GAState\Web\Slim\Log\FileLoggerFactory                  as DefaultLoggerFactory;
use GAState\Web\Slim\Middleware\ErrorMiddleware             as DefaultErrorMiddleware;
use GAState\Web\Slim\Renderer\DisplayErrorRenderer          as DefaultDisplayErrorRenderer;
use GAState\Web\Slim\Renderer\DisplayErrorRendererInterface as DisplayErrorRenderer;
use GAState\Web\Slim\Renderer\LogErrorRenderer              as DefaultLogErrorRenderer;
use GAState\Web\Slim\Renderer\LogErrorRendererInterface     as LogErrorRenderer;
use GAState\Web\Slim\Renderer\RendererInterface             as Renderer;
use GAState\Web\Slim\Renderer\TwigRenderer                  as DefaultRenderer;
use GAState\Web\Slim\Renderer\TwigFactory                   as DefaultTwigFactory;
use GAState\Web\Slim\Request\LaminasGlobalRequestFactory    as DefaultGlobalRequestFactory;
use GAState\Web\Slim\Request\GlobalRequestFactoryInterface  as GlobalRequestFactory;
use GAState\Web\Slim\Session\AppSession                     as DefaultAppSession;
use GAState\Web\Slim\Session\AppSessionInterface            as AppSession;
use GuzzleHttp\Client                                       as GuzzleHttpClient;
use Psr\Cache\CacheItemPoolInterface                        as PsrCache;
use Psr\Http\Client\ClientInterface                         as PsrHttpClient;
use Psr\Http\Message\RequestFactoryInterface                as PsrRequestFactory;
use Psr\Http\Message\ResponseFactoryInterface               as PsrResponseFactory;
use Psr\Http\Message\ServerRequestFactoryInterface          as PsrServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface                 as PsrServerRequest;
use Psr\Http\Message\StreamFactoryInterface                 as PsrStreamFactory;
use Psr\Http\Message\UploadedFileFactoryInterface           as PsrUploadedFileFactory;
use Psr\Http\Message\UriFactoryInterface                    as PsrUriFactory;
use Psr\Log\LoggerInterface                                 as PsrLogger;
use Slim\App                                                as SlimApp;
use Slim\Handlers\ErrorHandler                              as SlimErrorHandler;
use Slim\Middleware\ErrorMiddleware                         as SlimErrorMiddleware;
use Slim\Views\Twig                                         as SlimTwigView;
use Laminas\Diactoros\RequestFactory                        as LaminasRequestFactory;
use Laminas\Diactoros\ResponseFactory                       as LaminasResponseFactory;
use Laminas\Diactoros\ServerRequestFactory                  as LaminasServerRequestFactory;
use Laminas\Diactoros\StreamFactory                         as LaminasStreamFactory;
use Laminas\Diactoros\UploadedFileFactory                   as LaminasUploadedFileFactory;
use Laminas\Diactoros\UriFactory                            as LaminasUriFactory;

return (function () {
    $baseURI             = Env::getString(Env::BASE_URI);
    $logName             = Env::getString(Env::LOG_NAME);
    $logFile             = Env::getString(Env::LOG_FILE);
    $logLevel            = Env::getString(Env::LOG_LEVEL);
    $appCacheDir         = Env::getString(Env::APP_CACHE_DIR);
    $displayErrorDetails = Env::getBool(Env::SLIM_DISP_ERR_DETAILS);
    $logErrors           = Env::getBool(Env::SLIM_LOG_ERR);
    $logErrorDetails     = Env::getBool(Env::SLIM_LOG_ERR_DETAILS);
    $twigPaths           = [
        Env::getString(Env::TMPL_DIR),
        Env::getString(Env::SLIM_TMPL_DIR),
    ];
    $twigSettings = array_change_key_case(
        Env::getValues(
            Env::TWIG_PREFIX,
            [
                'CACHE' => boolval(Env::get(Env::TMPL_CACHE_DIR, false))
            ]
        )
    );
    $sessionOptions = Env::getValues(Env::SESSION_PREFIX);

    $appDeps = [
        AppCacheFactory::class      => \DI\get(DefaultAppCacheFactory::class),
        AppSession::class           => \DI\get(DefaultAppSession::class),
        DisplayErrorRenderer::class => \DI\get(DefaultDisplayErrorRenderer::class),
        LoggerFactory::class        => \DI\get(DefaultLoggerFactory::class),
        LogErrorRenderer::class     => \DI\get(DefaultLogErrorRenderer::class),
        Renderer::class             => \DI\get(DefaultRenderer::class),
        ResponseEmitter::class      => \DI\get(DefaultResponseEmitter::class),
        GlobalRequestFactory::class => \DI\get(DefaultGlobalRequestFactory::class),
        SlimAppFactory::class       => \DI\get(DefaultSlimAppFactory::class),

        DefaultAppCacheFactory::class   => \DI\autowire()
            ->constructorParameter('appCacheDir', $appCacheDir),
        DefaultAppSession::class        => \DI\autowire()
            ->constructorParameter('options', $sessionOptions)
            ->constructorParameter('deferStart', true)
            ->constructorParameter('deferEnd', false),
        DefaultErrorMiddleware::class   => \DI\autowire()
            ->constructorParameter('displayErrorDetails', $displayErrorDetails)
            ->constructorParameter('logErrors', $logErrors)
            ->constructorParameter('logErrorDetails', $logErrorDetails),
        DefaultLoggerFactory::class     => \DI\autowire()
            ->constructorParameter('logName', $logName)
            ->constructorParameter('logFile', $logFile)
            ->constructorParameter('logLevel', $logLevel),
        DefaultShutdownHandler::class   => \DI\autowire()
            ->constructorParameter('displayErrorDetails', $displayErrorDetails)
            ->constructorParameter('logErrors', $logErrors)
            ->constructorParameter('logErrorDetails', $logErrorDetails),
        DefaultSlimAppFactory::class    => \DI\autowire()
            ->constructorParameter('baseURI', $baseURI),
        DefaultTwigFactory::class       => \DI\autowire()
            ->constructorParameter('paths', $twigPaths)
            ->constructorParameter('settings', $twigSettings),
    ];

    $psrDeps = [
        PsrCache::class                => \DI\factory([AppCacheFactory::class, 'createAppCache'])
            ->parameter('namespace', '')
            ->parameter('defaultLifetime', 0),
        PsrHttpClient::class           => \DI\get(GuzzleHttpClient::class),
        PsrLogger::class               => \DI\factory([LoggerFactory::class, 'createLogger']),
        PsrRequestFactory::class       => \DI\get(LaminasRequestFactory::class),
        PsrResponseFactory::class      => \DI\get(LaminasResponseFactory::class),
        PsrServerRequest::class        => \DI\factory([GlobalRequestFactory::class, 'createRequestFromGlobals'])
            ->parameter('server', $_SERVER)
            ->parameter('query', $_GET)
            ->parameter('body', $_POST)
            ->parameter('cookies', $_COOKIE)
            ->parameter('files', $_FILES),
        PsrServerRequestFactory::class => \DI\get(LaminasServerRequestFactory::class),
        PsrStreamFactory::class        => \DI\get(LaminasStreamFactory::class),
        PsrUploadedFileFactory::class  => \DI\get(LaminasUploadedFileFactory::class),
        PsrUriFactory::class           => \DI\get(LaminasUriFactory::class),
    ];

    $slimDeps = [
        SlimErrorHandler::class    => \DI\get(DefaultErrorHandler::class),
        SlimErrorMiddleware::class => \DI\get(DefaultErrorMiddleware::class),
        SlimApp::class             => \DI\factory([SlimAppFactory::class, 'createSlimApp']),
        SlimTwigView::class        => \DI\factory([DefaultTwigFactory::class, 'create']),
    ];

    return array_merge($appDeps, $psrDeps, $slimDeps);
})();
