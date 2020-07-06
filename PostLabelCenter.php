<?php

namespace PostLabelCenter;

use Exception;
use PostLabelCenter\Models\PostLabelPluginConfiguration;
use PostLabelCenter\Models\PostPluginConfiguration;
use Shopware\Components\Plugin;
use Shopware\Models\Widget\Widget;
use Enlight_Controller_Request_RequestHttp;
use Acl\GreenField\Exceptions\GreenFieldSDKException;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use PostLabelCenter\Bootstrap\Database;
use Doctrine\ORM\Tools\SchemaTool;
use PostLabelCenter\Models\PostLabelReturnArticle;
use Shopware\Components\Theme\LessDefinition;
use Doctrine\Common\Collections\ArrayCollection;


class PostLabelCenter extends Plugin
{
    public static function getSubscribedEvents()
    {
        $action = "Enlight_Controller_Action";
        $dispatch = "Enlight_Controller_Dispatcher";
        $model = "Shopware\\Models\\";
        return [
            "{$action}_PreDispatch" => "onRegisterSubscriber",
            "{$action}_PostDispatch" => "onPostDispatch",
            "{$action}_PostDispatchSecure" => "addTemplateDir",
            "{$action}_PostDispatch_Backend_Order" => "onBackendOrderPostDispatch",
            "{$action}_PostDispatch_Backend_Shipping" => "onBackendShippingPostDispatch",
            "{$dispatch}_ControllerPath_Backend_DailyStatement" => "onGetBackendControllerPostDailyStatement",
            "{$dispatch}_ControllerPath_Backend_PostPluginConfiguration" => "onGetBackendControllerPostPluginConfiguration",
            "{$model}Order\\Order::preUpdate" => "onOrderPreUpdate",
            'Enlight_Controller_Action_preDispatch_Frontend_Account' => 'onAccountOrderDispatch',
            'Shopware_Modules_Admin_GetOpenOrderData_FilterResult' => 'onGetOpenOrderData',
            'Theme_Compiler_Collect_Plugin_Less' => 'onCollectLessFiles',
            'Shopware_Modules_Order_SendMail_FilterVariables' => 'beforeOrderMailSend',
            "Theme_Compiler_Collect_Plugin_Javascript" => "addJsFiles",
            'Shopware_Modules_Order_SaveShipping_FilterArray' => 'onOrderShippingSave',
            'Enlight_Controller_Action_preDispatch_Frontend_Checkout' => 'onCheckoutPreDispatch',
            'Enlight_Controller_Action_postDispatchSecure_Frontend_Checkout' => 'onCheckoutPostDispatch',
            // todo - implement in one of next versions
            //'Enlight_Controller_Dispatcher_ControllerPath_Backend_PostBackendWidget' => 'onGetBackendControllerPostBackendWidget',
            'Enlight_Controller_Action_PostDispatch_Backend_Index' => 'onPostDispatchBackendIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Analytics' => 'onBackendPostDispatchSecure',
            'Shopware_Modules_Order_SaveOrder_FilterDetailsSQL' => 'onSaveOrderFilterDetailsSql'
        ];
    }

