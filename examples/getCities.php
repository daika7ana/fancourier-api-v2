<?php

declare(strict_types=1);

/*
 * GetCities - list the localities of a Romanian county.
 *
 * Endpoint: GET reports/localities
 * Request:  Fancourier\Request\GetCities
 * Response: Fancourier\Response\GetCities
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetCities();

/*
 * GetCities request inputs:
 *   ->setCounty($county)
 */

$request->setCounty('Ilfov');

// --- send ------------------------------------------------------------------

$response = $fan->getCities($request);

/*
 * GetCities response getters:
 *   ->getData()              // raw API data as an array
 *   ->getAll()               // map of City objects, keyed by id
 *   ->getCity($cityName)     // one City object, or false when the name is missing
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The City object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getCounty()
 *   ->getAgency()
 *   ->getExtKm()
 */
