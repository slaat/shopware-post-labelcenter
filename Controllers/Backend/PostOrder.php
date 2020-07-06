<?php

use Acl\GreenField\Exceptions\GreenFieldResponseException;

class Shopware_Controllers_Backend_PostOrder extends Shopware_Controllers_Backend_ExtJs
{
    public function importShipmentAction()
    {
        $gf = $this->container->get("post_label_center.greenfield_service");
        $orderNumber = $this->Request()->getPost('ordernumber');
        $isStatusChange = (bool)$this->Request()->getPost('statuschange');
        $labelType = $this->Request()->getPost('labeltype');
        $customsDescription = $this->Request()->getPost('customsDescription');
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $order = $repository->findBy(["number" => $orderNumber]);
        $order = $order[0];
        $orderID = $order->getId();
        $isReturnLabel = false;

        // prevent multiple imports with status change needed?
        $orderAttributes = $gf->getOrderAttributes($orderID);
        $downloads = isset($orderAttributes['post_labels_downloads']) ? $orderAttributes['post_labels_downloads'] : false;

        if ($isStatusChange && $downloads) {
            $downloads = json_decode($downloads);
            foreach ($downloads as $download) {
                if ($download->source = 'status_change') {
                    // break import, because initial import already exists
                    $data = array(
                        'error' => array(
                            'errorMessage' => 'Label existiert bereits!',
                            'errorMessageExtended' => ''
                        )
                    );

                    $this->View()->assign([
                        'data' => $data,
                        'success' => false,
                    ]);
                }
                return;
            }
        }

        if ($labelType == 'RETURN_LABEL') {
            $isReturnLabel = true;
        }

        try {
            $response = $gf->importShipment($orderID, $isReturnLabel, $customsDescription);
            if (get_class($response) === 'Exception') {
                throw $response;
            } else if (is_array($response) && $response['isMessage']) {
                $this->View()->assign([
                    'data' => $response,
                    'success' => false,
                ]);
                return;
            }

        } catch (GreenFieldResponseException $e) {
            // quickfix:  important: these kind of errors should never happen
            // todo
            //$response = json_decode($e->getResponse()->getBody())->error->message;
            $data = array(
                'error' => array(
                    'errorMessage' => json_decode($e->getResponse()->getBody())->error->message,
                    'errorMessageExtended' => json_decode($e->getResponse()->getBody())->error->extendedMessage
                )
            );

            $this->View()->assign([
                'data' => $data,
                'success' => false,
            ]);

            return;
        } catch (\Exception $e) {
            $data = array(
                'error' => array(
                    'errorMessage' => $e->getMessage(),
                    'errorMessageExtended' => ''
                )
            );
            $this->View()->assign([
                'data' => $data,
                'success' => false,
            ]);

            return;
        }

        $success = true;

        if ($response->getRawResponse()->getResponseCode() != '200') {
            $success = false;
        }

        // breaking change, post sometimes return 2 tracking codes for 1 document (there are BOTH labels in document)
        //$shipment = end($response->data->importShipmentResult);

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
                $orderAttributes = $gf->getOrderAttributes($orderID);

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

                $isStatusChange ? $source = 'status_change' : $source = 'additional_label';

                $labelDownload = array(
                    'documentid' => $documentId,
                    'label_data' => array(
                        'date' => $date,
                        'type' => $isReturnLabel ? 'RETURN_LABEL' : 'SHIPPING_LABEL',
                        'source' => $source,
                        'downloaded' => false,
                        'tracking_code' => $trackingCode
                    )
                );

                $labelsDownloadsList = json_decode($labelsDownloadsList, true);

                // if status change replace first tracking number, else just add it
                $existingTrackingCodes = array_map('trim', explode(';', $order->getTrackingCode()));
                $existingTrackingCodes[] = $trackingCode;
                $existingTrackingCodes = implode('; ', $existingTrackingCodes);
                $order->setTrackingCode($existingTrackingCodes);

                // manipulate order attribute - first record should always be label,
                // created by status change
                if (count($labelsDownloadsList) > 0) {
                    $firstLabel = $labelsDownloadsList[0];

                    if ($firstLabel->label_data->source == 'status_change' && $isStatusChange) {
                        $labelsDownloadsList[0] = $labelDownload;
                    } else {
                        $labelsDownloadsList[] = $labelDownload;
                    }
                } else {
                    $labelsDownloadsList[] = $labelDownload;
                }

                $orderAttributes['post_labels_downloads'] = json_encode($labelsDownloadsList);
                $attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');
                $attributePersister->persist($orderAttributes, 's_order_attributes', $orderID);

                try {
                    Shopware()->Models()->persist($order);
                    Shopware()->Models()->flush();
                } catch (Exception $e) {
                    $this->View()->assign([
                        'data' => $e->getMessage(),
                        'success' => false,
                    ]);
                    return;
                }
            }
        }

        $this->View()->assign([
            'data' => $response,
            'success' => $success,
        ]);
    }

    public function cancelShipmentAction()
    {
        $gf = $this->container->get("post_label_center.greenfield_service");
        $success = true;
        $documentId = $this->Request()->getPost('documentId');
        $orderId = $this->Request()->getPost('orderId');

        if (!$documentId) {
            $this->View()->assign([
                'data' => [],
                'success' => false,
            ]);
            return;
        }

        $order = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderId);
        $orderNumber = $order->getNumber();
        $shopId = $order->getShop()->getId();
        $dispatch = $order->getDispatch();
        $dispatchId = $dispatch->getId();
        $overriddenConfig = false;

        $cancelDocuments = array(
            'documentIds' => array($documentId),
            'orderId' => $orderNumber,
            'shopId' => $shopId
        );

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

        if ($overriddenConfig) {
            $gf->overrideAuth($overriddenConfig);
        }

        try {
            $response = $gf->cancelShipment($cancelDocuments);

        } catch (GreenFieldResponseException $e) {
            $response = json_decode($e->getResponse()->getBody())->error->message;
            $this->View()->assign([
                'data' => $response,
                'success' => false,
            ]);

            return;

        } catch (\Exception $e) {
            $data = array(
                'error' => array(
                    'errorMessage' => $e->getMessage(),
                    'errorMessageExtended' => ''
                )
            );
            $this->View()->assign([
                'data' => $data,
                'success' => false,
            ]);

            return;
        }

        $rawResponse = $response->getRawResponse();
        $responseBody = json_decode($rawResponse->getBody());

        if ($rawResponse->getResponseCode() != '200') {
            $errors = array(
                'error' => array(
                    'errorMessage' => $responseBody->error->message,
                    'errorMessageExtended' => $responseBody->error->extendedMessage
                )
            );
            $this->View()->assign([
                'data' => $errors,
                'success' => false,
            ]);

            return;
        }

        if (!$responseBody->data->validCanceledShipment) {
            $success = false;
        }

        $this->View()->assign([
            'data' => '',
            'success' => $success,
        ]);
    }

}
