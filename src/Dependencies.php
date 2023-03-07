<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use GAState\Web\Slim\Env                                    as Env;
use GAState\Web\Slim\SlimAppFactory                         as DefaultAppFactory;
use GAState\Web\Slim\SlimAppFactoryInterface                as AppFactory;
use GAState\Web\Slim\Cache\AppCacheFactoryInterface         as AppCacheFactory;
use GAState\Web\Slim\Cache\FileAppCacheFactory              as DefaultAppCacheFactory;
use GAState\Web\Slim\Emitter\LaminasResponseEmitter         as DefaultResponseEmitter;
use GAState\Web\Slim\Emitter\ResponseEmitterInterface       as ResponseEmitter;
use GAState\Web\Slim\Error\ErrorHandler                     as DefaultErrorHandler;
use GAState\Web\Slim\Error\ShutdownHandler                  as ShutdownHandler;
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
use PDO;

return (function () {
    /**
     * Environment variables
     *
     * @var array<string,mixed> $envVars
     */
    $envVars = [
        'appCacheDir' => Env::getString(Env::APP_CACHE_DIR),
        'appDir' => Env::getString(Env::APP_DIR),
        'baseDir' => Env::getString(Env::BASE_DIR),
        'baseURI' => Env::getString(Env::BASE_URI),
        'logDir' => Env::getString(Env::LOG_DIR),
        'logName' => Env::getString(Env::LOG_NAME),
        'logFile' => Env::getString(Env::LOG_FILE),
        'logLevel' => Env::getString(Env::LOG_LEVEL),
        'pdoDSN' => Env::getString(Env::PDO_DSN),
        'pdoOptions' => [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ],
        'pdoPassword' => Env::getString(Env::PDO_PASSWORD),
        'pdoUsername' => Env::getString(Env::PDO_USERNAME),
        'serverOptions' => Env::getValues(Env::SERVER_PREFIX),
        'sessionOptions' => array_change_key_case(Env::getValues(Env::SESSION_PREFIX)),
        'slimDir' => Env::getString(Env::SLIM_DIR),
        'slimDisplayErrorDetails' => Env::getBool(Env::SLIM_DISP_ERR_DETAILS),
        'slimLogErrors' => Env::getBool(Env::SLIM_LOG_ERR),
        'slimLogErrorDetails' => Env::getBool(Env::SLIM_LOG_ERR_DETAILS),
        'slimTemplateDir' => Env::getString(Env::SLIM_TMPL_DIR),
        'templateCacheDir' => Env::getString(Env::TMPL_CACHE_DIR),
        'templateDir' => Env::getString(Env::TMPL_DIR),
        'twigPaths' => [
            \DI\get('templateDir'),
            \DI\get('slimTemplateDir')
        ],
        'twigOptions' => array_merge(
            ['cache' => \DI\get('templateCacheDir')],
            array_change_key_case(Env::getValues(Env::TWIG_PREFIX))
        ),
    ];

    /**
     * Application dependencies
     *
     * @var array<string,mixed> $appDeps
     */
    $appDeps = [
        AppCacheFactory::class => \DI\autowire(DefaultAppCacheFactory::class)
            ->constructorParameter('appCacheDir', \DI\get('appCacheDir')),
        AppFactory::class => \DI\autowire(DefaultAppFactory::class)
            ->constructorParameter('baseURI', \DI\get('baseURI')),
        AppSession::class => \DI\autowire(DefaultAppSession::class)
            ->constructorParameter('options', \DI\get('sessionOptions'))
            ->constructorParameter('deferStart', true)
            ->constructorParameter('deferEnd', false),
        DefaultErrorMiddleware::class => \DI\autowire()
            ->constructorParameter('displayErrorDetails', \DI\get('slimDisplayErrorDetails'))
            ->constructorParameter('logErrors', \DI\get('slimLogErrors'))
            ->constructorParameter('logErrorDetails', \DI\get('slimLogErrorDetails')),
        DefaultTwigFactory::class => \DI\autowire()
            ->constructorParameter('paths', \DI\get('twigPaths'))
            ->constructorParameter('settings', \DI\get('twigOptions')),
        DisplayErrorRenderer::class => \DI\get(DefaultDisplayErrorRenderer::class),
        GlobalRequestFactory::class => \DI\get(DefaultGlobalRequestFactory::class),
        LogErrorRenderer::class => \DI\get(DefaultLogErrorRenderer::class),
        LoggerFactory::class => \DI\autowire(DefaultLoggerFactory::class)
            ->constructorParameter('logName', \DI\get('logName'))
            ->constructorParameter('logFile', \DI\get('logFile'))
            ->constructorParameter('logLevel', \DI\get('logLevel')),
        PDO::class => \DI\autowire()
            ->constructorParameter('dsn', \DI\get('pdoDSN'))
            ->constructorParameter('username', \DI\get('pdoUsername'))
            ->constructorParameter('password', \DI\get('pdoPassword'))
            ->constructorParameter('options', \DI\get('pdoOptions')),
        PsrCache::class => \DI\factory([AppCacheFactory::class, 'createAppCache'])
            ->parameter('namespace', '')
            ->parameter('defaultLifetime', 0),
        PsrHttpClient::class => \DI\get(GuzzleHttpClient::class),
        PsrLogger::class => \DI\factory([LoggerFactory::class, 'createLogger']),
        PsrRequestFactory::class => \DI\get(LaminasRequestFactory::class),
        PsrResponseFactory::class => \DI\get(LaminasResponseFactory::class),
        PsrServerRequest::class => \DI\factory([GlobalRequestFactory::class, 'createRequestFromGlobals'])
            ->parameter('server', $_SERVER)
            ->parameter('query', $_GET)
            ->parameter('body', $_POST)
            ->parameter('cookies', $_COOKIE)
            ->parameter('files', $_FILES),
        PsrServerRequestFactory::class => \DI\get(LaminasServerRequestFactory::class),
        PsrStreamFactory::class => \DI\get(LaminasStreamFactory::class),
        PsrUploadedFileFactory::class => \DI\get(LaminasUploadedFileFactory::class),
        PsrUriFactory::class => \DI\get(LaminasUriFactory::class),
        Renderer::class => \DI\get(DefaultRenderer::class),
        ResponseEmitter::class => \DI\get(DefaultResponseEmitter::class),
        ShutdownHandler::class => \DI\autowire()
            ->constructorParameter('displayErrorDetails', \DI\get('slimDisplayErrorDetails'))
            ->constructorParameter('logErrors', \DI\get('slimLogErrors'))
            ->constructorParameter('logErrorDetails', \DI\get('slimLogErrorDetails')),
        SlimApp::class => \DI\factory([AppFactory::class, 'createSlimApp']),
        SlimErrorHandler::class => \DI\get(DefaultErrorHandler::class),
        SlimErrorMiddleware::class => \DI\get(DefaultErrorMiddleware::class),
        SlimTwigView::class => \DI\factory([DefaultTwigFactory::class, 'create']),
    ];

    return array_merge($envVars, $appDeps);
})();
