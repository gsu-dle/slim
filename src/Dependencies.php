<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

// GSU interfaces
use GAState\Web\Slim\Cache\AppCacheFactoryInterface         as AppCacheFactory;
use GAState\Web\Slim\Cache\SessionCacheFactoryInterface     as SessionCacheFactory;
use GAState\Web\Slim\Emitter\ResponseEmitterInterface       as ResponseEmitter;
use GAState\Web\Slim\Log\LoggerFactoryInterface             as LoggerFactory;
use GAState\Web\Slim\Message\RequestFactoryInterface        as RequestFactory;
use GAState\Web\Slim\Renderer\DisplayErrorRendererInterface as DisplayErrorRenderer;
use GAState\Web\Slim\Renderer\LogErrorRendererInterface     as LogErrorRenderer;
use GAState\Web\Slim\Renderer\RendererInterface             as Renderer;
use GAState\Web\Slim\SlimAppFactoryInterface                as SlimAppFactory;
// PSR interfaces
use Psr\Cache\CacheItemPoolInterface               as PsrCache;
use Psr\Http\Client\ClientInterface                as PsrHttpClient;
use Psr\Http\Message\RequestFactoryInterface       as PsrRequestFactory;
use Psr\Http\Message\ResponseFactoryInterface      as PsrResponseFactory;
use Psr\Http\Message\ServerRequestFactoryInterface as PsrServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface        as PsrServerRequest;
use Psr\Http\Message\StreamFactoryInterface        as PsrStreamFactory;
use Psr\Http\Message\UploadedFileFactoryInterface  as PsrUploadedFileFactory;
use Psr\Http\Message\UriFactoryInterface           as PsrUriFactory;
use Psr\Log\LoggerInterface                        as PsrLogger;
// Slim interfaces
use Slim\App                        as SlimApp;
use Slim\Views\Twig                 as SlimTwigView;
use Slim\Handlers\ErrorHandler      as SlimErrorHandler;
use Slim\Middleware\ErrorMiddleware as SlimErrorMiddleware;
// GSU implementations
use GAState\Web\Slim\Cache\CacheFactory             as GsuCacheFactory;
use GAState\Web\Slim\Emitter\LaminasResponseEmitter as GsuResponseEmitter;
use GAState\Web\Slim\Error\ErrorHandler             as GsuErrorHandler;
use GAState\Web\Slim\Error\ShutdownHandler          as GsuShutdownHandler;
use GAState\Web\Slim\Log\MonologLoggerFactory       as GsuLoggerFactory;
use GAState\Web\Slim\Message\LaminasRequestFactory  as GsuRequestFactory;
use GAState\Web\Slim\Middleware\ErrorMiddleware     as GsuErrorMiddleware;
use GAState\Web\Slim\Renderer\TwigRenderer          as GsuRenderer;
use GAState\Web\Slim\Renderer\TwigFactory           as GsuSlimTwigViewFactory;
use GAState\Web\Slim\Env                            as GsuEnv;
use GAState\Web\Slim\Renderer\DisplayErrorRenderer  as GsuDisplayErrorRenderer;
use GAState\Web\Slim\Renderer\LogErrorRenderer      as GsuLogErrorRenderer;
use GAState\Web\Slim\SlimAppFactory                 as GsuSlimAppFactory;
// Third-party implementations
use GuzzleHttp\Client                      as GuzzleHttpClient;
use Laminas\Diactoros\RequestFactory       as LaminasRequestFactory;
use Laminas\Diactoros\ResponseFactory      as LaminasResponseFactory;
use Laminas\Diactoros\ServerRequestFactory as LaminasServerRequestFactory;
use Laminas\Diactoros\StreamFactory        as LaminasStreamFactory;
use Laminas\Diactoros\UploadedFileFactory  as LaminasUploadedFileFactory;
use Laminas\Diactoros\UriFactory           as LaminasUriFactory;

