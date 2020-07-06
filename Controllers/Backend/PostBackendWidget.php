<?php

class Shopware_Controllers_Backend_PostBackendWidget extends Shopware_Controllers_Backend_ExtJs
{
    public function listAction()
    {
        $data =  array(
            array(
                'content' => 'Wo erhalte ich Unterstützung bei der Gestaltung meiner Sendungen?',
                'link' => 'https://www.post.at/footer_allgemein_haeufige_fragen.php#tab758',
                'date' => '16.03.2018'
            ),
            array(
                'content' => 'Informationen zum Paketversand mit Paket Standard oder Paket Premium sowie zum Weinpaket für bruchsicheren Weinversand lesen Sie hier.',
                'link' => 'https://www.post.at/geschaeftlich_versenden_paket_oesterreich.php',
                'date' => '15.03.2018'
            )
        );
        $this->View()->assign([
            'success' => true,
            'data'    => $data,
            'total' => 1
        ]);
    }
}
