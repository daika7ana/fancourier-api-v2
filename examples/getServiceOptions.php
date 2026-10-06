<?php

declare(strict_types=1);

/*
 * GetServiceOptions - list the options available for one service.
 *
 * Endpoint: GET reports/service-options
 * Request:  Fancourier\Request\GetServiceOptions
 * Response: Fancourier\Response\GetServiceOptions
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetServiceOptions();

/*
 * GetServiceOptions request inputs:
 *   ->setService($serviceName)   // default "Standard"
 */

$request->setService('fanbox');

// --- send ------------------------------------------------------------------

$response = $fan->getServiceOptions($request);

/*
 * GetServiceOptions response getters:
 *   ->getData()                 // raw API data as an array
 *   ->getAll()                  // map of ServiceOption objects, keyed by code
 *   ->getOption($optionCode)    // one ServiceOption object, or false when missing
 *   ->hasOption($optionCode)    // true when the option exists for the service
 */

if ($response->isOk()) {
    print_r($response->getData());
    var_dump($response->getAll());
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The ServiceOption object has the following functions:
 *   ->getCode()
 *   ->getName()
 */
