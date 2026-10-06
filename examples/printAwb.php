<?php

declare(strict_types=1);

/*
 * PrintAwb - render AWB labels.
 *
 * Endpoint: GET awb/label
 * Request:  Fancourier\Request\PrintAwb
 * Response: Fancourier\Response\PrintAwb
 *
 * Returns the label as a PDF (default), ZPL for label printers, or HTML.
 * PDF and ZPL are mutually exclusive. Use setDpi() together with ZPL, and
 * setSize() for the page format.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\PrintAwb();

/*
 * PrintAwb request methods:
 *   ->addAwb($awb)            // add an AWB to print (repeatable)
 *   ->setAwb($awb)            // alias for addAwb()
 *   ->setPdf($active)         // default true; disables ZPL
 *   ->setZpl($active)         // Zebra label output; disables PDF
 *   ->setDpi($dpi)            // ZPL only; -1 disables
 *   ->setHtml($active)        // HTML output (neither PDF nor ZPL)
 *   ->setSize($format)        // LabelFormat enum or "A4"/"A5"/"A6"
 *   ->setLang($language)      // Language enum or "ro"/"en"
 */

$request
    // ->setZpl(true)->setDpi(203)
    ->setSize(Fancourier\Enums\LabelFormat::A5)
    ->setLang(Fancourier\Enums\Language::Ro)
    ->addAwb('2326300120204');

// --- send ------------------------------------------------------------------

$response = $fan->printAwb($request);

/*
 * PrintAwb response getters:
 *   ->getData()   // label contents (PDF, ZPL or HTML)
 */

if ($response->isOk()) {
    echo $response->getData();
} else {
    var_dump($response->getErrorMessage());
}
