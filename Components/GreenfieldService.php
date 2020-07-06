<?php

namespace PostLabelCenter\Components;

use PostLabelCenter\Models\ShippingSet;
use Shopware\Models\Order\Order;
use Symfony\Component\Validator\Constraints\DateTime;
use WebDriver\Exception;
use Zend_Cache_Core;
use Acl\GreenField\GreenField;
use Shopware\Components\Logger;
use Shopware\Components\HttpClient\GuzzleFactory;
use PostLabelCenter\Models\PostLabelPluginConfiguration;
use Shopware\Components\Plugin\DBALConfigReader as ConfigReader;
use Acl\GreenField\Exceptions\GreenFieldAuthenticationException;
use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;
use Acl\GreenField\Modules\Plc\Shipment;
use Acl\GreenField\Modules\Plc\Shipment\Address;
use Acl\GreenField\Modules\Plc\Shipment\Printer;

class GreenfieldService
{
    /**
     * Plugin logger
     *
     * @var \Shopware\Components\Logger
     */
    protected $logger;

    /**
     * Guzzle client factory
     *
     * @var \Shopware\Components\HttpClient\GuzzleFactory
     */
    protected $clientFactory;

    /**
     * Session handler
     *
     * @var \Zend_Cache_Core
     */
    protected $cache;

    /**
     * Greenfield Library instance
     *
     * @var \Acl\GreenField\GreenField
     */
    protected $gf;

    /**
     * Config instance
     *
     * @var array
     */
    protected $config;

    /**
     * Authenticated
     *
     * @var bool
     */
    protected $authenticated = false;

    /**
     * Auth token
     *
     * @var int
     */
    protected $authToken = 0;

    protected $imageMapping = [
//      'AN' => '',
        'AV' => '',
        'IV' => '',
        'IZ' => '',
        'ZU' => ''
    ];



