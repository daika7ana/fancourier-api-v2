<?php

declare(strict_types=1);

/*
 * CreateAwbExternal - create international (export) AWBs.
 *
 * Endpoint: POST extern-awb
 * Request:  Fancourier\Request\CreateAwbExternal
 * Response: Fancourier\Response\CreateAwbExternal
 *
 * Note: only the "Export" service is accepted; the API rejects
 * "Export-Cont Colector" even though it is documented. For cash on delivery
 * set ->setReimbursement(...). setCurrency() is informational only; the API
 * derives the currency from the contract.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- build each shipment ---------------------------------------------------

$awb = new Fancourier\Objects\AwbExtern();
$awb
    ->setService('Export')
    ->setDeliveryMode(Fancourier\Enums\DeliveryMode::Rutier)          // "rutier" or "aerian"
    ->setDocumentType(Fancourier\Enums\DocumentType::Document)        // "document" or "non document"
    ->setBank('RAIFFEISEN BANK ROMANA')
    ->setIban('RO53RZBR0000060009520959')
    ->setParcels(1)
    ->setWeight(1)                                                    // kg
    ->setReimbursement(199.99)
    ->setCurrency('BGN')
    ->setDeclaredValue(1000)
    ->setSizes(10, 5, 1)                                              // cm; or setLength()/setHeight()/setWidth()
    ->setNotes('testing notes')
    ->setContents('SKU-1, SKU-2')

    ->setSenderName('John Ivy')
    ->setSenderPhone('0723000000')
    ->setSenderCounty('Arad')
    ->setSenderCity('Aciuta')
    ->setSenderStreet('Str Lunga')
    ->setSenderNumber('1')

    ->setRecipientName('John Ivy')
    ->setPhone('0723000000')
    ->setCountry('Bulgaria')
    ->setCounty('Sofia')
    ->setCity('Sofia')
    ->setStreet('ul. Ivan Denkoglu')
    ->setNumber('17')
    ->setBuilding('B9')
    ->setEntrance('69')
    ->setFloor('6')
    ->setApartment('9')
    ->setPostalCode('1000')
    ->addOption('S');

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\CreateAwbExternal();

/*
 * CreateAwbExternal request methods:
 *   ->addAwb(AwbExtern $awb)         // add a shipment (repeatable)
 *   ->resetAwbs()                    // drop all added shipments
 *   ->setPlatformId($platformId)     // only if FAN Courier provided one
 */

$request->addAwb($awb);

// --- send ------------------------------------------------------------------

$response = $fan->createAwbExternal($request);

/*
 * CreateAwbExternal response getters:
 *   ->getData()   // raw API payload
 *   ->getAll()    // the AwbExtern objects, updated with the result
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
            echo 'Errors: ' . print_r($awbr->getErrors(), 1) . '<br />';
            echo '<hr />';
        }
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The AwbExtern object is used both to build the request and to read the
 * response (it is updated with the API result).
 *
 * Input setters:
 *   ->setService($service)
 *   ->setDeliveryMode($mode)             // DeliveryMode enum or "rutier"/"aerian"
 *   ->setDocumentType($type)             // DocumentType enum or "document"/"non document"
 *   ->setBank($bank)                     ->setIban($iban)
 *   ->setEnvelopes($envelopes)           ->setParcels($parcels)       ->setWeight($weight)
 *   ->setSizes($length, $height, $width) // cm; or ->setLength() ->setHeight() ->setWidth()
 *   ->setReimbursement($cod)             // cash on delivery
 *   ->setCurrency($currency)             // optional
 *   ->setDeclaredValue($value)           ->setPaymentType($paymentType)
 *   ->setRefund($refund)                 ->setReturnPayment($paymentType)
 *   ->setNotes($notes)                   ->setContents($contents)     ->setCostCenter($costCenter)
 *   ->addOption($option)                 ->resetOptions()             ->getOptions()
 *   ->setUITCode($uitCode)               // e-Transport unique transport id
 *   ->setSenderName() ->setSenderContactPerson() ->setSenderPhone() ->setSenderAltPhone() ->setSenderEmail()
 *   ->setSenderCounty() ->setSenderCity() ->setSenderStreet() ->setSenderNumber() ->setSenderPostalCode()
 *   ->setSenderBuilding() ->setSenderEntrance() ->setSenderFloor() ->setSenderApartment()
 *   ->setRecipientName() ->setContactPerson() ->setPhone() ->setAltPhone() ->setEmail()
 *   ->setCountry() ->setCounty() ->setCity() ->setStreet() ->setNumber() ->setPostalCode()
 *   ->setBuilding() ->setEntrance() ->setFloor() ->setApartment()
 *
 * Result getters (available after the response):
 *   ->hasErrors() ->getErrors() ->getAwb()
 */
