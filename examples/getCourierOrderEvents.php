<?php

declare(strict_types=1);

/*
 * GetCourierOrderEvents - list the courier order event codes and their names.
 *
 * Endpoint: GET reports/order-events
 * Request:  Fancourier\Request\GetCourierOrderEvents
 * Response: Fancourier\Response\GetCourierOrderEvents
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetCourierOrderEvents();

/*
 * GetCourierOrderEvents request inputs:
 *   ->setLanguage($language)   // Language enum or "ro"/"en"
 */

$request->setLanguage(Fancourier\Enums\Language::Ro);

// --- send ------------------------------------------------------------------

$response = $fan->getCourierOrderEvents($request);

/*
 * GetCourierOrderEvents response getters:
 *   ->getData()                    // raw API data as an array
 *   ->getAll()                     // map of CourierOrderEvent objects, keyed by id
 *   ->getEvent($courierEventId)    // one CourierOrderEvent object, or false when missing
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The CourierOrderEvent object has the following functions:
 *   ->getId()
 *   ->getName()
 */
