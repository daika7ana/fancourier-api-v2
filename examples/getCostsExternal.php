<?php

declare(strict_types=1);

/*
 * GetCostsExternal - estimate the price of an international (export) shipment.
 *
 * Endpoint: GET reports/awb/external-tariff
 * Request:  Fancourier\Request\GetCostsExternal
 * Response: Fancourier\Response\GetCostsExternal
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetCostsExternal();

/*
 * GetCostsExternal request inputs:
 *   ->setDeliveryMode($mode)     // DeliveryMode enum or "rutier"/"aerian";
 *                                // available modes per country come from getCountries.php
 *   ->setDocumentType($type)     // DocumentType enum or "document"/"non document"
 *   ->setSenderCity($city)       ->setSenderCounty($county)
 *   ->setCountry($country)
 *   ->setEnvelopes($envelopes)   ->setParcels($parcels)
 *   ->setWeight($weight)         // kg
 *   ->setLength($length) ->setWidth($width) ->setHeight($height)  // cm
 *   ->setDeclaredValue($value)
 *   ->setService($service)       // default "Export"
 */

$request
    ->setParcels(1)
    ->setWeight(1)
    ->setWidth(10)
    ->setHeight(5)
    ->setLength(10)
    ->setSenderCounty('Arad')
    ->setSenderCity('Aciuta')
    ->setCountry('Moldova')
    ->setDeliveryMode(Fancourier\Enums\DeliveryMode::Rutier);

// --- send ------------------------------------------------------------------

$response = $fan->getCostsExternal($request);

/*
 * GetCostsExternal response getters:
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
