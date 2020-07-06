<?php
namespace Acl\GreenField\Modules\Plc;

use JsonSerializable;
use Acl\GreenField\Modules\Plc\Shipment\Address;
use Acl\GreenField\Modules\Plc\Shipment\Printer;

/**
 * GreenField Plc Module - Shipping Sub-Module Shipment object
 *
 * Shipment data object easily converted to an array as required by the endpoint.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Shipment implements JsonSerializable
{
    /**
     * Document types
     */
    const DOCTYPE_SHIPPING_LABEL = "SHIPPING_LABEL";
    const DOCTYPE_RETURN_LABEL = "RETURN_LABEL";

    /**
     * Order ID
     *
     * @var int
     */
    protected $orderId;

    /**
     * Shop ID
     *
     * @var int
     */
    protected $shopId;

    /**
     * Weight list
     *
     * @var array
     */
    protected $weightList = [];

    /**
     * Custom data bit
     *
     * Default: false
     *
     * @var bool
     */
    protected $customDataBit = false;

    /**
     * custom description
     *
     *
     * @var string
     */
    protected $customsDescription;


    /**
     * shipper reference 2
     *
     * @var string
     */
    protected $shipperReference;


    /**
     * Delivery Service Third Part ID
     *
     * Default: 10
     *
     * @var int
     */
    protected $deliveryId;

    /**
     * Bill address
     *
     * @var Acl\GreenField\Modules\Plc\Address
     */
    protected $billAddr;

    /**
     * Delivery address
     *
     * @var Acl\GreenField\Modules\Plc\Address
     */
    protected $deliveryAddr;

    /**
     * Printer format
     *
     * @var Acl\GreenField\Modules\Plc\Printer
     */
    protected $printer;

    /**
     * Document type
     *
     * @var string
     */
    protected $docType = self::DOCTYPE_SHIPPING_LABEL;

      /**
     * Customer product
     *
     * @var string
     */
    protected $customerProduct;

    /** Generate Label flag
     *
     * @var string
     */
    protected $generateLabel;

      /** branchKey
     *
     * @var string
     */
    protected $branchKey;


  /** branchKeyType
     *
     * @var string
     */
    protected $branchKeyType;

       /**
     * Features list
     *
     * @var array
     */
    protected $featuresList = [];



    /**
     * Constructor automatically instantiates the printer format object and assigns
     * it to itself. This might change in the future, but is handled as is for now
     * to simplify calling the endpoint and send data that should be handled on
     * the API in the first place.
     */
    public function __construct()
    {
        $this->printer = new Printer;
    }

    /**
     * Return object data as an array ready to be injected into the request on the
     * 'importShipment' endpoint.
     *
     * @return array
     */
    public function asArray()
    {
        $data = [
            "orderId"                       =>  $this->orderId,
            "shopId"                        =>  $this->shopId,
            "colloWeightList"               =>  $this->weightList,
            "ouRecipientAddress"            =>  $this->billAddr,
            "ouShipperAddress"              =>  $this->deliveryAddr,
            "customDataBit1"                =>  $this->customDataBit,
            "customsDescription"            =>  $this->customsDescription,
            "deliveryServiceThirdPartyID"   =>  $this->deliveryId,
            "printer"                       =>  $this->printer,
            "documentType"                  =>  $this->docType,
            "customerProduct"               =>  $this->customerProduct,
            "generateLabel"                 =>  $this->generateLabel,
            "branchKey"                     =>  $this->branchKey,
            "branchKeyType"                 =>  $this->branchKeyType,
            "featuresList"                  =>  $this->featuresList,
            "ouShipperReference2"           =>  $this->shipperReference
        ];

        return array_filter($data, function($v) {
            return $v !== null;
        });
    }

    /**
     * Serialize data for JSON encoding.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->asArray();
    }

    /**
     * Set order ID
     *
     * @param int $orderId
     * @return self
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Shop ID
     *
     * @param int $shopId
     * @return self
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
        return $this;
    }

    /**
     * Add a weight on the list. The first parameter is the weight that will get
     * added to the list. If the second parameter is set to bool(true), then the
     * list will be reset, and only that parameter will be on the list.
     *
     * @param int $weight Weight to be added on the list
     * @param bool $overwrite Overwrite existing values on the list
     * @return self
     */
    public function addWeight($weight, $overwrite = false)
    {
        if ($overwrite === true) {
            $this->weightList = [];
        }
        $this->weightList[] = $weight;
        return $this;
    }

    /**
     * Set custom data bit
     *
     * @param bool $bit Custom data bit
     * @return self
     */
    public function setCustomDataBit($bit)
    {
        $this->customDataBit = $bit;
        return $this;
    }

    /**
     * Set delivery ID
     *
     * @param int $deliveryId
     * @return self
     */
    public function setDeliveryId($deliveryId)
    {
        $this->deliveryId = $deliveryId;
        return $this;
    }

    /**
     * Set billing address
     *
     * @param \Acl\GreenField\Modules\Plc\Shipment\Address $address Address object
     * @return self
     */
    public function setBillAddr(Address $address)
    {
        $this->billAddr = $address;
        return $this;
    }

    /**
     * Set delivery address
     *
     * @param \Acl\GreenField\Modules\Plc\Shipment\Address $address Address object
     * @return self
     */
    public function setShipperAddr(Address $address)
    {
        $this->deliveryAddr = $address;
        return $this;
    }

    /**
     * Set custom description
     *
     * @param string
     * @return self
     */
    public function setCustomsDescription($customsDescription)
    {
        $this->customsDescription = $customsDescription;
        return $this;
    }


    /**
     * set shipper reference 2
     *
     * @param $shipperReference
     * @return $this
     */
    public function setShipperReference($shipperReference)
    {
        $this->shipperReference = $shipperReference;
        return $this;
    }



    /**
     * Set printer
     *
     * @param \Acl\GreenField\Modules\Plc\Shipment\Printer $printer Printer format object
     * @return self
     */
    public function setPrinter(Printer $printer)
    {
        $this->printer = $printer;
        return $this;
    }

    /**
     * Set document type
     *
     * @param string $docType Type of document
     * @return self
     */
    public function setDocType($docType)
    {
        $this->docType = $docType;
        return $this;
    }

    /**
     * Set customer number
     *
     * @param string $customerNumber Type of document
     * @return self
     */
    public function setCustomerProduct($customerProduct)
    {
        $this->customerProduct = $customerProduct;
        return $this;
    }


    /**
     * Set generateLabel flag number
     *
     * @param bool $generateLabel Type of document
     * @return self
     */
    public function setGenerateLabel($generateLabel)
    {
        $this->generateLabel = $generateLabel;
        return $this;
    }

     /**
     * Set customer number
     *
     * @param string $branchKey
     * @return self
     */
    public function setBranchKey($branchKey)
    {
        $this->branchKey = $branchKey;
        return $this;
    }

     /**
     * Set customer number
     *
     * @param string $branchKeyType
     * @return self
     */
    public function setBranchKeyType($branchKeyType)
    {
        $this->branchKeyType = $branchKeyType;
        return $this;
    }

    /**
     * Add a feature on the list. The first parameter is the weight that will get
     * added to the list. If the second parameter is set to bool(true), then the
     * list will be reset, and only that parameter will be on the list.
     *
     * @param array $weight Weight to be added on the list
     * @param bool $overwrite Overwrite existing values on the list
     * @return self
     */
    public function addFeature($feature, $overwrite = false)
    {
        if ($overwrite === true) {
            $this->featuresList = [];
        }
        $this->featuresList[] = $feature;
        return $this;
    }


}
