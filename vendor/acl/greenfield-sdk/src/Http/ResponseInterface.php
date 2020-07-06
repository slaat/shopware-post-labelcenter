<?php
namespace Acl\GreenField\Http;

/**
 * GreenField API Parsed Response Interface
 *
 * Parsed response classes of the GreenField Library must implement this interface
 * in order to be valid Response classes.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
interface ResponseInterface
{
    /**
     * Parse Response
     *
     * Parses the raw response and sets the parsed data to the class properties.
     * Retuns an instance of itself. If the response can not be parsed, an exception
     * is thrown.
     *
     * @param \Acl\GreenField\Http\RawResponse $rawResp Raw response object
     * @return self
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function parse(RawResponse $rawResp);

    /**
     * Returns the Raw Response object.
     *
     * @return \Acl\GreenField\Http\RawResponse
     */
    public function getRawResponse();
}
