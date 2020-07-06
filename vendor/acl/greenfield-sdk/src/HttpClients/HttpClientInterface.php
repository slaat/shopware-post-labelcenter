<?php
namespace Acl\GreenField\HttpClients;

/**
 * Http Client Interface
 *
 * All Http Clients for the ACL GreenField PHP SDK Library must implement this interface
 * and provide the required methods. Modules will use those Http Clients to contact
 * the GreenField API.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
interface HttpClientInterface
{
    /**
     * Set Base Url
     *
     * Sets the Base URL to the Http Client for the requests.
     *
     * @param string $baseUrl Base URL
     * @return self
     */
    public function setBaseUrl($baseUrl);

    /**
     * Sends the request to the provided URL usind the provided HTTP Method, with
     * a body, and any headers defined in the fourth parameter. The fifth parameter
     * '$timeout' may define a timeout for the request, defaults to 30 seconds.
     *
     * It automatically converts the received parameters to a query string for GET
     * and DELETE requests. On POST and PUT Http Method requests, the data is JSON
     * encoded.
     *
     * @param string $url URL to send to, may be absolute or relative to the Base URL
     * @param string $method HTTP Method
     * @param array $params Request parameters to send
     * @param array $headers Array of headers, where key is the header name, default []
     * @param int $timeout Request timeout, default 30
     * @return \Acl\GreenField\Http\RawResponse
     */
    public function send($url, $method, array $params = [], array $headers = [], $timeout = 30);
}
