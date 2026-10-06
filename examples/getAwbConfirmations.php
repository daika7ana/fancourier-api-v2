<?php

declare(strict_types=1);

/*
 * GetAwbConfirmations - download proof-of-delivery images for delivered AWBs.
 *
 * Endpoint: GET reports/get-awb-confirmations
 * Request:  Fancourier\Request\GetAwbConfirmations
 * Response: Fancourier\Response\GetAwbConfirmations
 *
 * The API returns a ZIP archive with one JPEG per AWB. AWBs without a
 * confirmation (e.g. test AWBs) simply have no image in the archive.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetAwbConfirmations();

/*
 * GetAwbConfirmations request inputs:
 *   ->addAwb($awb)
 *   ->setAwb($awb)   // alias for addAwb()
 *   ->resetAwbs()
 */

$request
    ->addAwb('7000011994717')
    ->addAwb('7000012005411');

// --- send ------------------------------------------------------------------

$response = $fan->getAwbConfirmations($request);

/*
 * GetAwbConfirmations response getters:
 *   ->getData()             // raw response
 *   ->getRAWbytes()         // the ZIP archive as a string of bytes
 *   ->getLength()           // archive size in bytes
 *   ->saveToFile($filename) // write the archive to disk; returns bytes written or false
 */

if ($response->isOk()) {
    echo 'ZIP size in bytes: ' . $response->getLength() . '<br />';
    if ($response->saveToFile('./example.zip')) {
        echo 'Saved to example.zip';
    } else {
        echo 'Failed saving file';
    }
} else {
    var_dump($response->getErrorMessage());
}
