<?php

namespace Acl\GreenField\Modules;

use Acl\GreenField\Logger;
use Acl\GreenField\Http\Request;
use Acl\GreenField\Http\Response;
use Acl\GreenField\Http\RawResponse;
use Acl\GreenField\Http\PdfResponse;
use Acl\GreenField\HttpClients\HttpClientInterface;
use Acl\GreenField\Exceptions\GreenFieldSDKException;
use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldAuthenticationException;

/**
 * Abstract GreenField Module
 *
 * Every module class from the PHP SDK class must exctend from this abstract class
 * in order to be considered a valid GreenField Module. The AbstractModule provides
 * a unified constructor for all modules, and handles Http Client setup.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
abstract class AbstractModule
{
    /**
     * Http Client
     *
     * @var \Acl\GreenField\HttpClients\HttpClientInterface
     */
    protected $client;

    /**
     * Module Base Url
     *
     * @var string
     */
    protected $moduleUrl;

    /**
     * Logger instance
     *
     * @var \Acl\GreenField\Logger
     */
    protected $logger;

    /**
     * Request count callback function, if set the request will contain the "requestNr"
     * parameter.
     *
     * @var callable
     */
    protected $reqCount;

    /**
     * Last response received from the API
     *
     * @var \Acl\GreenField\Http\Response
     */
    protected $lastResponse;

    /**
     * Copies the instance of the HttpClient to the properties, and sets the base
     * URL in the client.
     *
     * @param \Acl\GreenField\HttpClients\HttpClientInterface $client Http Client Instance
     * @param string $baseUrl Base URL
     * @param \Acl\GreenField\Logger $logger Logger instance
     */
    public function __construct(HttpClientInterface $client, $baseUrl, Logger $logger)
    {
        $this->client = $client;
        $this->moduleUrl = $baseUrl . $this->moduleUrl;
        $this->logger = $logger;
        $this->client->setBaseUrl($this->moduleUrl);

        $this->logger->info("Module initialized", ["name" => get_class($this)]);
    }

    /**
     * The Request Count Callback is called each time a new request is being made.
     * It is defined as a callable because the library does not maintain the request
     * count. This is the job of the caller of the library. The Request count callback
     * return value is parsed as an integer.
     *
     * If the passed in parameter is not callable, an exception is thrown.
     *
     * @param callable $reqCount Request count callback
     * @return void
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function setReqCountCallback($reqCount)
    {
        if (is_callable($reqCount) === false) {
            $this->logger->error("Received request count callback is not callable.");
            throw new GreenFieldSDKException(
                "Received request count callback is not callable."
            );
        }

        $this->reqCount = $reqCount;
    }

    /**
     * Returns the last response. If no request was yet made, null is returned.
     *
     * @return \Acl\GreenField\Http\Response|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Store authentication data in the module, and executes the sanity check to
     * ensure authentication data is ok, if the second paramter '$check' is set
     * to true. A bool status is returned indicating correctness of authentication
     * data. If the second parameter '$check' is not set to true or is omitted,
     * return status is always true.
     *
     * Each module can require different kind of authentication data, thus the method
     * accepts the authentication data as an array in the first parameter.
     *
     * @param array $authData Authentication data
     * @param bool $check Automatically run sanity check, default false
     * @return bool
     */
    abstract public function authenticate(array $authData, $check = false);

    /**
     * Sends the request to the API using the Client instance from the $client property.
     * Parses the received RawResponse, and returns the Parsed response. If an error
     * occurs while parsing the response in the Response object and and exception
     * is thrown, that exception is caught here, logged, and re-thrown.
     *
     * @param \Acl\GreenField\Http\Request $request Request object
     * @return \Acl\GreenField\Http\ResponseInterface
     *
     * @throws Acl\GreenField\Exceptions\GreenFieldResponseException If the response
     * code is 5xx.
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException If an error occurs
     * during transmition of the request, or parsing the response.
     */
    protected function send(Request $request)
    {
        $this->logger->debug("Sending request to API", ["request" => $request]);

        $origResp = $this->client->send(
            $request->getUrl(),
            $request->getMethod(),
            $request->render(),
            $request->getHeaders()
        );


        $rawResp = clone $origResp;

        // beacuse of the size we should not write pdf content into log file

        try {

            $tmpBody = $rawResp->getBody();
            $decodedBody = json_decode($tmpBody);

            $pdfFileContent = false;

            if (is_array($decodedBody->data)) {

                $decodedData = array();

                foreach ($decodedBody->data as &$dataEntry) {

                    if ($dataEntry->fileContent) {
                        $dataEntry->fileContent = '';

                    }
                    $decodedData[] = $dataEntry;

                }
                $decodedBody->data = $decodedData;
                $encodedBody = json_encode($decodedBody);
                $rawResp->setBody($encodedBody);

            } else {
                if ($decodedBody->data->pdfData) {
                    $pdfData = $decodedBody->data->pdfData;
                }

                if ($decodedBody->data->fileContent) {
                    $pdfData = $decodedBody->data->fileContent;
                    $pdfFileContent = true;

                }

                if ($pdfData && strlen(str_replace(' ', '', $pdfData)) > 0) {

                    if ($pdfFileContent) {
                        $decodedBody->data->fileContent = '';
                    } else {
                        $decodedBody->data->pdfData = '';
                    }

                    $encodedBody = json_encode($decodedBody);
                    $rawResp->setBody($encodedBody);
                }

            }


        } catch (Exception $e) {
            throw $e;
        }


        $this->logger->debug("Received response from API", ["response" => $rawResp]);

        $code = $rawResp->getResponseCode();
        switch (true) {
            case $code === 401:
                $msg = "Authentication error occurred while communicating with the API";
                $this->logger->error($msg, ["response" => $rawResp]);
                throw new GreenFieldAuthenticationException($msg, $rawResp, $code);
            case $code >= 500:
                $msg = "API call ended in a non-recoverable error.";
                $this->logger->error($msg, ["response" => $rawResp]);
                throw new GreenFieldResponseException($msg, $rawResp, $code);
            case $code >= 400:
                $msg = "API call ended in an error.";
                $this->logger->notice($msg, ["reponse" => $rawResp]);
                throw new GreenFieldResponseException($msg, $rawResp, $code);
        }

        try {
            return $this->parseRawResponse($origResp);
        } catch (GreenFieldSDKException $e) {
            $this->logger->error(
                "Error occured when parsing RawResponse.",
                ["exception" => $e->getMessage(), "rawResponse" => $rawResp, "request" => $request]
            );
            throw $e;
        }
    }

    /**
     * Parses the Raw Response from the API and returns the parsed response object.
     *
     * @param \Acl\GreenField\Http\RawResponse $rawResp Raw response object
     * @return \Acl\GreenField\Http\ResponseInterface
     */
    protected function parseRawResponse(RawResponse $rawResp)
    {
        $headers = $rawResp->getHeaders();
        $contentType = empty($headers["Content-Type"]) === false
            ? explode(";", $headers["Content-Type"][0])[0]
            : "";

        switch ($contentType) {
            case "application/pdf":
                $this->lastResponse = new PdfResponse;
                break;
            default:
                $this->lastResponse = new Response;
        }

        return $this->lastResponse->parse($rawResp);
    }
}
