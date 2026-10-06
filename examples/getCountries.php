<?php

declare(strict_types=1);

/*
 * GetCountries - list the countries served and their available delivery modes.
 *
 * Endpoint: GET reports/countries
 * Request:  Fancourier\Request\GetCountries (no inputs)
 * Response: Fancourier\Response\GetCountries
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- send ------------------------------------------------------------------

// GetCountries takes no request object and no inputs
$response = $fan->getCountries();

/*
 * GetCountries response getters:
 *   ->getData()                // raw API data as an array
 *   ->getAll()                 // map of Country objects, keyed by name
 *   ->getCountry($countryName) // one Country object, or false when missing
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The Country object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getCode()
 *   ->getShipping()
 *   ->hasAirShipping()
 *   ->hasLandShipping()
 */
