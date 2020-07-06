<?php
namespace Acl\GreenField\Modules\Shoepping;

use Acl\GreenField\Http\Request;
use Acl\GreenField\Modules\AbstractModule;
use Acl\GreenField\Exceptions\GreenFieldSDKException;
use Acl\GreenField\Modules\Shoepping\Exceptions\ShoeppingResponseException;

/**
 * GreenField Shoepping Module
 *
 * GreenField Shoeeping Module provides methods for communicating with the Shoepping
 * module in the GreenField API.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Shoepping extends AbstractModule
{
    /**
     * @inheritDoc
     */
    protected $moduleUrl = "shoepping/";

    /**
     * Merchant ID
     *
     * @var string
     */
    protected $merchantId = "";

    /**
     * API Key
     *
     * @var string
     */
    protected $apiKey = "";

    /**
     * License string
     *
     * @var string
     */
    protected $license = "";

    /**
     * Shop type
     *
     * @var string
     */
    protected $shopType = "";

    /**
     * Shop version
     *
     * @var string
     */
    protected $shopVersion = "";

    /**
     * Set shop type
     *
     * @param string $shopType Type of shop
     * @return self
     */
    public function setShopType($shopType)
    {
        $this->shopType = $shopType;
        return $this;
    }

    /**
     * Set shop version
     *
     * @param string $shopVersion Shop version string
     * @return self
     */
    public function setShopVersion($shopVersion)
    {
        $this->shopVersion = $shopVersion;
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Authentication data array must contain:
     * * merchantId
     * * apiKey
     * * license
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function authenticate(array $authData, $check = false)
    {
        if (array_key_exists("merchantId", $authData) === false
            || array_key_exists("apiKey", $authData) === false
            || array_key_exists("license", $authData) === false
        ) {
            $this->logger->error(
                "Authentication data is not complete. Missing keys.",
                ["requiredKeys" => ["merchantId", "apiKey", "license"], "providedKeys" => array_keys($authData)]
            );
            throw new GreenFieldSDKException(
                "Authentication data is not complete. Missing keys."
            );
        }
        $this->merchantId = $authData["merchantId"];
        $this->apiKey = $authData["apiKey"];
        $this->license = base64_decode(trim($authData["license"]));

        $check === true ? $this->sanityCheck() : true;
    }

    /**
     * Check authentication data against the API.
     *
     * @return bool
     */
    public function sanityCheck()
    {
        $request = new Request;
        $request->setUrl("sanityCheck");
        $request->setMethod("POST");
        $this->populateMetadata($request);
        $response = $this->send($request);
        return $response->getRawResponse()->code === 200;
    }

    /**
     * Retrieves the orders from the API and returns them as an array of objects.
     *
     * @return array
     *
     * @throws \Acl\GreenField\Modules\Shoepping\Exceptions\ShoeppingResponseException
     */
    public function getOrders()
    {
        $request = new Request;
        $request->setUrl("order");
        $this->populateMetadata($request);
        $response = $this->send($request);
        if ($response->code !== 200) {
            $msg = "Error occured when obtaining order data from the API.";
            $this->logger->error($msg, ["request" => $request, "response" => $response]);
            throw new ShoeppingResponseException($msg);
        }
        return $response->data;
    }

    /**
     * Sets the authorization data to the request.
     *
     * @param \Acl\GreenField\Http\Request $request Request object
     * @return self
     */
    protected function setAuth(Request $request)
    {
        $request->addMetaData([
            "merchantId"    =>  $this->merchantId,
            "apiKey"        =>  $this->apiKey
        ])->addHeader("authorization", $this->license);
        return $this;
    }

    /**
     * Populates the Request objects meta data with all the required information.
     *
     * @param \Acl\GreenField\Http\Request $request Request object
     * @return self
     */
    protected function populateMetadata(Request $request)
    {
        $this->setAuth($request);
        $request->addMetaData([
            "shopType"      =>  $this->shopType,
            "shopVersion"   =>  $this->shopVersion,
        ]);
        if ($this->reqCount !== null && is_callable($this->reqCount)) {
            $request->addMetaData([
                "requestNr" =>  call_user_func_array($this->reqCount, [])
            ]);
        }
        return $this;
    }
}
