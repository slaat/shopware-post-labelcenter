<?php
namespace Acl\GreenField\Modules\Plc\Shipment;

use JsonSerializable;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenField Plc Module - Shipment Address
 *
 * Contains the address data for the shipping endpoint.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Address implements JsonSerializable
{
    /**
     * Address parameters
     *
     * The parameters are set through the magic set method.
     *
     * Available parameters:
     * * email
     * * firstname
     * * lastname
     * * address
     * * additionalAddr
     * * houseNumber
     * * postalCode
     * * city
     * * countryIso3
     *
     * @var array
     */
    protected $params = [
        "email"             =>  "",
        "company"           =>  "",
        "department"        =>  "",
        "firstname"         =>  "",
        "lastname"          =>  "",
        "address"           =>  "",
        "additionalAddr"    =>  "",
        "houseNumber"       =>  "",
        "postalCode"        =>  "",
        "city"              =>  "",
        "countryIso2"       =>  ""
    ];

    /**
     * Parameter mapping
     *
     * Used for mapping when encoding to JSON.
     *
     * @var array
     */
    protected $mapping = [
        "email"             =>  "email",
        "company"           =>  "company",
        "department"        =>  "department",
        "firstname"         =>  "firstname",
        "lastname"          =>  "lastname",
        "address"           =>  "addressLine1",
        "additionalAddr"    =>  "addressLine2",
        "houseNumber"       =>  "houseNumber",
        "postalCode"        =>  "postalCode",
        "city"              =>  "city",
        "countryIso2"       =>  "countryID"
    ];

    /**
     * Serialize address data for JSON encoding.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this->params as $k => $v) {
            $data[$this->mapping[$k]] = $v;
        }
        return $data;
    }

    /**
     * Magic setter to set the address data to the address object. If the parameter
     * does not exist in the $params array, an exception is thrown.
     *
     * @param string $name Paremeter name
     * @param string $value Parameter value
     * @return void
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function __set($name, $value)
    {
        if (isset($this->params[$name]) === false) {
            throw new GreenFieldSDKException(
                "Parameter '{$name}' does not exist in the parameter list."
            );
        }

        $this->params[$name] = $value;
    }
}
