<?php

declare(strict_types=1);

/*
 * GetPudo - look up FAN Courier PUDO (pickup/drop-off) points.
 *
 * Endpoint: GET reports/pickup-points
 * Request:  Fancourier\Request\GetPudo
 * Response: Fancourier\Response\GetPudo
 *
 * Two ways to query:
 *   1. list the points of a type with setType(...), read them with getAll();
 *   2. fetch a single point by id with setId(...), read it with get().
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- 1. list pickup points by type -----------------------------------------

$request = new Fancourier\Request\GetPudo();

/*
 * GetPudo request inputs:
 *   ->setType($pudoType)   // PudoType enum or "fanbox"/"paypoint"/"office"
 *   ->setId($pudoId)       // single point; when set, the type is ignored
 */

$request->setType(Fancourier\Enums\PudoType::Fanbox);

$response = $fan->getPudo($request);

/*
 * GetPudo response getters:
 *   ->getData()   // raw API data as an array
 *   ->getAll()    // map of Pudo objects, keyed by id
 *   ->get($id)    // one Pudo object (null argument returns the only entry)
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

// --- 2. fetch a single pickup point by id ----------------------------------

$request = new Fancourier\Request\GetPudo();
$request->setId('S125'); // when setId is used, the type is ignored

$response = $fan->getPudo($request);

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->get(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The Pudo object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getRoutingLocation()
 *   ->getDescription()
 *   ->getLatitude()
 *   ->getLongitude()
 *   ->getAddress()
 *   ->getSchedule()
 *   ->getDrawer()
 *   ->getPhones()
 *   ->getEmail()
 *   ->getHighDemand()
 *   ->getPaymentMethods()
 *   ->getArray()
 */