return (function () {
    $sessionCacheName    = GsuEnv::getString(GsuEnv::SESSION_CACHE_NAME);
    $appCacheName        = GsuEnv::getString(GsuEnv::APP_CACHE_NAME);
    $appCacheDir         = GsuEnv::getString(GsuEnv::APP_CACHE_DIR);
    $displayErrorDetails = GsuEnv::getBool(GsuEnv::SLIM_DISP_ERR_DETAILS);
    $logErrors           = GsuEnv::getBool(GsuEnv::SLIM_LOG_ERR);
    $logErrorDetails     = GsuEnv::getBool(GsuEnv::SLIM_LOG_ERR_DETAILS);
    $twigPaths           = [
        GsuEnv::getString(GsuEnv::TMPL_DIR),
        GsuEnv::getString(GsuEnv::SLIM_TMPL_DIR),
    ];
    $twigSettings = array_change_key_case(
        GsuEnv::getValues(
            GsuEnv::TWIG_PREFIX,
            [
                'CACHE' => boolval(GsuEnv::get(GsuEnv::TMPL_CACHE_DIR, false))
            ]
        )
    );

    return [
        // PSR-3 implementation
        LoggerFactory::class => \DI\get(GsuLoggerFactory::class),
        PsrLogger::class     => \DI\factory([LoggerFactory::class, 'createLogger']),

        // PSR-6 implementation
        SessionCacheFactory::class => \DI\get(GsuCacheFactory::class),
        AppCacheFactory::class     => \DI\get(GsuCacheFactory::class),
        PsrCache::class            => \DI\get($sessionCacheName),
        GsuCacheFactory::class     => \DI\autowire()
            ->constructorParameter('sessionCacheName', $sessionCacheName)
            ->constructorParameter('appCacheDir', $appCacheDir),
        $sessionCacheName          => \DI\factory([SessionCacheFactory::class, 'createSessionCache']),
        $appCacheName              => \DI\factory([AppCacheFactory::class, 'createAppCache']),

        // PSR-17 implementation
        PsrRequestFactory::class       => \DI\get(LaminasRequestFactory::class),
        PsrResponseFactory::class      => \DI\get(LaminasResponseFactory::class),
        PsrServerRequestFactory::class => \DI\get(LaminasServerRequestFactory::class),
        PsrStreamFactory::class        => \DI\get(LaminasStreamFactory::class),
        PsrUploadedFileFactory::class  => \DI\get(LaminasUploadedFileFactory::class),
        PsrUriFactory::class           => \DI\get(LaminasUriFactory::class),

        // PSR-18 implementation
        PsrHttpClient::class => \DI\get(GuzzleHttpClient::class),

        // Slim
        SlimErrorHandler::class    => \DI\get(GsuErrorHandler::class),
        SlimErrorMiddleware::class => \DI\get(GsuErrorMiddleware::class),
        SlimAppFactory::class      => \DI\get(GsuSlimAppFactory::class),
        SlimApp::class             => \DI\factory([SlimAppFactory::class, 'createSlimApp']),

        // Twig
        GsuSlimTwigViewFactory::class => \DI\autowire()
            ->constructorParameter('paths', $twigPaths)
            ->constructorParameter('settings', $twigSettings),
        SlimTwigView::class           => \DI\factory([GsuSlimTwigViewFactory::class, 'create']),

        // GSU
        RequestFactory::class       => \DI\get(GsuRequestFactory::class),
        ResponseEmitter::class      => \DI\get(GsuResponseEmitter::class),
        Renderer::class             => \DI\get(GsuRenderer::class),
        DisplayErrorRenderer::class => \DI\get(GsuDisplayErrorRenderer::class),
        LogErrorRenderer::class     => \DI\get(GsuLogErrorRenderer::class),
        GsuErrorMiddleware::class   => \DI\autowire()
            ->constructorParameter('displayErrorDetails', $displayErrorDetails)
            ->constructorParameter('logErrors', $logErrors)
            ->constructorParameter('logErrorDetails', $logErrorDetails),
        GsuShutdownHandler::class   => \DI\autowire()
            ->constructorParameter('displayErrorDetails', $displayErrorDetails)
            ->constructorParameter('logErrors', $logErrors)
            ->constructorParameter('logErrorDetails', $logErrorDetails),
        PsrServerRequest::class     => \DI\factory([RequestFactory::class, 'createRequestFromGlobals']),
    ];
})();
