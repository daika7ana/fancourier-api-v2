<?php

declare(strict_types=1);

/*
 * GetAwbEvents - list the AWB event codes and their names.
 *
 * Endpoint: GET reports/awb-events
 * Request:  Fancourier\Request\GetAwbEvents
 * Response: Fancourier\Response\GetAwbEvents
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetAwbEvents();

/*
 * GetAwbEvents request inputs:
 *   ->setLanguage($language)   // Language enum or "ro"/"en"
 */

$request->setLanguage(Fancourier\Enums\Language::Ro);

// --- send ------------------------------------------------------------------

$response = $fan->getAwbEvents($request);

/*
 * GetAwbEvents response getters:
 *   ->getData()            // raw API data as an array
 *   ->getAll()             // map of AwbEvent objects, keyed by id
 *   ->getEvent($eventId)   // one AwbEvent object, or false when missing
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The AwbEvent object has the following functions:
 *   ->getId()
 *   ->getName()
 */
