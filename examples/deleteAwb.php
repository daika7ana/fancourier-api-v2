<?php

declare(strict_types=1);

/*
 * DeleteAwb - delete an existing AWB.
 *
 * Endpoint: DELETE awb
 * Request:  Fancourier\Request\DeleteAwb
 * Response: Fancourier\Response\DeleteAwb
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\DeleteAwb();

/*
 * DeleteAwb request inputs:
 *   ->setAwb($awb)
 */

$request->setAwb('2339300120181');

// --- send ------------------------------------------------------------------

$response = $fan->deleteAwb($request);

/*
 * DeleteAwb response getters:
 *   ->getData()   // true when the AWB was deleted, false on error
 */

if ($response->isOk()) {
    var_dump($response->getData());
} else {
    var_dump($response->getErrorMessage());
}
