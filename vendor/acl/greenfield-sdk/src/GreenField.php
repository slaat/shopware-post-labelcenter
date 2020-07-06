<?php
namespace Acl\GreenField;

use Psr\Log\LoggerInterface;
use Acl\GreenField\HttpClients\HttpClientFactory;
use Acl\GreenField\HttpClients\HttpClientInterface;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenField PHP SDK Library
 *
 * The GreenField PHP SDK Library enables simple and unified calls to the GreenField
 * API, handles errors appropriately, and provides standardized responses.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 *
 * @todo: add curl http client
 */
class GreenField
{
    /**
     * Http Client
     *
     * @var \Acl\GreenField\HttpClients\HttpClientInterface
     */
    protected $client;

    /**
     * GreenField API Base URL
     *
     * @var string
     */
    protected $baseUrl = "https://api.shoepping.acl.at/greenfield/restapi/";

    /**
     * Default module
     *
     * @var \Acl\GreenField\Modules\AbstractModule
     */
    protected $defaultModule;

    /**
     * Logger
     *
     * @var \Acl\GreenField\Logger
     */
    protected $logger;

    /**
     * GreenField Modules
     *
     * @var array
     */
    private $modules = [
        "shoepping" =>  [
            "className" =>  "\\Acl\\GreenField\\Modules\\Shoepping\\Shoepping",
            "instance"  =>  null
        ],
        "plc"       =>  [
            "className" =>  "\\Acl\\GreenField\\Modules\\Plc\\Plc",
            "instance"  =>  null
        ]
    ];

    /**
     * Constructor
     *
     * Constructs the Library and loads the http client based on input. The $handler
     * parameter must be set appropriatelly, currently the following are available:
     * * string(curl) **NOTE: not yet available**
     * * string(guzzle)
     * * GuzzleHttp\Client
     * * Acl\GreenField\HttpClients\HttpClientInterface
     *
     * @param mixed $handler Http Client Handler
     * @param string $baseUrl API Base URL, if omitted library default is taken
     * @param string $defaultModule Default GreenField module
     */
    public function __construct($handler, $baseUrl = "", $defaultModule = "")
    {
        if (!$handler instanceof HttpClientInterface) {
            $handler = HttpClientFactory::create($handler);
        }

        $this->client = $handler;
        $this->baseUrl = $baseUrl ?: $this->baseUrl;
        $this->logger = new Logger;

        if ($defaultModule !== "") {
            $this->setModule($defaultModule);
        }
    }

    /**
     * Forwards method calls to the default module.
     *
     * @param string $name Name of the method to call
     * @param array $args Method arguments
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function __call($name, array $args = [])
    {
        if ($this->defaultModule === null) {
            $this->logger->error("Unable to forward call to default module. Default module not loaded.");
            throw new GreenFieldSDKException(
                "Default module is not set, and the called method ({$name}) is not known."
            );
        }

        if (method_exists($this->defaultModule, $name) === false) {
            throw new GreenFieldSDKException(
                "Requested method name ({$name}) does not exist in the default module."
            );
        }

        return call_user_func_array([$this->defaultModule, $name], $args);
    }

    /**
     * Magic Get
     *
     * Retrieves the instance of the requested module, or throws an exception if
     * an invalid module has been requested.
     *
     * @param string $name Name of the module
     * @return \Acl\GreenField\Modules\AbstractModule
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function __get($name)
    {
        if ($name === "") {
            throw new GreenFieldSDKException(
                "No specific module was requested, and the default is not set."
            );
        }
        return $this->loadModule($name);
    }

    /**
     * Set Logger
     *
     * Instantiate the GreenField Logger and use the passed in logger instance that
     * implements the PSR-4 LoggerInterface as the logger in the GreenField Logger
     * wrapper.
     *
     * @param \Psr\Log\LoggerInterface $logger Logger instance
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger->setLogger($logger);
        $this->logger->info("GreenField library logger initialized", ["baseUrl" => $this->baseUrl]);
        return $this;
    }

    /**
     * Set GreenField module
     *
     * Sets the received GreenField module as default.
     *
     * @param string $module Name of the module
     * @return self
     */
    public function setModule($module)
    {
        $this->defaultModule = $this->loadModule($module);

        return $this;
    }

    /**
     * Load Module
     *
     * Loads the module and copies its insstance into the $modules array for simpler
     * re-use later. Throws an exception if the requested module does not exist.
     *
     * @param string $module Name of the module to load
     * @return \Acl\GreenField\Modules\AbstractModule
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    protected function loadModule($module)
    {
        if (array_key_exists($module, $this->modules) === false) {
            $this->logger->error("Requested module doese not exist", ["requested" => $module]);
            throw new GreenFieldSDKException("GreenField Module does not exist.");
        }

        if ($this->modules[$module]["instance"] === null) {
            $this->modules[$module]["instance"] = new $this->modules[$module]["className"](
                $this->client,
                $this->baseUrl,
                $this->logger
            );
        }

        return $this->modules[$module]["instance"];
    }
}
