<?php
namespace Acl\GreenField\HttpClients;

use GuzzleHttp\Client;
use Acl\GreenField\Http\RawResponse;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Ring\Exception\RingException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * Guzzle Http Client
 *
 * GreenField PHP SDK Http Client wrapper for the Guzzle Http Client.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * Guzzle Http Client
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Base Url
     *
     * @var string
     */
    protected $baseUrl = "";

    /**
     * Constructor
     *
     * Copy the Guzzle Http Client to the class properties or instantiate a new
     * instance if the parameter is omitted.
     *
     * @param \GuzzleHttp\Client $client Guzzle Http Client, default null
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client;
    }

    /**
     * Set Base Url
     *
     * Sets the Base URL to the Http Client for the requests.
     *
     * @param string $baseUrl Base URL
     * @return self
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function send($url, $method, array $params = [], array $headers = [], $timeout = 30)
    {
        if ($this->baseUrl !== "") {
            $url = $this->baseUrl . $url;
        }
        $options = [
            "headers"           =>  array_merge(["Accept" => "application/json"], $headers),
            "timeout"           =>  $timeout,
            "connect_timeout"   =>  10
        ];
        switch (strtolower($method)) {
        case "post":
        case "put":
            $options["json"] = $params;
            $options["expect"] = true;
            break;
        default:
            $options["query"] = $params;
        }

        $request = $this->client->createRequest($method, $url, $options);

        try {
            $response = $this->client->send($request);
        } catch (RequestException $e) {
            $response = $e->getResponse();

            if ($e->getPrevious() instanceof RingException || !$response instanceof ResponseInterface) {
                throw new GreenFieldSDKException($e->getMessage(), $e->getCode());
            }
        }

        return new RawResponse(
            $response->getHeaders(),
            (string)$response->getBody(),
            $response->getStatusCode()
        );
    }
}
