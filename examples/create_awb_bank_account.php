<?php

declare(strict_types=1);

/*
 * CreateAwbBankAccount - attach bank account (IBAN) records to existing AWBs.
 *
 * Endpoint: POST awb-bank-account
 * Request:  Fancourier\Request\CreateAwbBankAccount
 * Response: Fancourier\Response\CreateAwbBankAccount
 *
 * The body is a bare JSON array (no wrapper object); add one {awb, iban}
 * record per AWB, then read back how many records the API inserted.
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\CreateAwbBankAccount();

/*
 * CreateAwbBankAccount request methods:
 *   ->addRecord($awb, $iban)     // append one record (repeatable)
 *   ->getRecords()               // current record list
 */

$request->addRecord('2339300120181', 'RO49AAAA1B31007593840000');

// --- send ------------------------------------------------------------------

$response = $fan->createAwbBankAccount($request);

/*
 * CreateAwbBankAccount response getters:
 *   ->isOk()        // true when the API accepted the records
 *   ->getInserted() // number of inserted records
 *   ->getMessage()  // API success message
 *   ->getData()     // raw API payload
 */

if ($response->isOk()) {
    echo 'Inserted: ' . $response->getInserted() . '<br />';
    echo $response->getMessage() . '<br />';
} else {
    var_dump($response->getErrorMessage());
}
