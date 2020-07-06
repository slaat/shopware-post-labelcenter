<?php
namespace Acl\GreenField\Http;

use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenField API Response Error
 *
 * The API Response Error class contains all the fields that the GreenField API
 * supplies when an error occurs.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Error
{
    /**
     * Error code
     *
     * @var string
     */
    protected $code;

    /**
     * Message
     *
     * @var string
     */
    protected $message;

    /**
     * Error details
     *
     * @var string
     */
    protected $details;

    /**
     * Magic Set
     *
     * Throws an exception if requested property does not exist.
     *
     * @param string $prop Property name
     * @param string $value Property value
     * @return void
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function __set($prop, $value)
    {
        if (property_exists($this, $prop) === false) {
            throw new GreenFieldSDKException(
                "Requested property ({$prop}) does not exist."
            );
        }
        $this->{$prop} = $value;
    }

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
}
