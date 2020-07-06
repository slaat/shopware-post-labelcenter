<?php

use Shopware\Components\CSRFWhitelistAware;
use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class Shopware_Controllers_Backend_PostLabels extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    protected $nameMapping = array(
        'SHIPPING_LABEL' => 'Versandlabel (PDF)',
        'RETURN_LABEL' => 'Retourenlabel (PDF)',
        'UNDEFINED_NAME' => 'Undefined'
    );

    public function loadOrderLabelsAction()
    {
        // @todo: uncomment and finalize once the API is prepared
        $gf = $this->container->get("post_label_center.greenfield_service");
        $orderNumber = intval($this->Request()->getParam('orderNumber'));
        $orderId = intval($this->Request()->getParam('orderId'));
        $order = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderId);
        $dispatch = $order->getDispatch();
        $dispatchId = $dispatch->getId();
        $overriddenConfig = false;
        $shopId = $order->getShop()->getId();

        // used shipping set
        try {
            $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\ShippingSet::class);
            $shippingSet = $repository->findOneBy(['sPremiumDispatchID' => $dispatchId]);

            if ($shippingSet) {
                $overriddenConfig = $gf->getOverriddenConfig($shippingSet);
            }
        } catch (\Exception $e) {
            // eventual implement error handling
            return $e;
        }

        if($overriddenConfig) {
            $gf->overrideAuth($overriddenConfig);
        }

        try {
            // get all documents and filter them later
            $pdfDocuments = $gf->listPdfs($orderNumber, $shopId);
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

        // mark documents with "downloaded" flag
        // get order attributes
        $orderAttributes = $gf->getOrderAttributes($orderId);
        $labelsDownloadsList = isset($orderAttributes['post_labels_downloads']) ? json_decode($orderAttributes['post_labels_downloads']) : array();
        $documentList = array();

        foreach ($pdfDocuments->data as $pdfDocument) {
            if($pdfDocument->canceled) {
                continue;
            }
            $date = date('Y-m-d H:i:s', substr($pdfDocument->updatedOn, 0, 10));
            foreach ($labelsDownloadsList as $labelDownload) {
                if ($labelDownload->documentid == $pdfDocument->id) {
                    $doc = array();
                    $doc['id'] = $pdfDocument->id;
                    $doc['date'] = $date;
                    //$doc['name'] = $pdfDocument->documentType;
                    $type = $labelDownload->label_data->type ? $labelDownload->label_data->type : 'undefined_name';
                    $doc['name'] = $this->nameMapping[$type];
                    $doc['orderId'] = $orderId;
                    $doc['orderNumber'] = $orderNumber;
                    $labelDownload->label_data->downloaded ? $doc['downloaded'] = true : $doc['downloaded'] = false;
                    $documentList[] = $doc;
                    break;
                }
            }
        }

        $this->View()->assign([
            "success" => true,
            "data" => $documentList,
            "total" => count($documentList)
        ]);
    }

    public function openPdfAction()
    {
        $gf = $this->container->get("post_label_center.greenfield_service");
        $documentId = $this->Request()->getParam('documentid');
        $orderNumber = intval($this->Request()->getParam('orderNumber'));
        $shopId = $this->Request()->getParam('shopId');
        $orderId = $this->Request()->getParam('orderid');
        $order = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderId);
        $dispatch = $order->getDispatch();
        $dispatchId = $dispatch->getId();
        $overriddenConfig = false;

        if (preg_match('/\D/', $documentId)) {
            die('Invalid parameter');
        }



        // used shipping set
        try {
            $repository = Shopware()->Models()->getRepository(\PostLabelCenter\Models\ShippingSet::class);
            $shippingSet = $repository->findOneBy(['sPremiumDispatchID' => $dispatchId]);

            if ($shippingSet) {
                $overriddenConfig = $gf->getOverriddenConfig($shippingSet);
            }
        } catch (\Exception $e) {
            // eventual implement error handling
            return $e;
        }

        if($overriddenConfig) {
            $gf->overrideAuth($overriddenConfig);
        }

        try {
            $pdfDocument = $gf->loadPdf($documentId, $orderNumber, $shopId);

        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }

        $fileName = $pdfDocument->documentType . '-' . date("d-m-Y", $pdfDocument->updatedOn / 1000) . '.pdf';
        $pdfContent = base64_decode($pdfDocument->fileContent);

        if (!$pdfContent) {
            //todo show error message
            die("no content");
        }

        $gf->markOrderAsDownloaded($orderId, $documentId, $pdfDocument);
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $response = $this->Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $response->setHeader('Expires', '0');
        $response->setHeader('Cache-Control', 'must-revalidate');
        $response->setHeader('Pragma', 'public');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', strlen($pdfContent));
        $response->sendHeaders();
        echo $pdfContent;
        exit;
    }

    public function deletePostLabelAction()
    {
        $orderId = $this->Request()->getParam('orderId');
        $labelId = $this->Request()->getParam('id');

        if (!$orderId || !$labelId) {
            $this->View()->assign([
                'data' => array('message' => 'Alle Werte sind nicht vorhanden; orderId: $orderId, labelId: $labelId'),
                'success' => false,
            ]);
            return;
        }

        $gf = $this->container->get("post_label_center.greenfield_service");
        $order = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderId);
        $trackingCodes = array_map('trim', explode(';', $order->getTrackingCode()));
        $orderAttributes = $gf->getOrderAttributes($orderId);
        $orderLabels = $orderAttributes['post_labels_downloads'] ? json_decode($orderAttributes['post_labels_downloads'], true) : [];

        foreach ($orderLabels as $idx => $orderLabel) {
            if ($orderLabel->documentid == $labelId) {
                foreach ($trackingCodes as $idx => $trackingCode) {
                    if ($trackingCode == $orderLabel->label_data->tracking_code) {
                        unset($trackingCodes[$idx]);
                    }
                }
                unset($orderLabels[$idx]);
            }
        }

        $trackingCodes = implode('; ', $trackingCodes);
        $order->setTrackingCode($trackingCodes);
        $orderAttributes['post_labels_downloads'] = json_encode($orderLabels);

        try {
            $attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');
            $attributePersister->persist($orderAttributes, 's_order_attributes', $orderId);
            Shopware()->Models()->flush();
        } catch (\Exception $e) {
            $this->View()->assign([
                'data' => array('message' => $e->getMessage()),
                'success' => false,
            ]);

            return;
        }

        $this->View()->assign([
            'data' => array(),
            'success' => true,
        ]);
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'openPdf',
            'loadOrderLabels'
        ];
    }
}
