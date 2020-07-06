<?php
namespace Acl\GreenField\Http;

use JsonSerializable;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenField Request
 *
 * The GreenField Request contains the data that will be used for the request, and
 * is a dependency in the HttpClientInterface. From this class the reuest data is
 * read when a request is being sent to the API.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Request implements JsonSerializable
{
    /**
     * URL
     *
     * @var string
     */
    protected $url = "";

    /**
     * HTTP Method
     *
     * @var string
     */
    protected $method = "GET";

    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Meta data
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Skip metadata
     *
     * @var bool
     */
    protected $skipMeta = false;

    /**
     * Data key name
     *
     * @var string
     */
    protected $dataKey = "data";

    /**
     * Serialize request data for JSON encoding.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            "url"       =>  $this->url,
            "method"    =>  $this->method,
            "payload"   =>  $this->render(),
            "headers"   =>  $this->headers
        ];
    }

    /**
     * Set Request URL
     *
     * @param string $url URL for the request
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get Request URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set Request Method
     *
     * Check the Request method is valid and sets it to the properties.
     *
     * @param string $method HTTP Request method
     * @return self
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        $allowed = ["GET", "POST", "PUT", "DELETE"];
        if (in_array($method, $allowed) === false) {
            throw new GreenFieldSDKException(
                "Provided method ({$method}) is not permitted. Allowed methods: " . implode(",", $allowed)
            );
        }
        $this->method = $method;
        return $this;
    }

    /**
     * Get Request Method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Add data
     *
     * Adds more data to the data array.
     *
     * @param array $data Data array
     * @return self
     */
    public function addData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Set data
     *
     * Instead of adding to the data array, this sets the data to the received array
     * effectively overwritting any existing data.
     *
     * @param array $data Data array
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Add meta data
     *
     * Adds more meta data to the meta data array.
     *
     * @param array $meta Meta meta array
     * @return self
     */
    public function addMetaData(array $meta)
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Set meta data
     *
     * Instead of adding to the meta data array, this sets the meta data to the received array
     * effectively overwritting any existing meta data.
     *
     * @param array $meta Meta meta array
     * @return self
     */
    public function setMetaData(array $meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Add header
     *
     * Adds a header to the header array
     *
     * @param string $name Header name
     * @param string $value Header value
     * @return self
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get headers
     *
     * Returns the headers array
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Skip meta data
     *
     * By default meta data is rendered into the request, if this method is called
     * with no parameter, or true, meta data will be ignored when rendering this
     * request.
     *
     * @param bool $skip Skip meta data, default true
     * @return self
     */
    public function skipMetaData($skip = true)
    {
        $this->skipMeta = $skip;
        return $this;
    }

    /**
     * Set data key
     *
     * Sets the key under which the data array will be rendered in the request.
     * If an empty string is provded, then the data array will not be sent under
     * a key in a JSON object, but rather be rendered as an array.
     *
     * @param string $key Data key name
     * @return self
     */
    public function setDataKey($key)
    {
        $this->dataKey = $key;
        return $this;
    }

    /**
     * Render request
     *
     * Renders the request by combining data and meta in one array and returns it.
     * If 'skipMetaData' was set then meta data will be skipped from the request.
     *
     * @return array
     */
    public function render()
    {
        $request = [];
        if (in_array($this->method, ["GET", "DELETE"])) {
            $request = $this->skipMeta ? $this->data : array_merge($this->data, $this->meta);
            return $request;
        }

        if ($this->dataKey !== "") {
            // "special case"
            if (is_array($this->data)) {
                 $request[$this->dataKey] = $this->data;
            } else {
                $request[$this->dataKey] = $this->data ? $this->data : (object)$this->data;
            }

        } else {
            $request = $this->data;
        }

        if ($this->skipMeta === false) {
            $request["meta"] = $this->meta;
        }

        return $request;
    }
}
