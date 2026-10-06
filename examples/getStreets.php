<?php

declare(strict_types=1);

/*
 * GetStreets - list the streets of a city.
 *
 * Endpoint: GET reports/streets
 * Request:  Fancourier\Request\GetStreets
 * Response: Fancourier\Response\GetStreets
 *
 * The response is paginated; the loop below walks every page.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetStreets();

/*
 * GetStreets request inputs:
 *   ->setCounty($county)
 *   ->setCity($city)
 *   ->setPage($page)
 *   ->setPerPage($perPage)   // default 1000
 */

$request
    ->setCity('Braila')
    ->setCounty('Braila')
    ->setPerPage(1000);

// --- send ------------------------------------------------------------------

$response = $fan->getStreets($request);

/*
 * GetStreets response getters:
 *   ->getData()          // raw API data as an array
 *   ->getAll()           // map of Street objects, keyed by id
 *   ->getTotal() ->getPerPage() ->getCurrentPage() ->getTotalPages()
 */

if ($response->isOk()) {
    // walk every page of results
    while ($response->isOk() && ($response->getCurrentPage() <= $response->getTotalPages())) {
        echo 'Total: ' . $response->getTotal() . '<br />';
        echo 'Page: ' . $response->getCurrentPage() . '<br />';
        echo 'Results per page: ' . $response->getPerPage() . '<br />';
        echo 'Total pages: ' . $response->getTotalPages() . '<br />';
        echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
        echo '<hr />';

        if ($response->getCurrentPage() >= $response->getTotalPages()) {
            break;
        }

        $request->setPage($response->getCurrentPage() + 1);
        $response = $fan->getStreets($request);
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The Street object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getType()
 *   ->getCounty()
 *   ->getCity()
 *   ->hasZipCode($zipCode)
 *   ->getDetails($zipCode)
 *   ->getArray()
 */
