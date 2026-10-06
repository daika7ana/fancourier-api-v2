<?php

declare(strict_types=1);

/*
 * TrackAwb - track one or more AWBs.
 *
 * Endpoint: GET reports/awb/tracking
 * Request:  Fancourier\Request\TrackAwb
 * Response: Fancourier\Response\TrackAwb
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\TrackAwb();

/*
 * TrackAwb request inputs:
 *   ->addAwb($awb)
 *   ->setAwb($awb)                 // alias for addAwb()
 *   ->resetAwbs()
 *   ->setLanguage($language)       // Language enum or "ro"/"en"
 */

$request
    ->addAwb('2339300120170')
    ->setLanguage(Fancourier\Enums\Language::Ro);

// --- send ------------------------------------------------------------------

$response = $fan->trackAwb($request);

/*
 * TrackAwb response getters:
 *   ->getData()            // raw API data as an array
 *   ->getAll()             // map of AwbTracker objects, keyed by AWB number
 *   ->getAwb($awbNo)       // one AwbTracker object, or false when missing
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The AwbTracker object has the following functions:
 *   ->getAwbNumber()
 *   ->getReturnAwbNumber()
 *   ->getRedirectionAwbNumber()
 *   ->getReimbursementAwbNumber()
 *   ->getOPODAwbNumber()
 *   ->getPaymentDate()
 *   ->getMessage()
 *   ->getContent()
 *   ->hasConfirmation()
 *   ->getConfirmation()
 *   ->getOTD()
 *   ->getEvents()
 *   ->getStatus()
 */
