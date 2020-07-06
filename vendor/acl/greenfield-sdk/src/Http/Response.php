<?php
namespace Acl\GreenField\Http;

use Acl\GreenField\Http\Error;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenField API Parsed Response
 *
 * The Parsed response parses the Raw Response and populates the class properties
 * with the parsed response data.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Response implements ResponseInterface
{
    /**
     * Raw response object
     *
     * @var \Acl\GreenField\Http\RawResponse
     */
    protected $raw;

    /**
     * Response data
     *
     * @var object
     */
    protected $data;

    /**
     * Response meta data
     *
     * @var object
     */
    protected $meta;

    /**
     * Errors
     *
     * @var array<\Acl\GreenField\Http\Error>
     */
    protected $errors = [];

    /**
     * Magic Get
     *
     * Throws an exception if requested property does not exist.
     *
     * @param string $prop Property name
     * @return string
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function __get($prop)
    {
        if (property_exists($this, $prop) === false) {
            throw new GreenFieldSDKException(
                "Requested property ({$prop}) does not exist."
            );
        }
        return $this->{$prop};
    }

    /**
     * {@inheritDoc}
     */
    public function parse(RawResponse $rawResp)
    {
        $this->raw = $rawResp;

        $rawBody = $rawResp->getBody();
        $body = json_decode($rawBody);
        if (($decodeErr = json_last_error()) !== JSON_ERROR_NONE) {
            throw new GreenFieldSDKException(
                "Error occured while trying to decode response body. ({$decodeErr})"
            );
        }

        $this->data = isset($body->data) ? $body->data : null;
        $this->meta = isset($body->meta) ? $body->meta : null;

        if (isset($body->error) !== false) {
            $errors = is_array($body->error) ? $body->error : [$body->error];
            foreach ($errors as $error) {
                $errObj = new Error;
                $errObj->code = isset($error->code) ? $error->code : "";
                $errObj->message = isset($error->message) ? $error->message : "";
                $errObj->details = isset($error->extendedMessage) ? $error->extendedMessage : "";
                $this->errors[] = $errObj;
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRawResponse()
    {
        return $this->raw;
    }
}
