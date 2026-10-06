<?php

declare(strict_types=1);

/*
 * DeleteCourierOrder - cancel a courier pickup order.
 *
 * Endpoint: DELETE order
 * Request:  Fancourier\Request\DeleteCourierOrder
 * Response: Fancourier\Response\DeleteCourierOrder
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\DeleteCourierOrder();

/*
 * DeleteCourierOrder request inputs:
 *   ->setOrder($orderId)
 */

$request->setOrder('18680725');

// --- send ------------------------------------------------------------------

$response = $fan->deleteCourierOrder($request);

/*
 * DeleteCourierOrder response getters:
 *   ->getData()   // true when the order was deleted, false on error
 */

if ($response->isOk()) {
    var_dump($response->getData());
} else {
    var_dump($response->getErrorMessage());
}
