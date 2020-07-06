<?php

use Shopware\Components\CSRFWhitelistAware;
use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class Shopware_Controllers_Backend_PostPluginConfiguration extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{


    const LICENSE = "QmFzaWMgWVhWemRISnBZVzVRYjNOMFRtOWtaVHBzUVhaS1FVZFFabVJIVjBzdFFYRmZRalJIT1d0dVluZzVNMHRXWWxOYVFRPT0=";

    public function getPluginConfigurationAction()
    {
        $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\PostLabelPluginConfiguration::class);
        $configuration = $repository->findAll();
        $data = Shopware()->Models()->toArray($configuration);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ]);
    }

    // todo: currently single instance
    public function savePluginConfigurationAction()
    {
        $unitID = $this->Request()->getParam('unitID');

        if (isset($unitID)) {
            $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\PostLabelPluginConfiguration::class);

            // should be max one saved configuration
            $allSavedConfigurations = $repository->findAll();
            $savedConfiguration = $repository->findOneBy(['unitID' => $unitID]);

            if ($savedConfiguration) {
                $pluginConfiguration = $savedConfiguration;
            } else {
                // we have one saved but under different guid
                if ($allSavedConfigurations) {
                    $pluginConfiguration = $allSavedConfigurations[0];
                } else {
                    $pluginConfiguration = new \PostLabelCenter\Models\PostLabelPluginConfiguration();
                }
            }
        } else {
            $this->View()->assign([
                "success" => false,
                "error" => 'OrgUnitId is missing'
            ]);
            return;
        }

        $unitID = $this->Request()->getParam('unitID');
        $unitGUID = $this->Request()->getParam('unitGUID');
        $returnTimeMax = $this->Request()->getParam('returnTimeMax');
        $returnReasons = $this->Request()->getParam('returnReasons');
        $clientID = $this->Request()->getParam('clientID');
        $identifier = $this->Request()->getParam('identifier');
        $paperLayout = $this->Request()->getParam('paperLayout');
        $dataImportOnly = $this->Request()->getParam('dataImportOnly') == 'on' ? 1 : 0;
        $returnOrderAllowed = $this->Request()->getParam('returnOrderAllowed') == 'on' ? 1 : 0;
        $infoName = $this->Request()->getParam('infoName');
        $infoNameExtended = $this->Request()->getParam('infoNameExtended');
        $infoPhone = $this->Request()->getParam('infoPhone');
        $infoStreet = $this->Request()->getParam('infoStreet');
        $infoZip = $this->Request()->getParam('infoZip');
        $infoCity = $this->Request()->getParam('infoCity');
        $infoCountry = $this->Request()->getParam('infoCountry');

        $apiURL = $this->Request()->getParam('apiURL');

        //setting the license for the sanity check
        $license = self::LICENSE;
        $_GET['license'] = $license;

        // todo - validate fields above
        $pluginConfiguration->setUnitID($unitID);
        $pluginConfiguration->setUnitGUID($unitGUID);
        $pluginConfiguration->setLicense($license);
        $pluginConfiguration->setReturnTimeMax($returnTimeMax);
        $pluginConfiguration->setReturnReasons($returnReasons);
        $pluginConfiguration->setClientID($clientID);
        $pluginConfiguration->setApiUrl($apiURL);
        $pluginConfiguration->setIdentifier($identifier);
        $pluginConfiguration->setPaperLayout($paperLayout);
        $pluginConfiguration->setDataImportOnly($dataImportOnly);
        $pluginConfiguration->setReturnOrderAllowed($returnOrderAllowed);
        $pluginConfiguration->setInfoName($infoName);
        $pluginConfiguration->setInfoNameExtended($infoNameExtended);
        $pluginConfiguration->setInfoPhone($infoPhone);
        $pluginConfiguration->setInfoStreet($infoStreet);
        $pluginConfiguration->setInfoZip($infoZip);
        $pluginConfiguration->setInfoCity($infoCity);
        $pluginConfiguration->setInfoCountry($infoCountry);


        try {

            Shopware()->Models()->persist($pluginConfiguration);
            Shopware()->Models()->flush();

            $service = $this->container
                ->get("post_label_center.greenfield_service");
            $service->setConfig($pluginConfiguration);
            $service->sanityCheck();

        } catch (Exception $e) {
            $this->View()->assign([
                "success" => false,
                "error" => strval($e->getMessage())
            ]);
            return;
        }

        $this->View()->assign([
            'success' => true
        ]);
    }

    public function getPluginConfigurationContractsAction()
    {
        $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\PostLabelPluginConfiguration::class);
        $configurations = $repository->findAll();

        $this->View()->assign([
            'success' => false,
            'data' => [],
            'total' => 0
        ]);

        if (!$configurations) {
            return;
        }

        $configurations = $configurations[0];

        if (!$existingContracts = $configurations->getContractNumbers()) {
            return;
        }

        $contractNumbers = json_decode($existingContracts);

        $this->View()->assign([
            'success' => true,
            'data' => $contractNumbers,
            'total' => count($contractNumbers)
        ]);
    }


    public function saveContractNumberAction()
    {
        // get parameter
        $contractNumber = $this->Request()->getParam('contractNumber');
        $unitGUID = $this->Request()->getParam('unitGUID');
        $license = $this->Request()->getParam('license');
        $identifier = $this->Request()->getParam('identifier');

        try {
            $this->container
                ->get("post_label_center.greenfield_service")
                ->overrideAuth(
                    [
                        "orgUnitID" => $contractNumber,
                        "orgUnitGuID" => $unitGUID,
                        "clientId" => $license
                    ]
                )->sanityCheck();

            $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\PostLabelPluginConfiguration::class);
            $configurations = $repository->findAll();
            $returnResult = array(
                'success' => false,
                'data' => [],
                'total' => 0
            );

            // current version support 1 configuration#
            if (!$configurations) {
                $returnResult['data'] = $errors = array(
                    'error' => array(
                        'errorMessage' => 'No saved configuration found',
                        'errorMessageExtended' => ''
                    )
                );
                $this->View()->assign($returnResult);
                return;
            }

            $configuration = $configurations[0];

            if (!$existingContracts = $configuration->getContractNumbers()) {
                $existingContracts = json_encode(array());
            }

            $existingContracts = json_decode($existingContracts);
            $newContractObject = json_decode(json_encode(
                    array(
                        'contractnumber' => $contractNumber,
                        'unitGUID' => $unitGUID,
                        'license' => $license,
                        'identifier' => $identifier

                    )
                )
            );

            $contractInDb = false;

            foreach ($existingContracts as $existingContract) {
                if ($existingContract->contractnumber == $newContractObject->contractnumber) {
                    $contractInDb = true;
                    break;
                }
            }

            if ($contractInDb) {
                $returnResult['data'] = $errors = array(
                    'error' => array(
                        'errorMessage' => 'Contract already in database',
                        'errorMessageExtended' => ''
                    )
                );
                $this->View()->assign($returnResult);

                return;
            }

            array_push($existingContracts, $newContractObject);
            $configuration->setContractNumbers(json_encode($existingContracts));
            Shopware()->Models()->persist($configuration);
            Shopware()->Models()->flush();

            $this->View()->assign([
                'success' => true,
                'data' => [],
                'total' => 0
            ]);
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
        }
    }

    public function deleteContractAction()
    {
        $params = $this->Request()->getParams();
        $contractNumber = $params['contractnumber'];
        $success = false;
        $data = array();

        $this->View()->assign([
            'success' => $success,
            'data' => $data,
            'total' => 0
        ]);

        if (!$contractNumber) {
            return;
        }

        try {
            $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\PostLabelPluginConfiguration::class);
            $configurations = $repository->findAll();

            if (!$configurations) {
                return;
            }

            $configuration = $configurations[0];

            if (!$existingContracts = $configuration->getContractNumbers()) {
                return;
            }

            $existingContracts = json_decode($existingContracts);
            $contractForDelete = json_decode(json_encode(array('contractnumber' => $contractNumber)));

            foreach ($existingContracts as $idx => $existingContract) {
                if ($existingContract->contractnumber == $contractForDelete->contractnumber) {
                    unset($existingContracts[$idx]);
                }
            }

            $configuration->setContractNumbers(json_encode(array_values($existingContracts)));

            Shopware()->Models()->persist($configuration);
            Shopware()->Models()->flush();

            $this->View()->assign([
                'success' => true,
                'data' => $data,
                'total' => 0
            ]);

        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    public function getPluginBankAction ()
    {
        $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\PostLabelPluginConfiguration::class);
        $configuration = $repository->findAll();
        $data = Shopware()->Models()->toArray($configuration);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ]);
    }

    public function savePluginBankAction ()
    {

        $unitID = $this->Request()->getParam('unitID');

        if (isset($unitID)) {
            $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\PostLabelPluginConfiguration::class);

            // should be max one saved configuration
            $allSavedConfigurations = $repository->findAll();
            $savedConfiguration = $repository->findOneBy(['unitID' => $unitID]);

            if ($savedConfiguration) {
                $pluginConfiguration = $savedConfiguration;
            } else {
                // we have one saved but under different guid
                if ($allSavedConfigurations) {
                    $pluginConfiguration = $allSavedConfigurations[0];
                } else {
                    $this->View()->assign([
                        "success" => false,
                        "error" => 'Bitte speichern Sie zuerst Grundeinstellungen!'
                    ]);

                    return;
                }
            }
        } else {
            $this->View()->assign([
                "success" => false,
                "error" => 'Bitte speichern Sie zuerst Grundeinstellungen!'
            ]);

            return;
        }

        $bankAccountOwner = $this->Request()->getParam('bankAccountOwner');
        $bankBic = $this->Request()->getParam('bankBic');
        $accountIban = $this->Request()->getParam('accountIban');
        $pluginConfiguration->setBankAccountOwner($bankAccountOwner);
        $pluginConfiguration->setBankBic($bankBic);
        $pluginConfiguration->setAccountIban($accountIban);

        // todo - validate fields above

        try {
            $this->container
                ->get("post_label_center.greenfield_service")
                ->setConfig($pluginConfiguration)
                ->sanityCheck();
            Shopware()->Models()->persist($pluginConfiguration);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign([
                "success" => false,
                "error" => strval($e->getMessage())
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true
        ]);
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'getPluginConfiguration',
            'getPluginConfigurationContracts'
        ];
    }
}
