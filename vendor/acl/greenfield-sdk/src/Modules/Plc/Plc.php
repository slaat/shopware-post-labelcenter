<?php
namespace Acl\GreenField\Modules\Plc;

use DateTime;
use Acl\GreenField\Http\Request;
use Acl\GreenField\Http\PdfResponse;
use Acl\GreenField\Modules\AbstractModule;
use Acl\GreenField\Exceptions\GreenFieldSDKException;
use Acl\GreenField\Exceptions\GreenFieldNotFoundException;

/**
 * GreenField Plc Module
 *
 * GreenField Shoeeping Module provides methods for communicating with the Plc module
 * in the GreenField API.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Plc extends AbstractModule
{
    /**
     * @inheritDoc
     */
    protected $moduleUrl = "austrianpost/";

    /**
     * Client ID
     *
     * @var int
     */
    protected $clientId = 0;

    /**
     * Unit ID
     *
     * @var int
     */
    protected $orgUnitId = -1;

    /**
     * Unit GUID
     *
     * @var string
     */
    protected $orgUnitGuid = "";

    /**
     * License string
     *
     * @var string
     */
    protected $license = "";

    /**
     * Overriden authentication data
     *
     * @var array
     */
    protected $overridenAuth = [];

    /**
     * Auth token
     *
     * After authorization, the token is returned and set into this property as
     * well. On consequent requests you should re-set this token with the 'setToken'
     * method.
     *
     * @param int
     */
    protected $token = 0;

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
     * Plugin version
     *
     * @var string
     */
    protected $pluginVersion = "";

    /**
     * Set plugin version
     *
     * @param string $pluginVersion Plugin Version string
     * @return self
     */
    public function setPluginVersion($pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;
        return $this;
    }

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
     * Set authorization token for the requests. To obtain a token, perform an 'authenticate'
     * call.
     *
     * @param int $token Authorization token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Get authorization token.
     *
     * @return int|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set license key
     *
     * @return self
     */
    public function setLicense($license)
    {
        $this->license = $license;
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Authentication data array must contain:
     * * orgUnitID
     * * orgUnitGuID
     * * license
     *
     * Optional:
     * * clientId
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function authenticate(array $authData, $check = false)
    {
        if (array_diff(["orgUnitID", "orgUnitGuID", "license"], array_keys($authData))) {
            $this->logger->error(
                "Authentication data is not complete. Missing keys.",
                ["requiredKeys" => ["orgUnitID", "orgUnitGuID", "license"], "providedKeys" => array_keys($authData)]
            );
            throw new GreenFieldSDKException(
                "Authentication data is not complete. Missing keys."
            );
        }

        $this->orgUnitId = $authData["orgUnitID"];
        $this->orgUnitGuid = $authData["orgUnitGuID"];
        $this->license = base64_decode(trim($authData["license"]));
        $this->clientId = $authData["clientId"];

        if ($check === true) {
            $this->sanityCheck();
        }
        return true;
    }

    /**
     * Override authentication data
     *
     * Overrides authentication data, for the next request only. For a list of available
     * keys in the authentication array @see \Acl\GreenField\Modules\Plc\Plc::authenticate
     *
     * After a call has been made, the overriden authentication data is discarded,
     * and the original auth data is used.
     *
     * @param array $authData Authentication data
     * @param bool $check Automatically run sanity check, default false
     * @return self
     */
    public function overrideAuth(array $authData, $check = false)
    {
        $this->overridenAuth = [
            "orgUnitID"     =>  isset($authData["orgUnitID"]) ? $authData["orgUnitID"] : $this->orgUnitId,
            "orgUnitGuID"   =>  isset($authData["orgUnitGuID"]) ? $authData["orgUnitGuID"] : $this->orgUnitGuid,
            "license"       =>  isset($authData["license"]) ? $authData["license"] : $this->license,
            "clientId"      =>  isset($authData["clientId"]) ? $authData["clientId"] : $this->clientId
        ];

        return $this;
    }

/*******************************************************************************
 * PING                                                                        *
 *******************************************************************************/

    /**
     * Sanity check
     *
     * Check authentication data against the API. On error, an exception is thrown.
     *
     * @return \Acl\GreenField\Http\Response
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function sanityCheck()
    {
        $request = new Request;
        $request->setUrl("sanityCheck");
        $request->setMethod("POST");
        $this->populateMetadata($request);
        $response = $this->send($request);
        if ($response->getRawResponse()->getResponseCode() !== 200) {
            throw new GreenFieldSDKException("Error occured while trying to authenticate against the API.");
        }
        return $response;
    }

/*******************************************************************************
 * /PING                                                                       *
 *******************************************************************************/

/*******************************************************************************
 * BRANCH                                                                      *
 *******************************************************************************/

    /**
     * Retrieves branch data from the API for the passed in post code and type.
     *
     * @param int $postcode Postal code
     * @param int $type Branch type
     * @return \Acl\GreenField\Http\Response
     */
    public function getActiveBranches($postcode, $type)
    {
        $request = new Request;
        $request->setUrl("branch/getActiveBranches");
        $request->setMethod("POST");
        $request->addData([
            "country"           =>  "AT",
            "distance"          =>  5000,
           // "maxRows"           =>  100,
            "postalCode"        =>  $postcode,
            "type"              =>  $type
        ]);
        $this->populateMetadata($request);

        return $this->send($request);
    }

/*******************************************************************************
 * /BRANCH                                                                     *
 *******************************************************************************/

/*******************************************************************************
 * DELIVERY OPTIONS                                                            *
 *******************************************************************************/

    /**
     * Get services for specified country
     *
     * @param array $countries ISO2 country names as array
     * @return \Acl\GreenField\Http\Response
     */
    public function getServices(array $countries)
    {
        $request = new Request;
        $request->setUrl("deliveryOptions/services/byCountry");
        $request->setMethod("POST");
        $request->addData($countries);
        $this->populateMetadata($request);

        return $this->send($request);
    }

    /**
     * Call the check params API endpoint and return the results.
     *
     * @param array $delOpts Delivery options array
     * @return \Acl\GreenField\Http\Response
     */
    public function checkParams($delOpts)
    {
        $request = new Request;
        $request->setUrl("deliveryOptions/features/checkCombination");
        $request->setMethod("POST");
        $request->addData($delOpts);
        $this->populateMetadata($request);

        return $this->send($request);
    }

/*******************************************************************************
 * /DELIVERY OPTIONS                                                           *
 *******************************************************************************/

/*******************************************************************************
 * LABEL                                                                       *
 *******************************************************************************/

    /**
     * Get all documents for the specified order number from the specified shop.
     *
     * @param string $orderId Shop order number
     * @param int $shopId Shop identifier
     * @return \Acl\GreenField\Http\Response
     */
    public function getOrderDocs($order, $shopId)
    {
        $request = new Request;
        $request->setUrl("label/findAll");
        $request->setMethod("POST");
        $request->addData([
            "orderId"   =>  $order,
            "shopId"    =>  $shopId
        ]);
        $this->populateMetadata($request);

        return $this->send($request);
    }

    /**
     * Load the requested document for the specified Shop ID from the API.
     *
     * @param int $docId Document ID
     * @param string $orderId Shop order number
     * @param int $shipId Shop identifier
     * @return \Acl\GreenField\Http\Response
     */
    public function loadDoc($docId, $orderId, $shopId)
    {
        $request = new Request;
        $request->setUrl("label/loadPdf");
        $request->setMethod("POST");
        $request->setData([
            "documentId"    =>  $docId,
            "orderId"       =>  $orderId,
            "shopId"        =>  $shopId
        ]);
        $this->setAuth($request);
        $request->skipMetaData();

        return $this->send($request);
    }

/*******************************************************************************
 * /LABEL                                                                      *
 *******************************************************************************/

/*******************************************************************************
 * SHIPPING                                                                    *
 *******************************************************************************/

    /**
     * Perform end of day
     *
     * Sends a request to the API to perform the end-of-day calculations, generate
     * the PDF, and returns it base64 encoded. This method returns the parsed API
     * response on success, and throws an exception on error. If a DateTime object
     * is provided as the input, then a search is performed on the API with that
     * given date.
     *
     * @param \DateTime $date Search date, default null
     * @return \Acl\GreenField\Http\Response
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function performEndOfDay(DateTime $date = null)
    {
        $request = new Request;
        if ($date !== null) {
            $request->setUrl("shipping/performEndOfDay/search");
            $request->setData([
                "date"  =>  $date->format("Y-m-d")
            ]);
        } else {
            $request->setUrl("shipping/performEndOfDay");
            $request->addHeader("Accept", "application/pdf");
        }
        $request->setMethod("POST");
        $this->populateMetadata($request);

        return $this->send($request);
    }

    /**
     * Call the 'pdf/generate' API endpoint with the provided shipment data in
     * the '\Acl\GreenField\Modules\Plc\Shipment' object. Throws an exception on
     * error.
     *
     * @param \Acl\GreenField\Modules\Plc\Shipment $shipment Shipment data object
     * @return \Acl\GreenField\Http\Response
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function generateShippingPdf(Shipment $shipment)
    {
        $request = new Request;
        $request->setUrl("shipping/pdf/generate");
        $request->setMethod("POST");
        $request->setData($shipment->asArray());
        $this->populateMetadata($request);
        return $this->send($request);
    }


    /**
     * Call the 'pdf/generate' API endpoint with the provided shipment data in
     * the '\Acl\GreenField\Modules\Plc\Shipment' object. Throws an exception on
     * error.
     *
     * @param $documentIds array
     * @return \Acl\GreenField\Http\Response
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function cancelShipment($documentIds)
    {
        $request = new Request;
        $request->setUrl("shipping/cancelShipment");
        $request->setMethod("POST");
        $request->setData($documentIds);
        $this->populateMetadata($request);
        return $this->send($request);
    }


    /**
     * List all PDFs for the given Order ID and Shop ID.
     *
     * @param string $orderId Order ID
     * @param string $shopID Shop ID
     * @return \Acl\GreenField\Http\Response
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function listPdfs($orderId, $shopId)
    {
        return $this->fetchPdf([
            "orderId"   =>  $orderId,
            "shopId"    =>  $shopId
        ]);
    }

    /**
     * Load specific PDF based on the received PDF/Document ID.
     *
     * @param int $pdfId PDF/Document ID
     * @return object
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function loadPdf($pdfId, $orderId = '', $shopId = '')
    {
        $response = $this->fetchPdf([
		"documentId"    =>  $pdfId,
		"orderId"    =>  $orderId,
   		"shopId"    =>  $shopId
        ]);

        if (count($response->data) === 0) {
            throw new GreenFieldNotFoundException("Requested PDF was not found.");
        }

        return $response->data[0];
    }

    /**
     * Call the load PDF API endpoint with the provided array payload.
     *
     * @param array $payload API Call data payload
     * @return \Acl\GreenField\Http\Response
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    protected function fetchPdf(array $payload)
    {
        $request = new Request;
        $request->setUrl("shipping/pdf/load");
        $request->setMethod("POST");
        $request->setData($payload);
        $this->populateMetadata($request);
        return $this->send($request);
    }

/*******************************************************************************
 * /SHIPPING                                                                   *
 *******************************************************************************/

/*******************************************************************************
 * TRACKING                                                                    *
 *******************************************************************************/

    /**
     * Get tracking parcel detail info from the API
     *
     * @param string $orderId Parsel Tracking ID
     * @return \Acl\GreenField\Http\Response
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function getParcelDetail($trackingId)
    {
        $request = new Request;
        $request->setUrl("tracking/getParcelDetail");
        $request->setMethod("POST");
        $request->setData([
            "identCode"         =>  $trackingId,
            "debitorNumber"     =>  "0025000120",
            "withDescription"   =>  true,
            "fromInsertDate"    =>  " "
        ]);
        $this->populateMetadata($request);
        return $this->send($request);
    }

/*******************************************************************************
 * /TRACKING                                                                   *
 *******************************************************************************/

    /**
     * Set auth
     *
     * Sets the authorization data to the request.
     *
     * @param \Acl\GreenField\Http\Request $request Request object
     * @return self
     */
    protected function setAuth(Request $request)
    {
        if (empty($this->token) === false) {
            $request->addMetaData(["token" => $this->token]);
        } else {
            if ($this->overridenAuth === []) {
                $request->addMetaData([
                    "orgUnitGuID"   =>  $this->orgUnitGuid,
                    "orgUnitID"     =>  $this->orgUnitId,
                    "clientId"      =>  $this->clientId
                ]);
            } else {
                $request->addMetaData([
                    "orgUnitGuID"   =>  $this->overridenAuth["orgUnitGuID"],
                    "orgUnitID"     =>  $this->overridenAuth["orgUnitID"],
                    "clientId"      =>  $this->overridenAuth["clientId"]
                ]);
            }
        }
        $request->addHeader(
            "authorization",
            isset($this->overridenAuth["license"])
                ? $this->overridenAuth["license"]
                : $this->license
        );
        $this->overridenAuth = [];
        return $this;
    }

    /**
     * Populate meta data
     *
     * Populates the Request objects meta data with all the required information.
     *
     * @param \Acl\GreenField\Http\Request $request Request object
     * @param bool $skipAuth Skip adding authentication data to the meta data, default false
     * @return self
     */
    protected function populateMetadata(Request $request, $skipAuth = false)
    {
        if ($skipAuth === false) {
            $this->setAuth($request);
        }
        $request->addMetaData([
            "shopType"      =>  $this->shopType,
            "shopVersion"   =>  $this->shopVersion,
            "pluginVersion" =>  $this->pluginVersion
        ]);
        if ($this->reqCount !== null && is_callable($this->reqCount)) {
            $request->addMetaData([
                "requestNr" =>  call_user_func_array($this->reqCount, [])
            ]);
        }
        return $this;
    }
}