    public function install(InstallContext $context)
    {
        $service = $this->container->get('shopware_attribute.crud_service');



        $service->update(
            's_articles_attributes',
            'postfeature_fragile',
            'boolean',
            [
                'label' => 'Zerbrechlich',
                'displayInBackend' => true,
                'translatable' => true,
                'position' => 200,
            ],
            null,
            true,
            0
        );



        $service->update(
            's_articles_attributes',
            'postfeature_hazardous',
            'boolean',
            [
                'label' => 'Gefahrgut',
                'displayInBackend' => true,
                'translatable' => true,
                'position' => 201,
            ],
            null,
            true,
            0
        );

        $service->update(
            's_order_attributes',
            'customsDescription',
            'text',
            [
                'label' => 'EMS Inhaltsbeschreibung',
                'displayInBackend' => true,
                'translatable' => false,
                'position' => 201,
            ],
            null,
            true,
            0
        );

        $service->update(
            's_order_attributes',
            'post_import_status',
            'text',
            [
                'label' => 'PLC order status',
                'displayInBackend' => true,
                'translatable' => false,
                'position' => 202,
            ],
            null,
            true,
            0
        );

        $service->update(
            's_order_attributes',
            'branch',
            'text',
            [
                'label' => 'Branch',
                'displayInBackend' => true,
                'translatable' => true,
                'position' => 203,
            ],
            null,
            true,
            0
        );

        $service->update(
            's_order_attributes',
            'higher_insurance',
            'float',
            [
                'label' => 'Höherversicherung',
                'displayInBackend' => true,
                'translatable' => true,
                'position' => 204,
            ],
            null,
            true,
            0
        );

        $service->update(
            's_order_attributes',
            'shipper_reference_2',
            'text',
            [
                'label' => 'Referenz 2',
                'displayInBackend' => true,
                'translatable' => true,
                'position' => 205,
            ],
            null,
            true,
            0
        );

        $service->update(
            's_user_attributes',
            'branch',
            'text',
            [
                'label' => 'Branch',
                'displayInBackend' => true,
                'translatable' => true,
                'position' => 203,
            ],
            null,
            true,
            0
        );
// todo  - implement in one of the next versions
//        $plugin = $context->getPlugin();
//        $widget = new Widget();
//        $widget->setName('post-backend-widget');
//        $widget->setLabel('Post News');
//        $widget->setPlugin($plugin);
//        $plugin->getWidgets()->add($widget);

        Shopware()->Models()->generateAttributeModels(['s_articles_attributes']);
        Shopware()->Models()->generateAttributeModels(['s_user_attributes']);

        $database = new Database(
            $this->container->get('models')
        );

        $database->install();
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();

        //Order return
        $this->createSchema();
        $this->createAttributes();
        parent::install($context);
    }

    public function createSchema()
    {
        $models = $this->container->get('models');
        $tool = new SchemaTool($models);
        $schemaManager = $models->getConnection()->getSchemaManager();

        $classes = [$models->getClassMetaData(PostLabelReturnArticle::class)];

        if (!$schemaManager->tablesExist(['post_label_return_articles'])) {
            $tool->createSchema($classes);
        }

        $classes = [$models->getClassMetadata(PostLabelPluginConfiguration::class)];
        if (!$schemaManager->tablesExist(['post_label_plugin_configuration'])) {
            $tool->createSchema($classes);
        }
    }

    public function createAttributes()
    {
        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update('s_order_attributes', 'orderidhash', 'text', [
            'label' => 'Maximaler Refundbetrag ',
            'helpText' => 'Bitte nich editieren!',
            'translatable' => true,
            'displayInBackend' => false
        ]);

        $service->update('s_order_attributes', 'post_labels_downloads', 'text', [
            'label' => 'Post Labels Status ',
            'helpText' => 'Info über Labels Download',
            'translatable' => true,
            'displayInBackend' => false
        ]);

        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels(['s_order_attributes']);
    }

    public function addJsFiles(\Enlight_Event_EventArgs $args)
    {
        $jsDir = __DIR__ . '/Resources/views/frontend/_public/src/js/';

        return new ArrayCollection([
            $jsDir . 'custom.js',
            $jsDir . 'jquery-ui.min.js'
        ]);
    }

    public function uninstall(UninstallContext $context)
    {
        $plugin = $context->getPlugin();
        $em = $this->container->get('models');
        $widget = $plugin->getWidgets()->first();

        if ($widget) {
            $em->remove($widget);
            $em->flush();
        }

        $database = new Database(
            $this->container->get('models')
        );
        if ($context->keepUserData()) {
            return;
        }
        $database->uninstall();

    }

    public function update(UpdateContext $updateContext)
    {
        $currentVersion = $updateContext->getCurrentVersion();
        //$updateVersion = $updateContext->getUpdateVersion();

        if (version_compare($currentVersion, '1.0.3', '<=')) {
            $service = $this->container->get('shopware_attribute.crud_service');
            $service->update(
                's_order_attributes',
                'customsDescription',
                'text',
                [
                    'label' => 'EMS Inhaltsbeschreibung',
                    'displayInBackend' => true,
                    'translatable' => false,
                    'position' => 201,
                ],
                null,
                true,
                0
            );
        }
        if (version_compare($currentVersion, '1.0.6', '<=')) {
            $service = $this->container->get('shopware_attribute.crud_service');
            $service->update(
                's_order_attributes',
                'higher_insurance',
                'float',
                [
                    'label' => 'Höherversicherung',
                    'displayInBackend' => true,
                    'translatable' => true,
                    'position' => 204,
                ],
                null,
                true,
                0
            );
        }
        if (version_compare($currentVersion, '1.0.9', '<=')) {
            $service = $this->container->get('shopware_attribute.crud_service');
            $service->update(
                's_order_attributes',
                'shipper_reference_2',
                'text',
                [
                    'label' => 'Referenz 2',
                    'displayInBackend' => true,
                    'translatable' => true,
                    'position' => 205,
                ],
                null,
                true,
                0
            );
        }
    }

