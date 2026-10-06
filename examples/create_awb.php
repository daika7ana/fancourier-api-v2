<?php

declare(strict_types=1);

/*
 * CreateAwb - create one or more internal (Romanian) AWBs.
 *
 * Endpoint: POST intern-awb
 * Request:  Fancourier\Request\CreateAwb
 * Response: Fancourier\Response\CreateAwb
 *
 * Build one AwbIntern per shipment, add them to the request, then read each
 * AWB back from the response (the response reuses the same objects).
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- build each shipment ---------------------------------------------------

$awb = new Fancourier\Objects\AwbIntern();
$awb
    ->setService('Cont Colector')                                     // cash on delivery service
    ->setPaymentType(Fancourier\Enums\PaymentType::Expeditor)         // or PaymentType::Destinatar
    ->setParcels(1)
    ->setWeight(1)                                                    // kg
    ->setReimbursement(199.99)                                         // cash on delivery amount
    ->setDeclaredValue(1000)
    ->setSizes(10, 5, 1)                                              // cm; or setLength()/setHeight()/setWidth()
    ->setNotes('testing notes')
    ->setContents('SKU-1, SKU-2')
    ->setRecipientName('John Ivy')
    ->setPhone('0723000000')
    ->setCounty('Arad')
    ->setCity('Aciuta')
    ->setStreet('Str Lunga')
    ->setNumber('1')
    ->addOption('S')                                                  // Saturday delivery
    ->addOption('X');                                                 // ePOD

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\CreateAwb();

/*
 * CreateAwb request methods:
 *   ->addAwb(AwbIntern $awb)         // add a shipment (repeatable)
 *   ->resetAwbs()                    // drop all added shipments
 *   ->setPlatformId($platformId)     // only if FAN Courier provided one
 */

$request->addAwb($awb);

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
 * response (it is updated with the API result).
 *
 * Input setters:
 *   ->setService($service)               ->setBank($bank)               ->setIban($iban)
 *   ->setEnvelopes($envelopes)           ->setParcels($parcels)         ->setWeight($weight)
 *   ->setReimbursement($cod)             // cash on delivery
 *   ->setCurrency($currency)             // optional
 *   ->setDeclaredValue($value)           ->setPaymentType($paymentType)
 *   ->setRefund($refund)                 ->setReturnPayment($paymentType)
 *   ->setNotes($notes)                   ->setContents($contents)
 *   ->setSizes($length, $height, $width) // cm; or ->setLength() ->setHeight() ->setWidth()
 *   ->setCostCenter($costCenter)
 *   ->addOption($option)                 // add one option letter
 *   ->setOptions($options)               // replace all options with a single string, e.g. "SX"
 *   ->resetOptions()                     ->getOptions()   // getOptions() reads the current list
 *   ->setUITCode($uitCode)               // e-Transport unique transport id
 *   ->setRecipientName() ->setContactPerson() ->setPhone() ->setAltPhone() ->setEmail()
 *   ->setCounty() ->setCity() ->setStreet() ->setNumber() ->setPostalCode()
 *   ->setBuilding() ->setEntrance() ->setFloor() ->setApartment()
 *   ->setPickupLocation($pudoId)         // recipient picks the parcel up from a PUDO point
 *   ->setDropOffLocation($pudoId)        // sender leaves the parcel at a PUDO point
 *   ->setSenderName() ->setSenderContactPerson() ->setSenderPhone() ->setSenderAltPhone() ->setSenderEmail()
 *   ->setSenderCounty() ->setSenderCity() ->setSenderStreet() ->setSenderNumber() ->setSenderPostalCode()
 *   ->setSenderBuilding() ->setSenderEntrance() ->setSenderFloor() ->setSenderApartment()
 *
 * Non-EU parcel tax (set all four together):
 *   ->setIsValueUnderThreshold($bool) ->setCountryCode($code) ->setVatId($vatId) ->setCompany($company)
 *
 * Result getters (available after the response):
 *   ->hasErrors() ->getErrors() ->getAwb() ->getDetails()
 */
