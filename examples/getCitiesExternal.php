<?php

declare(strict_types=1);

/*
 * GetCitiesExternal - list the localities of a foreign county.
 *
 * Endpoint: GET reports/external-localities
 * Request:  Fancourier\Request\GetCitiesExternal
 * Response: Fancourier\Response\GetCitiesExternal
 *
 * The response is paginated; the loop below walks every page.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetCitiesExternal();

/*
 * GetCitiesExternal request inputs:
 *   ->setCountry($country)
 *   ->setCounty($county)
 *   ->setPage($page)
 *   ->setPerPage($perPage)   // default 100
 */

$request
    ->setCountry('Moldova')
    ->setCounty('Basarabeasca')
    ->setPerPage(100);

// --- send ------------------------------------------------------------------

$response = $fan->getCitiesExternal($request);

/*
 * GetCitiesExternal response getters:
 *   ->getData()          // raw API data as an array
 *   ->getAll()           // map of CityExternal objects, keyed by id
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
        $response = $fan->getCitiesExternal($request);
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The CityExternal object has the following functions:
 *   ->getId()
 *   ->getName()
 *   ->getCounty()
 *   ->getCountry()
 */
