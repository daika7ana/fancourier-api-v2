<?php

declare(strict_types=1);

/*
 * GetCounties - list the Romanian counties.
 *
 * Endpoint: GET reports/counties
 * Request:  Fancourier\Request\GetCounties (no inputs)
 * Response: Fancourier\Response\GetCounties
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- send ------------------------------------------------------------------

// GetCounties takes no request object and no inputs
$response = $fan->getCounties();

/*
 * GetCounties response getters:
 *   ->getData()               // raw API data as an array
 *   ->getAll()                // map of County objects, keyed by name
 *   ->getCounty($countyName)  // one County object, or false when missing
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The County object has the following functions:
 *   ->getId()
 *   ->getName()
 */
