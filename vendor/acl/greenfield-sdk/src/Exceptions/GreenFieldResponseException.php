<?php
namespace Acl\GreenField\Exceptions;

use Exception;
use Acl\GreenField\Http\RawResponse;

/**
 * GreenField Response Exception
 *
 * The GreenField Response Exception is thrown when a response is of status 5xx.
 * The Exception contains all the error data, as well as the Raw Response object.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class GreenFieldResponseException extends GreenFieldSDKException
{
    /**
     * Raw Response Object
     *
     * @var \Acl\GreenField\Http\RawResponse
     */
    protected $rawResponse;

    /**
     * Construct the exception and add the raw response to the properties.
     *
     * @param string $message Exception message
     * @param \Acl\GreenField\Http\RawResponse $response Raw response object
     * @param int $code HTTP Response code, default 0
     * @param \Excetpion $previous Previous exception, default null
     */
    public function __construct(
        $message,
        RawResponse $response,
        $code = 0,
        Exception $previous = null
    ) {
        $this->rawResponse = $response;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get Raw Response
     *
     * @return \Acl\GreenField\Http\RawResponse
     */
    public function getResponse()
    {
        return $this->rawResponse;
    }
}
