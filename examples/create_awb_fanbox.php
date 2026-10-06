<?php

declare(strict_types=1);

/*
 * CreateAwbFanbox - create an internal AWB delivered to a FANBox locker.
 *
 * Endpoint: POST intern-awb
 * Request:  Fancourier\Request\CreateAwb
 * Response: Fancourier\Response\CreateAwb
 *
 * This is the internal CreateAwb flow with a locker service and a PUDO point.
 * See create_awb.php for the full AwbIntern reference; only the FANBox-specific
 * parts are repeated here.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- build the shipment ----------------------------------------------------

$awb = new Fancourier\Objects\AwbIntern();
$awb
    ->setService('FANBox')                                // "FANBox", "FANBox Cont Colector",
                                                          // "CollectPoint" or "CollectPoint Cont Colector"
    ->setPaymentType(Fancourier\Enums\PaymentType::Expeditor) // or PaymentType::Destinatar
    ->setParcels(1)
    ->setWeight(1)                                        // kg
    ->setDeclaredValue(1000)
    ->setSizes(10, 5, 1)                                  // cm; or setLength()/setHeight()/setWidth()
    ->setNotes('testing notes')
    ->setContents('SKU-1, SKU-2')
    ->setRecipientName('John Ivy')
    ->setPhone('0723000000')
    ->setCounty('Tulcea')
    ->setCity('Tulcea')
    ->setPickupLocation('F1011137')                       // FANBox/Paypoint id (see getPudo.php)
    ->setStreet('Str. Babadag')                           // must match the PUDO point address
    ->setNumber('1')                                      // must match the PUDO point address
    ->addOption('W')                                      // locker option: "W" = drop-off, "V" = pickup
    ->addOption('X');                                     // ePOD

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\CreateAwb();
$request->addAwb($awb);

/*
 * CreateAwb request methods:
 *   ->addAwb(AwbIntern $awb)         // add a shipment (repeatable)
 *   ->resetAwbs()                    // drop all added shipments
 */

// --- send ------------------------------------------------------------------

$response = $fan->createAwb($request);

/*
 * CreateAwb response getters:
 *   ->getData()   // raw API payload
 *   ->getAll()    // the AwbIntern objects, updated with the result
 */

if ($response->isOk()) {
    var_dump($response->getData());

    $al = $response->getAll();
    echo 'Count: ' . count($al) . '<br />';
    foreach ($al as $awbr) {
        if ($awbr->hasErrors()) {
            print_r($awbr->getErrors());
        } else {
            echo 'AWB: ' . $awbr->getAwb() . '<br />';
            print_r($awbr->getDetails());
            echo '<hr />';
        }
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The AwbIntern object is used both to build the request and to read the
 * response. Its full setter/getter list is documented in create_awb.php.
 */
