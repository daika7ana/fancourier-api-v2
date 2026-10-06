<?php

declare(strict_types=1);

/*
 * GetBranches - list FAN Courier branches.
 *
 * Endpoint: GET reports/branches
 * Request:  Fancourier\Request\GetBranches
 * Response: Fancourier\Response\GetBranches
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetBranches();

/*
 * GetBranches request inputs:
 *   ->setCity($city)
 *   ->setCounty($county)
 *
 * Note: the API documents both as optional, but sending them does not appear
 * to change the response.
 */

$request
    ->setCity('Braila')
    ->setCounty('Braila');

// --- send ------------------------------------------------------------------

$response = $fan->getBranches($request);

/*
 * GetBranches response getters:
 *   ->getData()   // raw API data as an array
 *   ->getAll()    // map of Branch objects, keyed by id
 *   ->get($id)    // one Branch object, or null when missing
 */

if ($response->isOk()) {
    echo 'Total: ' . count($response->getData()['data']);
    echo '<pre>';
    print_r($response->getAll());
    echo '<hr />';
    print_r($response->getData());
    echo '</pre>';
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The Branch object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getBank()
 *   ->getBankAccount()
 *   ->getEmail()
 *   ->getPhone()
 *   ->getSecondaryPhone()
 *   ->getContactPerson()
 *   ->getCounty()
 *   ->getCity()
 *   ->getCountyId()
 *   ->getCityId()
 *   ->getStreet()
 *   ->getStreetNo()
 *   ->getPostalCode()
 *   ->getBuilding()
 *   ->getEntrance()
 *   ->getFloor()
 *   ->getApartment()
 */