    public function __construct(
        Logger $logger,
        Zend_Cache_Core $cache,
        GuzzleFactory $clientFactory
    )
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->clientFactory = $clientFactory;
        if ( $this->loadConfig(true) ) {
            $this->initLib();
        }
    }


    // TODO: skippingCheck Should not be needed ...
    protected function loadConfig($skipCheck = false)
    {
        $configuration = $this->getFullConfig();

        try {
            $configurationArray = Shopware()->Models()->toArray($configuration);
            if (is_array($configurationArray) === false || ($config = reset($configurationArray)) === false) {
                // @todo: handle config error
                return false;
            }
        } catch (\Exception $e) {
            if (!$skipCheck) return false;
        }

        $this->setConfig($configuration);

        return true;
    }
    public function getFullConfig()
    {
        $repository = Shopware()->Models()->getRepository(PostLabelPluginConfiguration::class);
        $fullConfig = $repository->findAll();
        if (!$fullConfig || empty($fullConfig) || !isset($fullConfig[0])) {
            return false;
        }
        $fullConfig = $fullConfig[0];
        return $fullConfig;
    }

    protected function initLib()
    {
        $this->gf = new GreenField(
            $this->clientFactory->createClient(),
            $this->config["apiUrl"],
            "plc"
        );

        //Shopware::VERSION consant is removed since shopware 5.6
        if(defined('\Shopware::VERSION')) {
            $shopwareVersion = \Shopware::VERSION;
        } else {
            $shopwareVersion = Shopware()->Container()->getParameter('shopware.release.version');
        }

        $this->gf->setLogger($this->logger);
        $this->gf->setShopType("shopware"); // hardcoded, duh
        $this->gf->setShopVersion($shopwareVersion);
        $this->gf->setPluginVersion("1.1.3"); //TODO GET VERSION FROM services.xml
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(PostLabelPluginConfiguration $config)
    {
        $this->config = [
            "apiUrl" => $config->getApiUrl(),
            "orgUnitID" => (int)$config->getUnitID(),
            "license" => $config->getLicense(),
            "orgUnitGuID" => $config->getUnitGUID(),
            "clientId" => $config->getClientID()
        ];

        return $this;
    }

    public function importShipment($orderID = null, $isReturnlabel = false, $customsDescription = false)
    {
        $order = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderID);

        if (!$order) {
            // todo: what?
            exit;
        }

        $billing = $order->getBilling();
        $customer = $billing->getCustomer();
        $shipping = $order->getShipping();
        $dispatch = $order->getDispatch();
        $dispatchId = $dispatch->getId();

        // used shipping set
        try {
            $repository = Shopware()->Models()->getRepository(ShippingSet::class);
            $shippingSet = $repository->findOneBy(['sPremiumDispatchID' => $dispatchId]);

            if (!$shippingSet) {
                return new \Exception('Keine gespeicherte Konfiguration gefunden für Ihre Versandart ');
            }

            $features = $shippingSet->getFeatures();
            $features = json_decode($features);

            // compare initial user contactnumber (orgUnitGuID) with saved contractnumber in shipping set
            // eventualy use the one from shippingset

            $fullConfig = $this->getFullConfig();

            if (!$fullConfig) {
                return new \Exception('Keine Plugin Konfiguration gefunden!');
            }
            $overriddenConfig = $this->getOverriddenConfig($shippingSet);

            if($overriddenConfig) {
                $this->overrideAuth($overriddenConfig);

            }
        } catch (\Exception $e) {
            // eventual implement error handling
            return $e;
        }

        $shipment = new Shipment();
        foreach ($features as $feature) {

            if ($feature->checked) {
                $tmpFeature = array(
                    'name' => $feature->name,
                    'thirdPartyID' => $feature->thirdPartyID
                );
                // nachnahme
                if ($feature->thirdPartyID == '006' || $feature->thirdPartyID == '022') {
                    $tmpFeature = array_merge($tmpFeature, $this->getAdditionalImportInfo($order));
                    //63 - Höherversicherung
                } else if ($feature->thirdPartyID == '063') {
                    $higherInsurance = $this->getHigherInsurance($order->getId());

                    $higherInsuranceFeature = ["value1" => $higherInsurance, "value2" => "EUR"];
                    $tmpFeature = array_merge($tmpFeature, $higherInsuranceFeature);

                }
                $shipment->addFeature($tmpFeature);

            }
        }



        $orderAttributes = $this->getOrderAttributes($order->getId());
        $isBranchDelivery = false;

        if ($branch = $orderAttributes['branch']) {
            $isBranchDelivery = true;
            try {
                $branchObject = json_decode($branch);
                if (!$branchObject->additionalBranchData->branchKey || !$branchObject->additionalBranchData->type) {
                    // todo return exception
                    throw new \Exception('No branch data in shop order');
                }
                $shipment->setBranchKey($branchObject->additionalBranchData->branchKey);
                $shipment->setBranchKeyType($branchObject->additionalBranchData->type);

            } catch (\Exception $e) {
                // todo implement exception handling
                die($e->getMessage());
            }
        }

        $bill = new Address();
        $bill->email = $customer->getEmail();
        $isShipping = false;
        if($shipping->getStreet() && $shipping->getZipCode() && $shipping->getCity()) {
            $isShipping = true;
        }


        $firstName = $isShipping ? $shipping->getFirstName() : $billing->getFirstName();
        $lastName = $isShipping ? $shipping->getLastName() : $billing->getLastName();
        $company = $isShipping ? $shipping->getCompany() : $billing->getCompany();
        $department = $isShipping ? $shipping->getDepartment() : $billing->getDepartment();
        $bill->firstname = $isBranchDelivery ? $shipping->getDepartment() : $firstName;
        $bill->lastname = $isBranchDelivery ? $billing->getFirstName() . ' ' . $billing->getLastName() : $lastName;
        $bill->company = $isBranchDelivery ? '' : $company;
        $bill->department = $department;
        $address = $isShipping ? $shipping->getStreet() : $billing->getStreet();
        $postalCode = $isShipping ? $shipping->getZipCode() : $billing->getZipCode();
        $city =  $isShipping ? $shipping->getCity() : $billing->getCity();

        $bill->address = $address;
        $bill->houseNumber = '';
        $bill->postalCode = $postalCode;
        $bill->city = $city;
        $bill->countryIso2 = $isShipping ? $shipping->getCountry()->getIso() : $billing->getCountry()->getIso();
        $bill->additionalAddr = $isBranchDelivery ? $shipping->getAdditionalAddressLine1() : '';

        $pluginConfig = $this->getFullConfig();
        $infoName = $pluginConfig->getInfoName();
        $infoNameExtended = $pluginConfig->getInfoNameExtended();
        $infoPhone = $pluginConfig->getInfoPhone();
        $infoStreet = $pluginConfig->getInfoStreet();
        $infoZip = $pluginConfig->getInfoZip();
        $infoCity = $pluginConfig->getInfoCity();
        //$infoCountry = $pluginConfig->getInfoCountry();
        $infoCountry = 'AT';

        $shipper = new Address();
        $shipper->email = Shopware()->Config()->get('mail') ?: '';//$customer->getEmail();
        $shipper->company = $infoName;
        $shipper->department = $infoNameExtended;
//      $shipper->lastname = $infoName;
        $shipper->address = $infoStreet;
        //$shipper->houseNumber = '';
        $shipper->postalCode = $infoZip;
        $shipper->city = $infoCity;
        $shipper->countryIso2 = $infoCountry;
        // fill shipment info
        $shipment->setDeliveryId(strval($shippingSet->getProductID()));
        $shipment->setBillAddr($bill);
        $shipment->setShipperAddr($shipper);
        $shipment->setOrderId($order->getNumber());


        $shipment->setShipperReference($this->getShipperReference($order->getId()));

        //46 = ems international
        if (strval($shippingSet->getProductID() == 46)) {
            if(!$customsDescription && $customsDescription !== "") {
                $customsDescription = $this->getCustomsDescription($order->getId());
            }

            $emsErrors = false;
            if ($customsDescription) {
                $shipment->setCustomsDescription($customsDescription);
            } else {
                $error = [
                    'errorMessage' => 'Warnung! EMS Inhaltsbeschreibung fehlt.',
                    'errorMessageExtended' => 'Inhaltsbeschreibung ist für EMS International erforderlich. Bitte geben Sie die EMS Inhaltsbeschreibung in der Bestelldetailübersicht ein.'
                ];
                $emsErrors['isMessage'] = true;
                $emsErrors['error'][] = $error;

                $this->logger->error(
                    "Error ",
                    ["msg" => $error]
                );

            }
            if (!$billing->getPhone() || $billing->getPhone() === '') {
                $error = [
                    'errorMessage' => 'Warnung! Telefonnummer des Empfängers fehlt.',
                    'errorMessageExtended' => 'Telefonnummer des Empfängers ist für EMS International erforderlich. Bitte geben Sie die Telefonnummer des Empfängers in dessen Lieferadresse an oder wählen Sie eine andere Versandart.'
                ];

                $emsErrors['isMessage'] = true;
                $emsErrors['error'][] = $error;

                $this->logger->error(
                    "Error ",
                    ["msg" => $error]
                );

            }

            if ($emsErrors) {

                return $emsErrors;
            }
        }


        // add sum of product weights to shipment
        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Article\Detail::class);
        $products = [];

        $counter = 0;
        $firstProduct = null;

        foreach ($order->getDetails() as $detail) {

            if ($detail->getMode() !== 0) {
                continue;
            }
            $variant = $repository->findOneBy(['number' => $detail->getArticleNumber()]);

            $products[$detail->getArticleNumber()]["variant"] = $variant;
            $products[$detail->getArticleNumber()]["quantity"] = $detail->getQuantity();
            if($counter === 0) {
                $firstProduct = $variant->getArticle();
            }
            $counter += 1;
        }

        $weight = 0;
        foreach ($products as $product) {

            // find parent price
            if ($product["variant"]->getWeight() <= 0) {
                $mainDetail = $product["variant"]->getArticle()->getMainDetail();
                $weight += $mainDetail->getWeight() * $product["quantity"];
                continue;
            }
            $weight += $product["variant"]->getWeight() * $product["quantity"];
        }

        if($weight == 0) {
            $weight = 0.1;
        }



        //add phone numbers  and article description to features
        if($firstProduct) {
            $articleDescription = (object)["name" => "articleDescription", "value1" => strip_tags($firstProduct->getName())];
            $shipment->addFeature($articleDescription);
        }
        $shipperPhone = (object)["name" => "shipperPhone", "value1" => $infoPhone];
        $recipentPhone = (object)["name" => "recipientPhone", "value1" =>  $billing->getPhone()];

        $shipment->addFeature($shipperPhone);
        $shipment->addFeature($recipentPhone);
        $shipment->addWeight((float)$weight);

        // id for webservice is actual our ordernumber
        $shopID = $order->getShop()->getId();
        $shipment->setShopId($shopID);

        // just for tracking purpose
        $shipment->setCustomerProduct($this->getCustomerProduct());

        if ($isReturnlabel) {
            $shipment->setDocType(Shipment::DOCTYPE_RETURN_LABEL);
        }

        $shipment->setGenerateLabel(true);

        if ($this->isDataImportOnly()) {
            $shipment->setGenerateLabel(false);
        } else {
            $printer = new Printer();
            $labelFormat = $pluginConfig->getPaperLayout();
            if ($labelFormat) {
                $printer->layout = $labelFormat;
            }
            $shipment->setPrinter($printer);
        }
        // send request
        $response = $this->__call("generateShippingPdf", [$shipment]);

        return $response;
    }

    //gets customsdescription attribute from the db instead of the service (service saves too late)
    protected function getCustomsDescription($orderId)
    {
        if (intval($orderId)) {
            $sql = "select customsdescription  from s_order_attributes
                	where orderID = $orderId";
            $query = Shopware()->Container()
                ->get('dbal_connection')
                ->executeQuery($sql);

            return $query->fetchColumn();
        }

        return false;
    }

    //gets higher_insurance attribute from the db instead of the service (service saves too late)
    protected function getHigherInsurance($orderId)
    {
        if (intval($orderId)) {
            $sql = "select higher_insurance  from s_order_attributes
                	where orderID = $orderId";
            $query = Shopware()->Container()
                ->get('dbal_connection')
                ->executeQuery($sql);

            return $query->fetchColumn();
        }

        return false;
    }

    //gets shipper_reference_2 attribute from the db instead of the service (service saves too late)
    protected function getShipperReference($orderId)
    {
        if (intval($orderId)) {
            $sql = "select shipper_reference_2  from s_order_attributes
                	where orderID = $orderId";
            $query = Shopware()->Container()
                ->get('dbal_connection')
                ->executeQuery($sql);

            return $query->fetchColumn();
        }

        return false;
    }

    public function getOrderAttributes($orderId)
    {
        $attributeLoader = Shopware()->Container()->get('shopware_attribute.data_loader');
        $orderAttributes = $attributeLoader->load('s_order_attributes', $orderId);

        return $orderAttributes;
    }

    public function getCustomerProduct()
    {
        //Shopware::VERSION consant is removed since shopware 5.6
        if(defined('\Shopware::VERSION')) {
            $shopwareVersion = \Shopware::VERSION;
        } else {
            $shopwareVersion = Shopware()->Container()->getParameter('shopware.release.version');
        }

        return "Shopware " . $shopwareVersion;
    }

    public function isDataImportOnly()
    {
        if (!$fullConfig = $this->getFullConfig()) {
            return false;
        }
        return $fullConfig->isDataImportOnly() == true;
    }

    public function __call($name, array $params)
    {
        if ($this->gf === null) {
            $this->initLib();
        }

        if ($this->authenticated === false) {
            $this->auth();
        }

        $response = call_user_func_array([$this->gf, $name], $params);
        return $response;
    }

    protected function auth()
    {
        $config = $this->config;

        unset($config["apiUrl"]);
        $this->authenticated = $this->gf->authenticate($config);
    }

    public function cancelShipment($documentIds = null)
    {
        $response = $this->__call("cancelShipment", [$documentIds]);

        return $response;
    }

    public function getTrackingData($orderNumber = null, $trackingCode = null)
    {
        // todo - order check for codes realy necesary?
        if (!$trackingCode) {
            return array('noTrackingCode' => true);
        }

        try {
            $trackingArray = $this->getTrackingStatus($trackingCode);
        } catch (GreenFieldResponseException $e) {

            $this->logger->error(
                "Error fetching tracking status from the API",
                ["msg" => $e->getMessage()]
            );

            $response = json_decode($e->getResponse()->getBody());

            // special case - do trackign data for order
            if (!$response || strpos($response->error->extendedMessage,
                    'Es konnten keine Daten für Ihre Abfrage gefunden werden.') !== false) {
                return array('noTrackingData' => true);
            }
            return $response;

        } catch (GreenFieldSDKException $e) {
            $this->logger->error(
                "Error fetching tracking status from the API",
                ["msg" => $e->getMessage()]
            );

            return array('noTrackingData' => true);

        } catch (\Exception $e) {
            $this->logger->error(
                "Error fetching tracking status from the API",
                ["msg" => $e->getMessage()]
            );

            return array('noTrackingData' => true);
        }

        $path = 'custom/plugins/PostLabelCenter/Resources/views/frontend/_public/src/img/';
        $this->imageMapping = [
            'AV' => $path . 'posticon-paket-sendungsuebernahme.png',
            'IV' => $path . 'posticon-lkw.png',
            'IZ' => $path . 'posticon-paket-zustellung.png',
            'ZU' => $path . 'posticon-checkbox-checked.png',
        ];

        foreach ($trackingArray->parcels as &$package) {
            foreach ($package->parcelEvents as &$packageEvent) {
                $dateTime = new \DateTime();
                $dateTime->setTimestamp($packageEvent->eventTimestamp / 1000);
                $date = $dateTime->format('d.m.Y H:i:s');
                $packageEvent->aclFormateDate = $date;
                // add icons
                if (isset($this->imageMapping[$packageEvent->trackingState])) {
                    $packageEvent->icon = $this->imageMapping[$packageEvent->trackingState];
                }
            }
        }

        return $trackingArray;
    }

    public function getTrackingStatus($trackingCode)
    {
        $response = $this->__call("getParcelDetail", [$trackingCode]);

        return $response->data;
    }

    public function checkFeatureParams($featureIDs)
    {
        $response = $this->__call("checkParams", array($featureIDs));

        return $response;
    }

    public function pluginConfigurationExists()
    {
        return (bool)$this->getPostPluginConfiguration();
    }

    public function getPostPluginConfiguration()
    {
        $repository = Shopware()->Models()->getRepository(PostLabelPluginConfiguration::class);
        $configurations = $repository->findAll();

        // todo - change later if multiple instances allowed
        return $configurations[0];
    }

    public function markOrderAsDownloaded($orderId, $documentId, $pdfDocument)
    {
        // save view into DB - usability
        $orderAttributes = $this->getOrderAttributes($orderId);
        if (!$labelsDownloadsList = $orderAttributes['post_labels_downloads']) {
            $labelsDownloadsList = '[]';
        }

        $labelsDownloadsList = json_decode($labelsDownloadsList);


        foreach ($labelsDownloadsList as &$labelDownload) {
            if ($labelDownload->documentid == $documentId) {
                $labelDownload->label_data->downloaded == true ? $documentDownloaded = true : $documentDownloaded = false;
                if (!$documentDownloaded) {
                    $labelDownload->label_data->downloaded = true;
                    $orderAttributes['post_labels_downloads'] = json_encode($labelsDownloadsList);
                    $attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');

                    try {
                        $attributePersister->persist($orderAttributes, 's_order_attributes', $orderId);
                        Shopware()->Models()->flush();
                    } catch (\Exception $e) {
                        $this->View()->assign([
                            'data' => $e->getMessage(),
                            'success' => false,
                        ]);

                        return;
                    }
                }
                break;
            }
        }
    }

    public function getOrderTrackingCodes($orderId)
    {
        $repository = Shopware()->Models()->getRepository(Order::class);
        if (!$order = $repository->findOneBy(['number' => $orderId])) {
            return false;
        }

        if (!$trackingCode = $order->getTrackingCode()) {
            return false;
        }

        $trackingCodes = explode(';', $trackingCode);

        return $trackingCodes;
    }

    public function isOrderReturnAllowed()
    {
        if (!$fullConfig = $this->getFullConfig()) {
            return false;
        }

        return $fullConfig->isReturnOrderAllowed() == true;
    }

    public function getReturnReasons()
    {
        if (!$fullConfig = $this->getFullConfig()) {
            return false;
        }

        return $fullConfig->getReturnReasons();
    }

    public function getReturnTimeMax()
    {
        if (!$fullConfig = $this->getFullConfig()) {
            return false;
        }

        return $fullConfig->getReturnTimeMax();
    }

    public function getAdditionalImportInfo ($order)
    {

        if (!$fullConfig = $this->getFullConfig()) {
            return [];
        }

        $additional = [
            'value1' =>  strval($order->getInvoiceAmount()),
            'value2' =>  'EUR',
            'value3' =>  $fullConfig->getAccountIban() . '|' . $fullConfig->getBankBic() . '|' .  $fullConfig->getBankAccountOwner(),
            'value4' => ''
        ];

       return $additional;
    }

    public function getOverriddenConfig($shippingSet, $contractNumber = false)
    {
        $fullConfig = $this->getFullConfig();

        if (!$fullConfig || !$shippingSet) {
            return false;
        }

        $shippingContractNumber = $shippingSet->getContractNumber();
        $additionalContracts = json_decode($fullConfig->getContractNumbers());
        $newImportConfig = false;

        if ($contractNumber) {
            foreach ($additionalContracts as $contract) {
                if ($contract->contractnumber == (int)$contractNumber) {
                    $newImportConfig = [
                        'orgUnitID' => (int)trim($contract->contractnumber),
                        'orgUnitGuID' => trim($contract->unitGUID),
                        'clientId' => trim($contract->license)
                    ];
                    return $newImportConfig;
                }
            }
            return false;
        }

        if ($fullConfig->getUnitID() != $shippingContractNumber && $shippingContractNumber !='')
        {
            foreach ($additionalContracts as $contract) {
                if ($contract->contractnumber == $shippingContractNumber) {
                    $newImportConfig = [
                        'orgUnitID' => (int)trim($contract->contractnumber),
                        'orgUnitGuID' => trim($contract->unitGUID),
                        'clientId' => trim($contract->license)
                    ];
                   return $newImportConfig;
                }
            }
        }

        return false;
    }

    public function getLogger()
    {
        return $this->logger;
    }

}
