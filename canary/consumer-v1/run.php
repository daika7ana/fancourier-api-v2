<?php

/**
 * Fixture canary consumer — a v1-style caller of the FAN Courier client.
 *
 * This file intentionally uses the pre-2.0 snake_case surface so the consumer
 * codemod (rector.php) has something to migrate. It is dependency-free and
 * network-free: every call below is local (options/headers/pack()/getters), no
 * send() and no HTTP.
 *
 * Run from the repo root or after `cp -r canary/consumer-v1 build/canary`:
 * both layouts resolve vendor/ two levels up.
 */

require __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, 'canary FAIL: ' . $msg . "\n");
        exit(1);
    }
}

try {
    $c = new \Fancourier\Client();
    assert_true($c->set_verify(true, true) === $c, 'Client::set_verify() should be fluent');
    assert_true($c->set_timeout(1, 1) === $c, 'Client::set_timeout() should be fluent');
    assert_true($c->headers_add('X-Canary', '1') === $c, 'Client::headers_add() should be fluent');
    assert_true($c->headers_delete('X-Canary') === $c, 'Client::headers_delete() should be fluent');
    assert_true(is_string($c->get_error()), 'Client::get_error() should return a string');
    assert_true($c->get_error() === '', 'Client::get_error() should be empty before a request');

    $auth = new \Fancourier\Auth('canary-id', 'canary-user', 'canary-pass', 'canary-token');

    $payload = (new \Fancourier\Request\GetCosts())
        ->authenticate($auth)
        ->setPaymentType('destinatar')
        ->setCity('Bucuresti')
        ->setCounty('Bucuresti')
        ->setEnvelopes(2)
        ->setWeight(1.5)
        ->pack();

    assert_true($payload['clientId'] === 'canary-id', 'GetCosts::pack() should carry the auth clientId');
    assert_true($payload['info']['service'] === 'Standard', 'GetCosts::pack() service should be Standard');
    assert_true($payload['info']['payment'] === 'destinatar', 'GetCosts::pack() payment should be destinatar');
    assert_true($payload['info']['packages'] === ['envelope' => 2], 'GetCosts::pack() should carry envelopes');
    assert_true($payload['recipient']['locality'] === 'Bucuresti', 'GetCosts::pack() recipient locality');

    $awb = (new \Fancourier\Objects\AwbIntern())
        ->setRecipientName('Ion Popescu')
        ->setCity('Cluj-Napoca')
        ->setWeight(3)
        ->setPaymentType('expeditor')
        ->setReturnPayment('destinatar');

    assert_true($awb->getRecipientName() === 'Ion Popescu', 'AwbIntern::getRecipientName()');
    assert_true($awb->getWeight() === 3, 'AwbIntern::getWeight() should normalise a numeric value');
    assert_true($awb->pack()['info']['payment'] === 'expeditor', 'AwbIntern::pack() payment');

    $extern = (new \Fancourier\Objects\AwbExtern())
        ->setDeliveryMode('aerian')
        ->setDocumentType('non document');
    assert_true($extern->getDeliveryMode() === 'aerian', 'AwbExtern::getDeliveryMode()');
    assert_true($extern->getDocumentType() === 'non document', 'AwbExtern::getDocumentType()');

    $facade = new \Fancourier\Fancourier('canary-id', 'canary-user', 'canary-pass', 'canary-token');
    assert_true($facade->getToken() === 'canary-token', 'Fancourier::getToken() should return the cached token');

    fwrite(STDOUT, "canary OK\n");
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, 'canary FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
