<?php

use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class Shopware_Controllers_Frontend_PostLabelOrderReturn extends Enlight_Controller_Action
{
    protected $modelManager;

    protected $postLabelOrderReturnService = null;

    public function indexAction()
    {
        $hashFromOrderMailLink = $this->Request()->getParam('userorder');
        $returnedFromHash = false;

        if ($hashFromOrderMailLink) {
            $orderId = $this->postLabelOrderReturnService->getOrderIdFromHash($hashFromOrderMailLink);
            $returnedFromHash = true;
        } else {
            $orderId = $this->Request()->getParam('orderid');
        }

        if (!$orderId) {
            $this->redirectReturnToOrders();
        }

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $order = $repository->findOneBy(['id' => $orderId]);

        if (!$order || !$order->getNumber()) {
            $this->redirectReturnToHome();
            return;
        }

        $orderUser = $order->getCustomer();
        if (!$orderUser) {
            $this->redirectReturnToHome();
            return;
        }
        $orderUserId = $orderUser->getId();

        // user has to be logged or has valid hash checked above
        $userId = Shopware()->Session()->get('sUserId');

        if (!$userId && !$returnedFromHash) {
            $this->redirectReturnToHome();
            return;
        }

        if ($returnedFromHash) {
            $userId = $orderUserId;
        }

        if ($userId != $orderUserId) {
            $this->redirectReturnToHome();
            return;
        }

        $this->View()->assign('orderid', $orderId);
        $orderReturnArticles = $this->postLabelOrderReturnService->getOrderReturnArticles($order);

        if (count($orderReturnArticles) == 0) {
            if ($returnedFromHash) {
                $this->redirectReturnToHome();
            } else {
                $this->redirectReturnToOrders();
            }
            return;
        }

        $this->View()->assign('orderReturnArticles', $orderReturnArticles);
        $returnReasons = $this->postLabelOrderReturnService->getReturnReasonList();
        $this->View()->assign('returnReasons', $returnReasons);

    }

    public function redirectReturnToOrders($showMessage = true)
    {
        if ($showMessage) {
            Shopware()->Session()->sOrderReturnMessages = array(
                'redirectInfoMessage' => 'Diese Bestellung wurde bereits zur Gänze als Retoure angemeldet, 
				bitte wenden Sie sich bei Fragen oder Problemen mittels Kontaktformular an unser Kundenservice'
            );
        }
        return $this->redirect(
            [
                'controller' => 'account',
                'action' => 'orders',
            ]
        );
    }

    public function redirectReturnToHome()
    {
        return $this->redirect(
            [
                'controller' => 'index',
            ]
        );
    }

    public function getSubsidiaryListAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $this->Response()->setHeader('Content-type', 'application/json', true);

        $gf = $this->container->get("post_label_center.greenfield_service");
        $sString = $this->Request()->getParam('sString');

        if (!$sString) {
            exit;
        }

        try {
            $branchesList = $gf->getActiveBranches($sString, 2);
        } catch (GreenFieldResponseException $e) {
            $response = json_decode($e->getResponse()->getBody());
            $error = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->extendedMessage
                )
            );

            $this->Response()->setBody(json_encode($error));
            return;

        } catch (Exception $e) {
            $response = $e->getMessage();
            $error = array(
                'error' => array(
                    'errorMessage' => $response,
                    'errorMessageExtended' => $response
                )
            );
            $this->Response()->setBody(json_encode($error));
            return;
        }

        $branchesList = $branchesList->data;
        $allBranches = array();

        foreach ($branchesList as $singleBranch) {

            $fullBranch = json_decode(json_encode($singleBranch), true);
            $branch = array();
            $branch['additionalBranchData']['branchKey'] = $fullBranch['branchKey'];
            $branch['additionalBranchData']['firstLineOfAddress'] = $fullBranch['firstLineOfAddress'];
            $branch['additionalBranchData']['orgPostalCode'] = $fullBranch['orgPostalCode'];
            $branch['additionalBranchData']['type'] = $fullBranch['type'];
            $branch['name'] = $fullBranch['firstLineOfAddress'];
            $branch['address'] = $fullBranch['address'];
            $branch['type'] = $fullBranch['type'];
            $tmpBranchAddress = $singleBranch->address->city . " "
                . $singleBranch->address->streetName . " "
                . $singleBranch->address->streetNumber . ", "
                . $singleBranch->address->postalCode . ", "
                . $singleBranch->name . ", ";

            $branch['title'] = $tmpBranchAddress;
            array_push($allBranches, $branch);
        }

        $this->Response()->setHeader('Content-type', 'application/json', true);
        $this->Response()->setBody(json_encode($allBranches));
    }

    public function setUserSubsidiaryAction()
    {
        $session = Shopware()->Session();

        $branchData = array(
            'userId' => $session->offsetGet('sUserId'),
            'sBranch' => $this->Request()->getParam('sBranch'),
            'branchAddress' => $this->Request()->getParam('branchAddress'),
            'additionalBranchData' => $this->Request()->getParam('additionalBranchData'),
        );

        Shopware()->Session()->offsetSet('postBranch', $branchData);
        exit;
    }

    public function getUserBranchAction()
    {
        $userBranch = Shopware()->Session()->offsetGet('postBranch');

        if ($userBranch) {
            $branchDom = "<div>Ausgewählte Filiale:<br> " .
                "<div><strong>" .
                $userBranch['sBranch'] .
                "</strong>" .
                "<br><a id='delete_selected_user_branch' style='cursor: pointer;'><i class='icon--cross'></i> Entfernen</a></div></div>";
            echo $branchDom;
        }

        exit;
    }

    public function removeUserBranchAction()
    {
        Shopware()->Session()->offsetUnset('postBranch', null);
        exit;
    }

    public function preDispatch()
    {
        $pluginPath = $this->container->getParameter('post_label_center.plugin_dir');
        $this->get('template')->addTemplateDir($pluginPath . '/Resources/views/');
        $this->postLabelOrderReturnService = Shopware()->Container()->get('post_label_return.post_label_return_service');
    }

    public function returnArticlesAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $params = $this->Request()->getParams();
        $orderId = $params['orderid'];
        $returnArticles = $params['returnArticles'];

        $returnData = array(
            'success' => true,
            'redirect' => false,
            'errorMessage' => '',
            'data' => array()
        );

        if (!$orderId) {
            $returnData['success'] = false;
            $returnData['errorMessage'] = 'Fehler: Es wurde keine Bestellung ID übermittelt.';
            $returnData['redirect'] = array('controller' => 'account', 'action' => 'orders');
            $this->sendJsonResponse($returnData);
            return;
        }

        if (!count($returnArticles)) {
            $returnData['success'] = false;
            $returnData['errorMessage'] = 'Fehler: Es wurden keine Artikel gefunden.';
            $returnData['redirect'] = array('controller' => 'postlabelorderreturn', 'action' => 'index');
            $this->sendJsonResponse($returnData);
            return;
        }

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $order = $repository->findOneBy(['id' => $orderId]);
        $orderDetails = $order->getDetails();
        $returnAllowed = false;
        $returnedArticles = [];

        foreach ($returnArticles as $articleOrderNumber => $returnArticle) {

            if (array_key_exists('checked',
                    $returnArticle) && $returnArticle['checked'] == 'on' && $returnArticle['reason']) {

                foreach ($orderDetails as $detail) {
                    if ($articleOrderNumber == $detail->getArticleNumber() && $detail->getMode() == '0') {

                        $returnArticleModel = new \PostLabelCenter\Models\PostLabelReturnArticle();

                        if (!$this->postLabelOrderReturnService->isArticleReturnPossible($detail,
                            $returnArticle['amount'])) {
                            continue;
                        }

                        $dateTime = new DateTime();
                        $returnArticleModel->setDetailId($detail->getId());
                        $returnArticleModel->setOrderId($detail->getOrder()->getId());
                        $returnArticleModel->setArticleId($detail->getArticleId());
                        $returnArticleModel->setArticleOrderNumber($articleOrderNumber);
                        $returnArticleModel->setAmount($returnArticle['amount']);
                        $returnArticleModel->setReturnReason($returnArticle['reason']);
                        $returnArticleModel->setReturnTime($dateTime);

                        $returnArticle['returnDate'] = $dateTime->format('d.m.Y H:i:s');
                        try {
                            Shopware()->Models()->persist($returnArticleModel);
                            Shopware()->Models()->flush($returnArticleModel);
                        } catch (Exception $e) {
                            $returnData['success'] = false;
                            $returnData['errorMessage'] = 'Fehler: ' . $e->getMessage();
                            $this->sendJsonResponse($returnData);
                            return;
                        }

                        $returnArticle['details'] = Shopware()->Models()->toArray($detail);
                        $returnArticle['details']['image'] = Shopware()->Modules()->Articles()->sGetArticlePictures(
                            $detail->getArticleId(), true, null, $articleOrderNumber);

                        $returnedArticles[] = $returnArticle;
                        $returnAllowed = true;
                    }
                }
            }
        }

        if (!$returnAllowed) {
            $returnData['success'] = false;
            $returnData['errorMessage'] = 'Fehler: Retoure von ausgewählten Artikel ist nicht mehr möglich.';
            $this->sendJsonResponse($returnData);
            return;
        }
        try {
            $gf = $this->container->get("post_label_center.greenfield_service");
            $response = $gf->importShipment($orderId, true);
            // todo: need to check for errors here
            $pdfContent = $response->data->pdfData;
            $pdfContent = base64_decode($pdfContent); // pdf may be different, depending on what the API sends back

            // UPDATE ORDER STATUS TO which status?
//                $orderStatus = Shopware()->Models()->find(Status::class, 4);
//                $order->setOrderStatus($orderStatus);
//                Shopware()->Models()->Persist($order);
//                Shopware()->Models()->flush();


            if (!$pdfContent) {
                $returnData['success'] = false;
                $returnData['errorMessage'] = 'Fehler beim generieren von Retourenlabel.';
                $this->sendJsonResponse($returnData);
                return;
            }

            $uniqId = uniqid();
            $fileName = $this->getUniqFileName($uniqId);
            $cacheDir = $this->getLabelsCacheDir();
            $filePath = $cacheDir . '/' . $fileName;

            $shipments = $response->data->importShipmentResult;

            foreach ($shipments as $shipment) {

                $trackingCodes = [];

                foreach ($shipment->colloCodeList as $collCodeElement) {
                    $trackingCodes[] = $collCodeElement->code;
                    //todo - remove later when multipackage available
                    break;
                }

                if (count($trackingCodes) > 0) {

                    // save view into DB - usability
                    $orderAttributes = $gf->getOrderAttributes($orderId);

                    // todo: check if wanted attribute exists ?
                    if (!$labelsDownloadsList = $orderAttributes['post_labels_downloads']) {
                        $labelsDownloadsList = '[]';
                    }

                    //DO LOGIC
                    // if someone changes order status change only first pf tracking numbers
                    $trackingCode = $trackingCodes[0];
                    $documentId = $response->data->documentId;
                    $date = new DateTime();
                    $date = $date->format('d.m.Y H:i:s');

                    $source = 'additional_label';

                    $labelDownload = array(
                        'documentid' => $documentId,
                        'label_data' => array(
                            'date' => $date,
                            'type' => 'RETURN_LABEL',
                            'source' => $source,
                            'downloaded' => false,
                            'tracking_code' => $trackingCode
                        )
                    );

                    $labelsDownloadsList = json_decode($labelsDownloadsList);

                    // if status change replace first tracking number, else just add it
                    $existingTrackingCodes = array_map('trim', explode(';', $order->getTrackingCode()));
                    $existingTrackingCodes[] = $trackingCode;
                    $existingTrackingCodes = implode('; ', $existingTrackingCodes);
                    $order->setTrackingCode($existingTrackingCodes);

                    // manipulate order attribute - first record should always be label,
                    // created by status change
                    if (count($labelsDownloadsList) > 0) {
                        $labelsDownloadsList[] = $labelDownload;

                    } else {
                        $labelsDownloadsList[] = $labelDownload;
                    }

                    $orderAttributes['post_labels_downloads'] = json_encode($labelsDownloadsList);
                    $attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');
                    $attributePersister->persist($orderAttributes, 's_order_attributes', $orderId);
                }

                try {
                    Shopware()->Models()->persist($order);
                    Shopware()->Models()->flush();
                } catch (Exception $e) {
                    $returnData['success'] = false;
                    $returnData['errorMessage'] = $e->getMessage();
                    $this->sendJsonResponse($returnData);

                    return;
                }

            }

            if (!is_dir($cacheDir)) {
                if (false === @mkdir($cacheDir, 0777, true)) {
                    throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", 'Postlabels',
                        $cacheDir));
                }
            } elseif (!is_writable($cacheDir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", 'Postlabels',
                    $cacheDir));
            }

            $pdfFile = fopen($filePath, 'wb');
            fwrite($pdfFile, $pdfContent, strlen($pdfContent));
            fclose($pdfFile);

        } catch (GreenFieldResponseException $e) {
            $response = json_decode($e->getResponse()->getBody());
            $error = "$response->error->message  $response->error->extendedMessage";
            $returnData['success'] = false;
            $returnData['errorMessage'] = $error;
            $this->sendJsonResponse($returnData);

            return;
        } catch (Exception $e) {
            $returnData['success'] = false;
            $returnData['errorMessage'] = $e->getMessage();
            $this->sendJsonResponse($returnData);

            return;
        }

        $returnData['success'] = true;
        $returnData['errorMessage'] = '';
        $returnData['data']['labelId'] = $uniqId;
        $this->sendJsonResponse($returnData);

        return;
    }

    public function sendJsonResponse($returnData)
    {
        $this->Response()->setHeader('Content-type', 'application/json');
        $this->Response()->setBody(json_encode($returnData));
    }

    public function getUniqFileName($uniqId, $LabelType = 'RETURN_LABEL')
    {
        return 'RetourenLabel_' . $uniqId . '.pdf';
    }

    public function getLabelsCacheDir()
    {
        return $this->get('kernel')->getCacheDir() . '/general/postlabels/';
    }

    public function getReturnLabelAction()
    {
        $uniqId = $this->Request()->getParam('labelId');
        $fileName = $this->getUniqFileName($uniqId);
        $cacheDir = $this->get('kernel')->getCacheDir() . '/general/postlabels/';
        $filePath = $cacheDir . '/' . $fileName;
        $pdfFile = file_get_contents($filePath);
        $response = $this->Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-Disposition', 'attachment;filename="Retoure.pdf"');
        $response->setHeader('Expires', '0');
        $response->setHeader('Cache-Control', 'must-revalidate');
        $response->setHeader('Pragma', 'public');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', strlen($pdfFile));
        $response->sendHeaders();
        echo $pdfFile;
        exit;
    }

    public function redirectReturnToReturn($orderId)
    {
        return $this->redirect(
            [
                'controller' => 'postlabelorderreturn',
                'action' => 'index',
                'orderid' => $orderId
            ]
        );
    }
}