    public function onRegisterSubscriber()
    {
        $this->registerMyComponents();
    }

    public function registerMyComponents()
    {
        require_once __DIR__ . "/vendor/autoload.php";
    }

    public function onBackendOrderPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $view = $args->getSubject()->View();

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/order/post_shipment/controller/detail.js');
            $view->extendsTemplate('backend/order/post_shipment/controller/list.js');
            $view->extendsTemplate('backend/order/post_shipment/controller/batch.js');
            $view->extendsTemplate('backend/order/post_labels/controller/post_labels.js');
            $view->extendsTemplate('backend/order/post_labels/view/detail/window.js');
            $view->extendsTemplate('backend/order/post_tracking/view/detail/window.js');
        }

        // add post_import_status attribute value into backend component
        if ($request->getActionName() === 'getList') {
            $assignedData = $view->getAssign('data');
            foreach ($assignedData as $key => $order) {
                $assignedData[$key]["postImportStatus"] = Shopware()->Db()->fetchOne(
                    'SELECT IFNULL((SELECT post_import_status FROM s_order_attributes WHERE orderID = ?),"")',
                    array($order["id"])
                );
            }
            $view->assign('data', $assignedData);
        }

        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/order/post_labels/app.js');
            $view->extendsTemplate('backend/order/post_tracking/app.js');
        }
    }

    public function onBackendShippingPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $view = $args->getSubject()->View();

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/shipping/post_configuration/view/edit/panel.js');
        }

        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/shipping/post_configuration/app.js');
        }
    }

    public function onGetBackendControllerPostDailyStatement()
    {
        return $this->getPath() . '/Controllers/Backend/PostDailyStatement.php';
    }

    public function onGetBackendControllerPostPluginConfiguration()
    {
        return $this->getPath() . '/Controllers/Backend/PostPluginConfiguration.php';
    }

    public function onPostDispatch(\Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        if ($request->getModuleName() === "backend"
            && $request->getControllerName() === "config"
            && $request->getActionName() === "saveForm"
            && $request->getParam("name") === "PostLabelCenter"
        ) {
            $this->configSaved($args, $request);
        }
    }

    protected function configSaved(\Enlight_Event_EventArgs $args, $request = null)
    {
        $request = $request ?: $args->getSubject()->Request();
        $status = true;

        foreach ($request->get("elements") as $element) {
            if ($element["name"] !== "plc.sanityCheck") {
                continue;
            }
            if ($element["value"] === false) {
                break;
            }
            try {
                $status = $this->container
                    ->get("post_label_center.greenfield_service")
                    ->sanityCheck();
            } catch (GreenFieldSDKException $e) {
                // @todo: return a nicely formated error
                $status = false;
            }
        }
        if ($status === false) {
            // @todo: replace with a proper error
            throw new Exception("Error occured when checking API authentication data.");
        }
    }

    public function onPostDispatchBackendIndex(\Enlight_Controller_ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $view->addTemplateDir($this->getPath() . '/Resources/views/');

//        if ($request->getActionName() === 'index') {
//            $view->extendsTemplate('backend/index/post_backend_widget/app.js');
//        }

        //CSS und Javascript laden
        $view->extendsTemplate('backend/index/post_label_center/index.tpl');
    }

    public function addTemplateDir(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->addTemplateDir($this->getPath() . '/Resources/views/');
    }

    public function onAccountOrderDispatch(\Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $controller = $eventArgs->getSubject();
        $view = $controller->View();
        $request = $controller->Request();
        $action = $request->getActionName();

        if ($action == 'orders') {
            $view->addTemplateDir(__DIR__ . '/Resources/views/');
        }
    }

    // add maxReturnTime to orders array
    public function onGetOpenOrderData(\Enlight_Event_EventArgs $eventArgs)
    {
        $filteredReturn = $eventArgs->getReturn();
        $postLabelReturnService = Shopware()->Container()->get('post_label_return.post_label_return_service');
        $gf = Shopware()->Container()->get("post_label_center.greenfield_service");

        if (!$gf->isOrderReturnAllowed()) {
            $eventArgs->setReturn($filteredReturn);
            return;
        }

        Shopware()->Template()->assign('postReturnOrderAllowed', true);

        foreach ($filteredReturn as $idx => $order) {
            $maxOrderDate = $postLabelReturnService->getMaxReturnTimeFromOrderTime($filteredReturn[$idx]['ordertime']);
            $filteredReturn[$idx]['maxReturnTime'] = $maxOrderDate;
            $filteredReturn[$idx]['returnPossible'] = false;
//			$filteredReturn[$idx]['notOnlyEsdInOrder'] = $this->isEsdOnlyOrder($order['details']);
            $returnByQuantityAllowed = $postLabelReturnService->orderReturnByQuantityAllowed($order['details']);
            $filteredReturn[$idx]['returnByQuantityAllowed'] = $returnByQuantityAllowed;
        }

        // check session for custom messages from frontend controller
        if (Shopware()->Session()->sOrderReturnMessages) {
            Shopware()->Template()->assign('sOrderReturnMessages', Shopware()->Session()->sOrderReturnMessages);
            Shopware()->Session()->sOrderReturnMessages = null;
        }

        $eventArgs->setReturn($filteredReturn);
    }

    public function isEsdOnlyOrder($details)
    {
        foreach ($details as $detail) {
            if ($detail['esdarticle'] != '1' && $detail['modus'] == '0') {
                return true;
            }
        }
        return false;
    }

    public function onBackendPostDispatchSecure(\Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $request = $eventArgs->getSubject()->Request();
        $view = $eventArgs->getSubject()->View();
        $view->addTemplateDir($this->getPath() . '/Resources/views/');

        if ($request->getActionName() == 'index') {
            $view->extendsTemplate('backend/analytics/post_order_return.js');
        }

        if ($request->getActionName() == 'load') {
            $view->extendsTemplate('backend/analytics/store/post_order_return/navigation.js');
            $view->extendsTemplate('backend/analytics/view/table/post_order_return.js');
            $view->extendsTemplate('backend/analytics/controller/post_order_return/main.js');
        }
    }

    public function beforeOrderMailSend(\Enlight_Event_EventArgs $eventArgs)
    {
        $return = $eventArgs->getReturn();
        $userBranch = Shopware()->Session()->offsetGet('postBranch');

        $orderNumber = $return['ordernumber'];

        if (!$orderNumber) {
            $eventArgs->setReturn($return);
            return;
        }

        // update order attributes with user selected branch
        if ($userBranch) {
            $shippingAdress = $return['shippingaddress'];
            $shippingAdress['department'] = (string)($userBranch['additionalBranchData']['firstLineOfAddress']);
            $shippingAdress['street'] = (string)($userBranch['branchAddress']['streetName'] . ' ' . $userBranch['branchAddress']['streetNumber']);
            $shippingAdress['zipcode'] = (string)($userBranch['branchAddress']['postalCode']);
            $shippingAdress['city'] = (string)($userBranch['branchAddress']['city']);
            // hardcoded - if branches available in more countries
            $shippingAdress['countryID'] = '23';
            $shippingAdress['additional_address_line1'] = '';
            $shippingAdress['additional_address_line2'] = (string)($userBranch['additionalBranchData']['branchKey']);
            //todo  remove company if branch delivery
            $shippingAdress['company'] = '';
            $return['shippingaddress'] = $shippingAdress;

            $sql = "SELECT id FROM s_order WHERE ordernumber = :orderNumber";
            $query = Shopware()->Container()->get('dbal_connection')->executeQuery(
                $sql,
                ['orderNumber' => $orderNumber]
            );

            $result = $query->fetch();
            $orderId = $result['id'] ? $result['id'] : false;

            if ($orderId) {
                $sql = "UPDATE s_order_attributes SET branch = :userBranch WHERE orderID = :orderID LIMIT 1";

                Shopware()->Container()->get('dbal_connection')->executeQuery(
                    $sql,
                    [
                        'userBranch' => $userBranch['sBranch'] ? json_encode($userBranch) : '1',
                        'orderID' => $orderId
                    ]
                );
            }
        }
        $eventArgs->setReturn($return);
    }

    public function onCollectLessFiles()
    {
        $lessDir = __DIR__ . '/Resources/views/frontend/_public/src/less/';
        $cssDir = __DIR__ . '/Resources/views/frontend/_public/src/css/';
        $less = new LessDefinition(
            array(),
            array(
                $lessDir . 'custom.less'
            )
        );

        return new ArrayCollection(array($less));
    }

    public function onOrderPreUpdate(\Enlight_Event_EventArgs $eventArgs)
    {
        // zu Lieferung bereit has status id 5
        $request = new \Enlight_Controller_Request_RequestHttp();

        if ($request->getModuleName() == 'api' && $request->getControllerName() == 'orders') {
            $order = $eventArgs->get('entity');
            $newOrderStatusId = $request->getParam('orderStatusId');
            $oldOrderStatus = Shopware()->Models()->toArray($order);
            $oldOrderStatusId = $oldOrderStatus['status'];

            // todo - check old status or not => problem is that every WS call returns different tracking number
            if (/*$newOrderStatusId != $oldOrderStatusId && */
                $newOrderStatusId == '5') {
                //send webservice call
                $gf = $this->container->get("post_label_center.greenfield_service");
                $success = true;
                $orderID = $order->getId();

                try {
                    $response = $gf->importShipment($orderID);
                } catch (GreenFieldResponseException $e) {
                    // todo
                    // error.log
                    //die('WS ERROR');
                }

                if ($response->getRawResponse()->getResponseCode() != '200') {
                    $success = false;
                }

                if ($success) {
                    $attributeData['post_import_status'] = 'OK';
                } else {
                    $attributeData['post_import_status'] = 'ERROR';
                }

                $trackingCodes = array();
                $shipments = $response->data->importShipmentResult;

                foreach ($shipments as $shipment) {
                    foreach ($shipment->colloCodeList as $collCodeElement) {
                        $trackingCodes[] = $collCodeElement->code;
                        //todo - remove later when multipackage available
                        break;
                    }
                }

                if (count($trackingCodes) > 0 /* && $order->getTrackingCode() == ''*/) {
                    $order->setTrackingCode($trackingCodes[0]);
                }
            }
        }
    }

    public function onCheckoutPostDispatch(\Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $controller = $eventArgs->getSubject();
        $request = $controller->Request();
        $dispatchesManipulated = false;

        if ($request->getActionName() === 'shippingPayment' || $request->getActionName() === 'confirm') {
            $dispatches = $controller->View()->sDispatches;
            $dispatchShippingSets = array();
            $sBasket = $controller->View()->sBasket['content'];

            // todo - somehow consolidate backend, hardcoded, webservice values
            $articleFlags = array(
                array(
                    'attributeName' => 'postfeature_fragile',
                    'name' => 'Zerbrechlich'
                ),
                array(
                    'attributeName' => 'postfeature_hazardous',
                    'name' => 'Gefahrgut - begrenzte Menge (LQ)'
                )
            );

            $existingArticleFlags = array();

            // PART 1 - get all special article attribute flags for whole basket
            foreach ($sBasket as $basketItem) {
                $itemDetails = $basketItem['additional_details'];

                foreach ($articleFlags as $flag) {
                    if (array_key_exists($flag['attributeName'], $itemDetails)) {
                        if ($itemDetails[$flag['attributeName']]) {
                            $existingArticleFlags[] = $flag['name'];
                        }
                    }
                }
            }


            // todo - implement info getter
            $gf = Shopware()->Container()->get("post_label_center.greenfield_service");
            $isBranchRequired = true;

            // PART 2 - filter shipping methods, check if any requires branch selection
            $shippingSetRepository = Shopware()->Models()->getRepository('PostLabelCenter\Models\ShippingSet');

            foreach ($dispatches as $dispatch) {

                // check if dispatch has shipping sets
                $dispatchID = intval($dispatch['id']);
                $shippingSets = null;
                $shippingSets = $shippingSetRepository->findBy(['sPremiumDispatchID' => $dispatchID]);
                $shippingSets = Shopware()->Models()->toArray($shippingSets);

                if ($shippingSets) {
                    // check features:
                    foreach ($shippingSets as $shippingSet) {
                        // currently checked product for shipping method
                        if ($shippingSet['checked']) {
                            $features = json_decode($shippingSet['features']);

                            $checkedFeatures = [];

                            foreach ($features as $feature) {
                                if ($feature->checked) {
                                    $checkedFeatures[] = $feature->name;
                                }
                            }

                            foreach ($existingArticleFlags as $existingArticleFlag) {
                                // unset dispatch
                                if (!in_array($existingArticleFlag, $checkedFeatures)) {
                                    unset($dispatches[$dispatchID]);
                                    //$dispatchesManipulated = true;

                                    if (Shopware()->Session()->sDispatch == $dispatchID) {
					                    $dispatchesManipulated = true;
                                        Shopware()->Session()->sDispatch = null;
                                        $controller->View()->sDispatch = null;
                                    }

                                }
                            }
                        }
                        $dispatchShippingSets[] = array('dispatchID' => $dispatchID, 'shippingSets' => $shippingSets);
                    }
                }
            }

            if ($dispatchesManipulated && count($dispatches) > 0) {
                $backupDispatch = reset($dispatches);
                Shopware()->Session()->sDispatch = $backupDispatch['id'];
                //$controller->View()->sDispatch = $backupDispatch;
            }
            $eventArgs->getSubject()->View()->sDispatches = $dispatches;
            $controller->View()->dispatchShippingSets = $dispatchShippingSets;

            //hides shipping if not post dispatch
            $sDispatchId = Shopware()->Session()->sDispatch;

            $shippingSet = $shippingSetRepository->findBy(['sPremiumDispatchID' => $sDispatchId]);
            $shippingSet = Shopware()->Models()->toArray($shippingSet);

            if($shippingSet) {
                $isBranchRequired = false;
            }

            if (!$isBranchRequired) {
                $controller->View()->sNoPostBranchSelected = true;
                Shopware()->Session()->offsetSet('sNoPostBranchSelected', true);
            }
        }

        // check branch and billing != shipping
        if ($request->getActionName() === 'confirm') {
            if ($controller->View()->activeShippingAddressId != $controller->View()->activeBillingAddressId) {
                Shopware()->Session()->offsetUnset('postBranch', null);
            }
        }
    }

    public function onCheckoutPreDispatch(\Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $controller = $eventArgs->getSubject();
        $request = $controller->Request();
//
//        if ($request->getActionName() === 'finish') {
//            // todo - implement info getter
//
//            if (Shopware()->Session()->offsetGet('sNoPostBranchSelected')) {
//                Shopware()->Session()->offsetUnset('sNoPostBranchSelected');
//                return $controller->forward('confirm');
//            }
//        }
    }

    public function onOrderShippingSave(\Enlight_Event_EventArgs $eventArgs)
    {
        // todo make sure that branch doesnt exists if other address chosen
        $return = $eventArgs->getReturn();
        if (!$userBranch = Shopware()->Session()->offsetGet('postBranch')) {
            $eventArgs->setReturn($return);
            return;
        }
        $return[':department'] = (string)($userBranch['additionalBranchData']['firstLineOfAddress']);
        $return[':street'] = (string)($userBranch['branchAddress']['streetName'] . ' ' . $userBranch['branchAddress']['streetNumber']);
        $return[':zipcode'] = (string)($userBranch['branchAddress']['postalCode']);
        $return[':city'] = (string)($userBranch['branchAddress']['city']);
        // hardcoded - if branches available in more countries
        $return[':countryID'] = '23';
        $return[':additional_address_line1'] = '';
        $return[':additional_address_line2'] = (string)($userBranch['additionalBranchData']['branchKey']);
        //todo  remove company if branch delivery
        $return[':company'] = '';
        $eventArgs->setReturn($return);
    }

    public function onGetBackendControllerPostBackendWidget()
    {
        return __DIR__ . '/Controllers/Backend/PostBackendWidget.php';
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return mixed
     */
    public function onSaveOrderFilterDetailsSql(\Enlight_Event_EventArgs $args)
    {
        $attributeData = [];

        $order = $args->getOrder();
        if (!$order) return;

        $sAmount = Shopware()->Modules()->Basket()->sGetAmountArticles();
        $sAmount = $sAmount['totalAmount'];
        $sAmount = $this->formatPrice($sAmount);
        $attributeData['higher_insurance'] = $sAmount;
        $attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');

        $orderID = $order['id'];
        $attributePersister->persist($attributeData, 's_order_attributes', $orderID);

        return;

    }

    /**
     * @param $price
     * @return bool|float
     */
    private function formatPrice($price)
    {
        $newPrice = floatval(str_replace(',', '.', $price));

        $newPrice = floatval(number_format($newPrice, 2));
        if (!$newPrice) {
            return false;
        }
        return $newPrice;
    }
}
