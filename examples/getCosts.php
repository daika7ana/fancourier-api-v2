<?php

declare(strict_types=1);

/*
 * GetCosts - estimate the price of an internal (Romanian) shipment.
 *
 * Endpoint: GET reports/awb/internal-tariff
 * Request:  Fancourier\Request\GetCosts
 * Response: Fancourier\Response\GetCosts
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetCosts();

/*
 * GetCosts request inputs:
 *   ->setPaymentType($paymentType)   // "destinatar" (default) or "expeditor";
 *                                    // accepts PaymentType enum or the raw string
 *   ->setCity($city)                 ->setCounty($county)
 *   ->setSenderCity($city)           ->setSenderCounty($county)
 *   ->setEnvelopes($envelopes)       ->setParcels($parcels)
 *   ->setWeight($weight)             // kg
 *   ->setLength($length) ->setWidth($width) ->setHeight($height)  // cm
 *   ->setDeclaredValue($value)
 *   ->addOption($option)             // add one option letter
 *   ->setOptions($options)           // replace all options with one string
 *   ->resetOptions()
 *   ->setService($service)           // default "Standard"
 */

$request
    ->setParcels(1)
    ->setWeight(1)
    ->setCounty('Arad')
    ->setCity('Aciuta')
    ->setDeclaredValue(125);

// --- send ------------------------------------------------------------------

$response = $fan->getCosts($request);

/*
 * GetCosts response getters:
 *   ->getData()          // raw API data as an array
 *   ->getAllErrors()     // request errors, when the API still returned data
 *   ->getKmCost() ->getWeightCost() ->getInsuranceCost() ->getOptionsCost()
 *   ->getFuelCost() ->getCost() ->getCostVat() ->getCostTotal()
 */

if ($response->isOk()) {
    var_dump($response->getData());
    echo '<hr />';
    echo 'extraKmCost: ' . $response->getKmCost() . '<br />';
    echo 'weightCost: ' . $response->getWeightCost() . '<br />';
    echo 'insuranceCost: ' . $response->getInsuranceCost() . '<br />';
    echo 'optionsCost: ' . $response->getOptionsCost() . '<br />';
    echo 'fuelCost: ' . $response->getFuelCost() . '<br />';
    echo 'costNoVAT: ' . $response->getCost() . '<br />';
    echo 'vat: ' . $response->getCostVat() . '<br />';
    echo 'total: ' . $response->getCostTotal() . '<br />';
} else {
    var_dump($response->getErrorMessage());
    print_r($response->getAllErrors());
}
