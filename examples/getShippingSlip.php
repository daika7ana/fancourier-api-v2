<?php

declare(strict_types=1);

/*
 * GetShippingSlip - list the shipping slips (borderouri) for one day.
 *
 * Endpoint: GET reports/awb
 * Request:  Fancourier\Request\GetShippingSlip
 * Response: Fancourier\Response\GetShippingSlip
 *
 * The response is paginated; the loop below walks every page.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetShippingSlip();

/*
 * GetShippingSlip request inputs:
 *   ->setDate($date)         // YYYY-mm-dd; defaults to today
 *   ->setPage($page)         // defaults to 1
 *   ->setPerPage($perPage)   // default 100
 */

$request
    ->setDate('2023-11-20')
    ->setPerPage(100);

// --- send ------------------------------------------------------------------

$response = $fan->getShippingSlip($request);

/*
 * GetShippingSlip response getters:
 *   ->getData()          // raw API data as an array
 *   ->getAll()           // array of ShippingSlip objects
 *   ->get($position)     // one ShippingSlip object, or false when missing
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
        $response = $fan->getShippingSlip($request);
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The ShippingSlip object has the following functions:
 *   ->getAwbNumber()
 *   ->getService()
 *   ->getServiceId()
 *   ->getWeight()
 *   ->getHeight()
 *   ->getWidth()
 *   ->getLength()
 *   ->getPayment()
 *   ->getReturnPayment()
 *   ->getReimbursement()
 *   ->getDeclaredValue()
 *   ->getNotes()
 *   ->getContents()
 *   ->getEnvelopes()
 *   ->getParcels()
 *   ->getDateTime()
 *   ->getCost()
 *   ->getCostCenter()
 *   ->getRefund()
 *   ->getCurrency()
 *   ->getRecipient()
 *   ->getSender()
 */
