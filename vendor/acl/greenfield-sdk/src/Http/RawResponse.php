<?php
namespace Acl\GreenField\Http;

use JsonSerializable;

/**
 * GreenField API Raw Response
 *
 * The Raw Response contains the raw response data received from the API splitted
 * into headers, body, and response code.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class RawResponse implements JsonSerializable
{
    /**
     * Headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Body
     *
     * @var string
     */
    protected $body = "";

    /**
     * Response Code
     *
     * @var int
     */
    protected $code = 0;

    /**
     * Constructor
     *
     * Copy the received raw response data to class properties.
     *
     * @param array $headers Response headers
     * @param string $body Response body
     * @param int $code Response status code
     */
    public function __construct(array $headers, $body, $code)
    {
        $this->headers = $headers;
        $this->body = $body;
        $this->code = $code;
    }

    /**
     * Serialize request data for JSON encoding.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            "code"      =>  $this->code,
            "headers"   =>  $this->headers,
            "body"      =>  $this->body
        ];
    }

    /**
     * Return the response headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    /**
     * Return the body of the response.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the body of the response. Neede for some special logging purpose
     *
     * @return string
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Return the HTTP response code.
     *
     * @return int
     */
    public function getResponseCode()
    {
        return $this->code;
    }
}
