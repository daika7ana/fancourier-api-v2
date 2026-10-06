<?php

declare(strict_types=1);

/*
 * GetBankTransfers - list the bank transfers for one day.
 *
 * Endpoint: GET reports/bank-transfers
 * Request:  Fancourier\Request\GetBankTransfers
 * Response: Fancourier\Response\GetBankTransfers
 *
 * The response is paginated; the loop below walks every page.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\GetBankTransfers();

/*
 * GetBankTransfers request inputs:
 *   ->setDate($date)         // YYYY-mm-dd; defaults to today
 *   ->setPage($page)         // defaults to 1
 *   ->setPerPage($perPage)   // default 100
 */

$request
    ->setDate('2023-11-20')
    ->setPerPage(100);

// --- send ------------------------------------------------------------------

$response = $fan->getBankTransfers($request);

/*
 * GetBankTransfers response getters:
 *   ->getData()          // raw API data as an array
 *   ->getAll()           // array of BankTransfer objects
 *   ->get($position)     // one BankTransfer object, or false when missing
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
        $response = $fan->getBankTransfers($request);
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The BankTransfer object has the following functions:
 *   ->getAwbNumber()
 *   ->getAwbDate()
 *   ->getReturnAwbNumber()
 *   ->getReimbursementAwbNumber()
 *   ->getAmountCollected()
 *   ->getContent()
 *   ->getTransferDate()
 *   ->getTransactionType()
 *   ->getTransactionDate()
 *   ->getRecipientName()
 *   ->getRecipientContactPerson()
 *   ->getRecipientCity()
 *   ->getSenderName()
 *   ->getSenderContactPerson()
 */
