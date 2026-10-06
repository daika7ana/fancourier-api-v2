<?php

declare(strict_types=1);

/*
 * GetServices - list the services available to the account.
 *
 * Endpoint: GET reports/services
 * Request:  Fancourier\Request\GetServices (no inputs)
 * Response: Fancourier\Response\GetServices
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- send ------------------------------------------------------------------

// GetServices takes no request object and no inputs
$response = $fan->getServices();

/*
 * GetServices response getters:
 *   ->getData()                 // raw API data as an array
 *   ->getAll()                  // map of Service objects, keyed by name
 *   ->getService($serviceName)  // one Service object, or false when missing
 *   ->hasService($serviceName)  // true when the service is available
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The Service object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getDescription()
 */
