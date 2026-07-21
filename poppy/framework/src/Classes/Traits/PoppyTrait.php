<?php

declare(strict_types = 1);

namespace Poppy\Framework\Classes\Traits;

use Illuminate\Auth\AuthManager;
use Illuminate\Cache\TaggableStore;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Redis\RedisManager;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Illuminate\View\Factory;
use Poppy\Framework\Foundation\Application;
use Poppy\Framework\Parse\Ini;
use Poppy\Framework\Parse\Xml;
use Poppy\Framework\Parse\Yaml;
use Poppy\Framework\Poppy\Poppy;
use Poppy\Framework\Translation\Translator;
use Psr\Log\LoggerInterface;

/**
 * PoppyTrait
 *
 * @see app
 */
trait PoppyTrait
{
    /**
     * get auth
     */
    protected function pyAuth(): AuthManager
    {
        return py_container()->make('auth');
    }

    /**
     * get translator
     */
    protected function pyTranslator(): Translator
    {
        return py_container()->make('translator');
    }

    /**
     * Get configuration instance.
     *
     * @return Repository
     */
    protected function pyConfig()
    {
        return py_container()->make('config');
    }

    /**
     * get db
     */
    protected function pyDb(): DatabaseManager
    {
        return py_container()->make('db');
    }

    /**
     * Get console instance.
     *
     * @return Kernel
     */
    protected function pyConsole()
    {
        return py_container()->make(Kernel::class);
    }

    /**
     * Get IoC Container.
     *
     * @return Container|Application
     */
    protected function pyContainer(): Container
    {
        return Container::getInstance();
    }

    /**
     * Get mailer instance.
     */
    protected function pyMailer(): Mailer
    {
        return py_container()->make('mailer');
    }

    /**
     * Get session instance.
     *
     * @return SessionManager|Store
     */
    protected function pySession()
    {
        return py_container()->make('session');
    }

    /**
     * get request
     */
    protected function pyRequest(): Request
    {
        return py_container()->make('request');
    }

    /**
     * get redirector
     */
    protected function pyRedirector(): Redirector
    {
        return py_container()->make('redirect');
    }

    /**
     * get validation
     */
    protected function pyValidation(): \Illuminate\Validation\Factory
    {
        return py_container()->make('validator');
    }

    /**
     * get event
     */
    protected function pyEvent(): Dispatcher
    {
        return py_container()->make('events');
    }

    /**
     * get logger
     */
    protected function pyLogger(): LoggerInterface
    {
        return py_container()->make('log');
    }

    /**
     * get response
     *
     * @return ResponseFactory
     */
    protected function pyResponse()
    {
        return py_container()->make(ResponseFactory::class);
    }

    /**
     * get file
     *
     * @return Filesystem
     */
    protected function pyFile()
    {
        return py_container()->make('files');
    }

    /**
     * get url
     *
     * @return UrlGenerator
     */
    protected function pyUrl()
    {
        return py_container()->make('url');
    }

    /**
     * get cache
     *
     * @param string $tag tag
     */
    protected function pyCache($tag = '')
    {
        $cache = py_container()->make('cache');
        if ($tag && $cache->getStore() instanceof TaggableStore) {
            return $cache->tags($tag);
        }

        return $cache;
    }

    /**
     * get redis
     */
    protected function pyRedis(): RedisManager
    {
        return py_container()->make('redis');
    }

    /**
     * get view
     */
    protected function pyView(): Factory
    {
        return py_container()->make('view');
    }

    /**
     * get poppy
     */
    protected function pyPoppy(): Poppy
    {
        return py_container()->make('poppy');
    }

    /**
     * Ini Parser
     */
    protected function pyIni(): Ini
    {
        return py_container()->make('poppy.ini');
    }

    /**
     * Ini Parser
     */
    protected function pyXml(): Xml
    {
        return py_container()->make('poppy.xml');
    }

    /**
     * Yaml Parser
     */
    protected function pyYaml(): Yaml
    {
        return py_container()->make('poppy.yaml');
    }
}
