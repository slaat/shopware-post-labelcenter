<?php

use Shopware\Components\CSRFWhitelistAware;
use PostLabelCenter\Models\ShippingSet;
use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class Shopware_Controllers_Backend_PostShippingConfiguration extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{

    public function loadShippingConfigurationsAction()
    {
        $gf = $this->container->get("post_label_center.greenfield_service");
        $sPremiumDispatchId = $this->Request()->getParam('dispatchID');
        $contractNumberParameter = $this->Request()->getParam('contractNumber');
        if (isset($contractNumberParameter) && $contractNumberParameter != '') {
            $contractNumberParameter = (int)$contractNumberParameter;
        }

        // OLD FASTER VERSION
        // $countriesIso2 = $this->Request()->getParam('countries');
        // $countriesIso2 = json_decode($countriesIso2);
        // $countriesIso2 = array($countriesIso2[0]);

        $countries = $this->getShipmentCountriesIso2ForId($sPremiumDispatchId);
        $countriesIso2 = [];
        foreach ($countries as $country) {
            $countriesIso2[] = $country['countryiso'];
        }

        // if not saved plugin configuration
        if (!$gf->pluginConfigurationExists()) {

            $errors = array(
                'error' => array(
                    'errorMessage' => 'Keine Plugin Konfiguration gefunden!',
                    'errorMessageExtended' => 'Bitte tragen Sie zuerst Post Labels Plugin Einstellungen ein! '
                )
            );
            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }

        $overriddenConfig = false;

        if (isset($contractNumberParameter) && $contractNumberParameter != '') {
            // used shipping set
            try {
                $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\ShippingSet::class);
                $shippingSet = $repository->findOneBy(['sPremiumDispatchID' => $sPremiumDispatchId]);
                $overriddenConfig = $gf->getOverriddenConfig($shippingSet, $contractNumberParameter);
            } catch (\Exception $e) {
                // eventual implement error handling
                return $e;
            }
            if($overriddenConfig) {
                $gf->overrideAuth($overriddenConfig);
            }
        }

        try {
            $allCombinations = $gf->getServices($countriesIso2);
        } catch (GreenFieldResponseException $e) {
            $response = json_decode($e->getResponse()->getBody());
            $errors = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->extendedMessage
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        } catch (GreenFieldSDKException $e) {
            $errors = array(
                'error' => array(
                    'errorMessage' => $e->getMessage(),
                    'errorMessageExtended' => $e->getMessage()
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        } catch (Exception $e) {
            $response = json_decode($e->getResponse()->getBody());
            $errors = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->extendedMessage,
                    // todo - remove it later
                    'errorCodeBackend' => 'Für diese Sendung sind noch keine Tracking Daten verfügbar.'
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }

        // prepare these combinations into array
        $shippingProducts = array();

        foreach ($allCombinations->data as $idx => $shippingProduct) {

            $tmpShippingProduct = array();

            foreach ($shippingProduct->featureList as $key => &$feature) {
                if ($feature->thirdPartyID == '52' || $feature->thirdPartyID == '53') {
                    unset($shippingProduct->featureList[$key]);
                }
            }

            $shippingProduct->featureList = array_values($shippingProduct->featureList);
            $tmpShippingProduct['productID'] = $shippingProduct->thirdPartyID;
            $tmpShippingProduct['sPremiumDispatchID'] = $sPremiumDispatchId;
            $tmpShippingProduct['name'] = $shippingProduct->name;
            $tmpShippingProduct['features'] = $shippingProduct->featureList;
            $tmpShippingProduct['contractNumber'] = $shippingProduct->contract;
            $shippingProducts[] = $tmpShippingProduct;
        }

        $originalShippingProducts = $shippingProducts;

        // check if there are any saved combinations for particular shipping
        $repository = Shopware()->Models()->getRepository(ShippingSet::class);
        // todo: use contract number for filtering
        //$savedProducts = Shopware()->Models()->toArray($repository->findAll());
        $savedProducts = [];
        $savedProducts[] = Shopware()->Models()->toArray($repository->findOneBy(['sPremiumDispatchID' => $sPremiumDispatchId]));
        $responseProducts = array();

        foreach ($shippingProducts as $idx => &$shippingProduct) {
            foreach ($savedProducts as $savedProduct) {

                // for not saved contract we let all products unchecked
                if ($contractNumberParameter && $savedProduct['contractNumber'] != $contractNumberParameter) {
                    continue;
                }

                if ($shippingProduct['productID'] == $savedProduct['productID'] && $shippingProduct['sPremiumDispatchID'] == $savedProduct['sPremiumDispatchID']) {
                    // merge saved configurations into array
                    // checked
                    $shippingProduct['checked'] = $savedProduct['checked'];
                    $savedContractNumber = $savedProduct['contractNumber'];
                    $savedFeatures = json_decode($savedProduct['features']);
                    // checked or new features
                    // compare saved features with ones from webservice response
                    foreach ($shippingProduct['features'] as $feature) {
                        foreach ($savedFeatures as $savedFeature) {

                            // if there are same features (featureid), get cheched value from saved feature and assign it to response feature
                            if ($savedFeature->name == $feature->name && $savedFeature->thirdPartyID == $feature->thirdPartyID) { //
                                $feature->checked = $savedFeature->checked;
                            } //
                        }
                    }
                }
            }
            $responseProducts[] = $shippingProduct;
//            unset($shippingProduct);
        }

        $savedConfiguration = $gf->getPostPluginConfiguration();
        $initialContractNumber = $savedConfiguration->getUnitId();
        $initialIdentifier = $savedConfiguration->getIdentifier();
        $groupedConfigurations = array();

        foreach ($shippingProducts as $shippingProductTmp) {
            $groupedConfigurations[] = $shippingProductTmp;
        }

        if ($savedContractNumber) {
            $savedConf['savedcontractnumber'] = $savedContractNumber ? $savedContractNumber : false;
            $savedConf['products'] = $groupedConfigurations;
        }

        // we have to add saved configuration into response
        $responseConfigurations = array();
        $savedConf['contractnumber'] = $initialContractNumber; // actualy initial contract number
        $savedConf['identifier'] = $initialIdentifier;
        $savedConf['products'] = $savedContractNumber == $initialContractNumber ? $groupedConfigurations : $originalShippingProducts;
        $responseConfigurations[] = $savedConf;

        // all other contract numbers with combinations
        $otherContractNumbers = json_decode($savedConfiguration->getContractNumbers());
        if (isset($otherContractNumbers) && count($otherContractNumbers) > 0) {
            foreach ($otherContractNumbers as $contractNumber) {
                $contractNumber->products = $contractNumber->contractnumber == $savedContractNumber ? $groupedConfigurations : $originalShippingProducts;
                $responseConfigurations[] = (array)$contractNumber;
            }
        }

        $this->View()->assign([
            'success' => true,
            'data' => $responseConfigurations,
            'total' => count($groupedConfigurations)
        ]);
    }

    protected function getShipmentCountriesIso2ForId($dispatchID)
    {
        if (intval($dispatchID)) {

            $sql = "select b.countryiso  from s_premium_dispatch_countries as a 
                	left join s_core_countries b on a.countryID = b.id
                	where a.dispatchID = $dispatchID";
            $query = Shopware()->Container()
                ->get('dbal_connection')
                ->executeQuery($sql);

            return $query->fetchAll();
        }

        return false;
    }

    public function savePostConfigurationAction()
    {
        $params = $this->Request()->getParams();
        $product = json_decode($params['record']);
        $product = $product[0];

        // send configuration check request
        // get checked features for saving and check their combinations compatibility by webservice call
        $featureIDs = array();
        foreach ($product->features as $feature) {
            if ($feature->checked) {
                $featureIDs[] = strval($feature->thirdPartyID);
            }
        }
        $response = array(
            'success' => false,
            'data' => array(
                'saving' => 'failure'
            ),
            'total' => 1
        );

        $this->View()->assign($response);
        $gf = $this->container->get("post_label_center.greenfield_service");

        // no saved configuration - saving not allowed
        if (!$gf->pluginConfigurationExists()) {
            $errors = array(
                'error' => array(
                    'errorMessage' => 'Keine Plugin Konfiguration gefunden!',
                    'errorMessageExtended' => 'Bitte tragen Sie zuert Post Labels Plugin Einstellungen ein! '
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }

        // todo - return exception messages to backend
        try {
            $response = $gf->checkFeatureParams($featureIDs);
        } catch (GreenFieldResponseException $e) {
            $response = json_decode($e->getResponse()->getBody());
            $errors = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->extendedMessage
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        } catch (Exception $e) {
            $errors = array(
                'error' => array(
                    'errorMessage' => $e->getMessage(),
                    'errorMessageExtended' => $e->getMessage()
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }

        $rawResponse = $response->getRawResponse();

        if (!$rawResponse->getResponseCode() == '200') {
            $this->View()->assign($response);
            return;
        }

        //multiple features are not compatible with each other
        if (json_decode($rawResponse->getBody())->data) {
            $response = json_decode($rawResponse->getBody());
            $errors = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->message
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }


        $repository = Shopware()->Models()->getRepository('PostLabelCenter\Models\ShippingSet');
        $existingShippingSet = $repository->findOneBy([
            'sPremiumDispatchID' => (int)$product->sPremiumDispatchID
        ]);

        if ($existingShippingSet) {
            $shippingSet = $existingShippingSet;
        } else {
            $shippingSet = new ShippingSet();
        }

        $savedConfiguration = $gf->getPostPluginConfiguration();
        $initialContractNumber = $savedConfiguration->getUnitId();

        $shippingSet->setSPremiumDispatchID((int)$product->sPremiumDispatchID);
        $shippingSet->setProductID($product->productID);
        $shippingSet->setName($product->name);
        // contractnumber parameter
        $contractNumberParam = isset($params['contractNumber']) ? $params['contractNumber'] : die;
        $contractNumber = $contractNumberParam ? $contractNumberParam : $initialContractNumber;
        $shippingSet->setContractNumber($contractNumber);
        $shippingSet->setImportedDate(new \DateTime());
        $shippingSet->setChanged(new \DateTime());
        $shippingSet->setChecked(true);
        $shippingSet->setFeatures(json_encode($product->features));

        try {
            Shopware()->Models()->persist($shippingSet);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            return;
        }

        // if not, send false to backend & show error message
        // todo set status - set success parameter to true or false
        // in case of false create user message window
        $this->View()->assign([
            'success' => true,
            'data' => array(
                'saving' => 'success'
            ),
            'total' => 1
        ]);
    }

    public function saveShippingSet($data, $shippingSet)
    {
        $data = $this->prepareAssociatedData($data, $shippingSet);
        $shippingSet->fromArray($data);
        Shopware()->Models()->persist($shippingSet);
        Shopware()->Models()->flush();
    }

    public function prepareAssociatedData($data)
    {
        $data['features'] = json_encode($data['features']);
        $data['description'] = 'test';
        $data['importedDate'] = new \DateTime();
        $data['changed'] = new \DateTime();
        return $data;
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'loadShippingConfigurations'
        ];
    }
}
