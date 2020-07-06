<?php
namespace Acl\GreenField\HttpClients;

use GuzzleHttp\Client;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * Http Client Factory
 *
 * Client Factory constructs the appropriate GreenField Http Client object based
 * on user input to GreenField Library constructio nand returns it.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class HttpClientFactory
{
    /**
     * It's a factory. No instantiation allowed.
     */
    private function __construct()
    {
    }

    /**
     * Create Http Client
     *
     * Creates the Http Client based on received data, and returns it. For information
     * on the $handler parameter @see \Acl\GreenField\GreenField::__construct()
     *
     * If an unknown handler is supplied throws an exception.
     *
     * @param mixed $handler Http Client Handler
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public static function create($handler)
    {
        if ($handler instanceof Client) {
            return new GuzzleHttpClient($handler);
        }

        if ($handler === "guzzle") {
            if (class_exists("\\GuzzleHttp\\Client") === false) {
                throw new GreenFieldSDKException(
                    "Guzzle Http Client handler was requested. Class '\\GuzzleHttp\\Client' not available."
                );
            }
            return new GuzzleHttpClient;
        }

        throw new GreenFieldSDKException("Supplied handler is now known");
    }
}
