<?php

declare(strict_types=1);

/*
 * CreateCourierOrder - schedule a courier pickup.
 *
 * Endpoint: POST order
 * Request:  Fancourier\Request\CreateCourierOrder
 * Response: Fancourier\Response\CreateCourierOrder
 *
 * A "Standard" order only needs the package details, pickup date and hours.
 * The "Express Loco ..." order types additionally require the recipient block
 * (see the commented setters below).
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\CreateCourierOrder();

/*
 * CreateCourierOrder request inputs:
 *   ->setAwb($awb)                            // optional existing AWB number
 *   ->setEnvelopes($envelopes)
 *   ->setParcels($parcels)
 *   ->setWeight($weight)                      // kg
 *   ->setSizes($length, $height, $width)      // cm; or setLength()/setHeight()/setWidth()
 *   ->setOrderType($orderType)                // OrderType enum or "Standard"/"Express Loco ..."
 *   ->setPickupDate($date)                    // YYYY-mm-dd
 *   ->setPickupHours($firstHour, $lastHour)   // at least 2 hours apart
 *   ->setNotes($notes)
 *
 * Recipient block (required for non-Standard order types):
 *   ->setRecipientName() ->setContactPerson() ->setPhone() ->setAltPhone() ->setEmail()
 *   ->setCounty() ->setCity() ->setStreet() ->setNumber() ->setPostalCode()
 *   ->setBuilding() ->setEntrance() ->setFloor() ->setApartment()
 */

$request
    ->setOrderType(Fancourier\Enums\OrderType::Standard)
    ->setParcels(1)
    ->setEnvelopes(1)
    ->setWeight(1)
    ->setSizes(10, 5, 1)
    ->setNotes('testing notes')
    ->setPickupDate(date('Y-m-d', time() + 86400))
    ->setPickupHours('09:00', '16:30');

// For an "Express Loco ..." order, also set the recipient:
//
// $request
//     ->setOrderType(Fancourier\Enums\OrderType::ExpressLoco2h)
//     ->setRecipientName('John Ivy')
//     ->setContactPerson('John Ivy')
//     ->setPhone('0723000000')
//     ->setCounty('Arad')
//     ->setCity('Aciuta')
//     ->setStreet('Str Lunga')
//     ->setNumber('1');

// --- send ------------------------------------------------------------------

$response = $fan->createCourierOrder($request);

/*
 * CreateCourierOrder response getters:
 *   ->getData()   // raw API payload
 *   ->getId()     // id of the created order
 */

if ($response->isOk()) {
    var_dump($response->getData());
    echo 'Order id: ' . $response->getId() . '<br />';
} else {
    var_dump($response->getErrorMessage());
}
