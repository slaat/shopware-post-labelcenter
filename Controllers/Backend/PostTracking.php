<?php

use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class Shopware_Controllers_Backend_PostTracking extends Shopware_Controllers_Backend_ExtJs
{
    public function getTrackingAction()
    {
        $orderNumber = $this->Request()->getParam('orderNumber');
        $trackingCode = $this->Request()->getParam('trackingCode');

        if (!$trackingCode) {
            $errors = array(
                'error' => array(
                    'errorMessage' => 'No trackingcode for order',
                    'errorMessageExtended' => '',
                    'errorCodeBackend'  => 'no data'
                )
            );

            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }

        $gf = $this->container->get("post_label_center.greenfield_service");
        $trackingData = $gf->getTrackingData($orderNumber, $trackingCode);

        $this->View()->assign([
            'data' => (array)($trackingData),
            'success' => true,
        ]);
    }
}
