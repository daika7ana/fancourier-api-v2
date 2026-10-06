<?php

declare(strict_types=1);

/*
 * GetCountiesExternal - list the counties of a foreign country.
 *
 * Endpoint: GET reports/external-counties
 * Request:  Fancourier\Request\GetCountiesExternal
 * Response: Fancourier\Response\GetCountiesExternal
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetCountiesExternal();

/*
 * GetCountiesExternal request inputs:
 *   ->setCountry($country)
 */

$request->setCountry('Moldova');

// --- send ------------------------------------------------------------------

$response = $fan->getCountiesExternal($request);

/*
 * GetCountiesExternal response getters:
 *   ->getData()   // raw API data as an array
 *   ->getAll()    // map of CountyExternal objects, keyed by id
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The CountyExternal object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getCode()
 *   ->getCountry()
 */
