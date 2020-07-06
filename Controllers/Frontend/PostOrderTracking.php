<?php
use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class Shopware_Controllers_Frontend_PostOrderTracking extends Enlight_Controller_Action
{
    protected $logger;

    /**
     * GreenField SDK Library Service
     *
     * @var \PostLabelCenter\Components\GreenfieldService
     */
    protected $gf;

    public function preDispatch()
    {
        $this->logger = Shopware()->PluginLogger();
        $this->gf = $this->container->get("post_label_center.greenfield_service");
    }

    public function getTrackingStatusAjaxAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->request()->getParam('orderid');
        $trackingCode = $this->request()->getParam('trackingcode');
        $trackingData = $this->gf->getTrackingData($orderNumber, $trackingCode);
        $this->Response()->setHeader("Content-Type", "application/json");
        $this->Response()->appendBody(json_encode($trackingData));
    }
}
