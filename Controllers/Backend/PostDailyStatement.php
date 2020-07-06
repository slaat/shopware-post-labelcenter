<?php

use Shopware\Components\CSRFWhitelistAware;
use Acl\GreenField\Exceptions\GreenFieldResponseException;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class Shopware_Controllers_Backend_PostDailyStatement extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    public function getDailyStatementAction()
    {
        $gf = $this->container->get("post_label_center.greenfield_service");
        try {
            $response = $gf->performEndOfDay();
        } catch (Exception $e) {
            $response = json_decode($e->getResponse()->getBody());
            $errors = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->extendedMessage
                )
            );

            $this->View()->assign([
                'success' => false,
                'data' => $errors,
            ]);
           return;
    }

        // @todo: need to check for errors here
        $pdfContent = json_decode($response->getContent());
        $date = $pdfContent->data->date ? date("d-m-Y", strtotime($pdfContent->data->date)) : date("d-m-Y", time());
        $pdfContent = base64_decode($pdfContent->data->pdfData); // pdf may be different, depending on what the API sends back

        if ($gf->isDataImportOnly()) {
            $this->View()->assign([
                'success' => true,
                'data' => array('dataimportonly' => true)
            ]);

            return;
        }

        if (!$pdfContent) {
            //todo show error message
            $errors = array(
                'error' => array(
                    'errorMessage' => 'No Pdf content',
                    'errorMessageExtended' => 'There is no Pdf data for your request'
                )
            );

            $this->View()->assign([
                'success' => false,
                'data' => $errors,
            ]);

            return;
        }
            $this->View()->assign([
                'success' => true,
                'data' => array(),
            ]);

            return;
    }

    public function loadDailyStatementAction()
    {
        $gf = $this->container->get("post_label_center.greenfield_service");
        $timestamp = $this->Request()->getParam('timestamp');
        $date = new DateTime();
        $date->setTimestamp($timestamp/1000);

        try {
            $response = $gf->performEndOfDay($date);
        } catch (Exception $e) {
            $response = json_decode($e->getResponse()->getBody());
            $errors = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->extendedMessage,
                    //todo - remove it later
                    'errorCodeBackend'  => 'Für diese Sendung sind noch keine Tracking Daten verfügbar.'
                )
            );
            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }
        $response = json_decode($response->getRawResponse()->getBody());
        $singleResponse = $response->data[0];
        // todo - consult with others
        $response = $singleResponse;
        $updatedOn = date("d-m-Y", time());
         // @todo: need to check for errors here
        $fileName = "Tagesabschluss-".$updatedOn.".pdf"; // filename may be different, depending on what the API sends back
        $pdfContent = base64_decode($response->fileContent); // pdf may be different, dependong on what the API sends back

        if (!$pdfContent) {
            //todo show error message
            die("no content");
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $response = $this->Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $fileName.'"');
        $response->setHeader('Expires', '0');
        $response->setHeader('Cache-Control', 'must-revalidate');
        $response->setHeader('Pragma', 'public');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', strlen($pdfContent));
        $response->sendHeaders();
        echo $pdfContent;
        exit;
    }

    //todo: do we need list
    public function loadDailyStatementListAction()
    {
        // todo call webservice
        $gf = $this->container->get("post_label_center.greenfield_service");
        $date = new \DateTime();
        $byClick = $this->Request()->getParam('byClick');

        try {
            $response = $gf->performEndOfDay($date);
        } catch (GreenFieldResponseException $e) {
            $response = json_decode($e->getResponse()->getBody());

            // hack for initial load
            if (!isset($byClick)) {
                $this->View()->assign([
                    "success" => true,
                    "data" => [],
                    "total" => 0
                ]);
                return;
            }
            $errors = array(
                'error' => array(
                    'errorMessage' => $response->error->message,
                    'errorMessageExtended' => $response->error->extendedMessage,
                    //todo - remove it later
                    'errorCodeBackend'  => 'Für diese Sendung sind noch keine Tracking Daten verfügbar.'
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
                    'errorMessageExtended' =>  $e->getMessage(),
                    //todo - remove it later
                    'errorCodeBackend'  => 'Tagesabschluss Exception.'
                )
            );
            $this->View()->assign([
                "success" => false,
                "data" => $errors,
                "total" => count($errors)
            ]);

            return;
        }

        $success = true;

        if ($response->getRawResponse()->getResponseCode() != '200') {
            $success = false;
        }

        $data = array();
        foreach ( $response->data as $dailyStatement ) {
            $statement = array();
            $dateTime = new DateTime();
            $dateTime->setTimestamp($dailyStatement->createdOn/1000);
            $date = $dateTime->format('d.m.Y H:i:s');
            $statement['id'] = $dailyStatement->id;
            $statement['name'] = "Tagesabschluss";
            $statement['date'] = $date;
            $statement['timestamp'] = $dailyStatement->createdOn;
            $data[] = $statement;
        }

        $this->View()->assign([
            'success' => $success,
            'data' => $data,
            'total' => 2
        ]);
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'getDailyStatement',
            'loadDailyStatement',
            'loadDailyStatementList'
        ];
    }
}
