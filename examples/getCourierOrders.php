<?php

declare(strict_types=1);

/*
 * GetCourierOrders - list the courier pickup orders placed for one day.
 *
 * Endpoint: GET reports/orders
 * Request:  Fancourier\Request\GetCourierOrders
 * Response: Fancourier\Response\GetCourierOrders
 *
 * The response is paginated; the loop below walks every page. Note that for
 * this endpoint the API's "total" is the number of pages, not the number of
 * items, so getTotal() and getTotalPages() return the same value.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetCourierOrders();

/*
 * GetCourierOrders request inputs:
 *   ->setDate($date)         // "dd-mm-YYYY" preferred; "YYYY-mm-dd" is converted automatically
 *   ->setPage($page)
 *   ->setPerPage($perPage)   // default 10
 */

$request
    ->setDate('24-11-2023')
    ->setPerPage(10);

// --- send ------------------------------------------------------------------

$response = $fan->getCourierOrders($request);

/*
 * GetCourierOrders response getters:
 *   ->getData()          // raw API data as an array
 *   ->getAll()           // map of CourierOrder objects, keyed by id
 *   ->get($id)           // one CourierOrder object, or false when missing
 *   ->getTotal()         // total number of pages (see note above)
 *   ->getPerPage() ->getCurrentPage() ->getTotalPages()
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
        $response = $fan->getCourierOrders($request);
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The CourierOrder object has the following functions:
 *   ->getId()
 *   ->getNumber()
 *   ->getStatus()
 *   ->getDate()
 *   ->getHour()
 *   ->getEnvelopes()
 *   ->getParcels()
 *   ->getWeight()
 *   ->getDimensions()
 *   ->getHeight()
 *   ->getLength()
 *   ->getWidth()
 *   ->getPickupDate()
 *   ->getPickupHours()
 *   ->getNotes()
 *   ->getType()
 *   ->getAwbs()
 *   ->getSender()
 */
