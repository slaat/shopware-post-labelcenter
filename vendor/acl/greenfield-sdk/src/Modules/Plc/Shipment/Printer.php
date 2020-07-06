<?php
namespace Acl\GreenField\Modules\Plc\Shipment;

use JsonSerializable;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenField Plc Module - Shipment Label Printer Format
 *
 * Contains the printer format data for the shipping endpoint.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Printer implements JsonSerializable
{
    /**
     * Printer parameters
     *
     * The parameters are set through the magic set method.
     *
     * Available parameters:
     * * paperFormat - default "100x200"
     * * fileFormat - default "pdf"
     * * layout - default "2xA5inA4"
     *
     * @var array
     */
    protected $params = [
        "paperFormat"   =>  "100x200",
        "fileFormat"    =>  "pdf",
        "layout"        =>  "2xA5inA4"
    ];

    /**
     * Parameter mapping
     *
     * Used for mapping when encoding to JSON.
     *
     * @var array
     */
    protected $mapping = [
        "paperFormat"   =>  "labelFormatID",
        "fileFormat"    =>  "languageID",
        "layout"        =>  "paperLayoutID"
    ];

    /**
     * Serialize pritner data for JSON encoding.
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
     * Magic setter to set the printer data to the printer object. If the parameter
     * does not exist in the $params array, an exception is thrown. If the value
     * is not a string, an exception is thrown.
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

        if (is_string($value) === false) {
            throw new GreenFieldSDKException(
                "Received value is not a string: " . var_export($value, true)
            );
        }

        $this->params[$name] = $value;
    }
}
