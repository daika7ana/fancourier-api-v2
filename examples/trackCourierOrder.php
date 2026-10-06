<?php

declare(strict_types=1);

/*
 * TrackCourierOrder - track one or more courier pickup orders.
 *
 * Endpoint: GET reports/orders/tracking
 * Request:  Fancourier\Request\TrackCourierOrder
 * Response: Fancourier\Response\TrackCourierOrder
 */

// bootstrap the library and the shared Fancourier instance ($fan)
require __DIR__ . '/_init.php';

// --- request ---------------------------------------------------------------

$request = new Fancourier\Request\TrackCourierOrder();

/*
 * TrackCourierOrder request inputs:
 *   ->addOrder($orderId)
 *   ->setOrder($orderId)           // alias for addOrder()
 *   ->resetOrders()
 *   ->setLanguage($language)       // Language enum or "ro"/"en"
 */

$request
    ->addOrder('18650990')
    ->setLanguage(Fancourier\Enums\Language::Ro);

// --- send ------------------------------------------------------------------

$response = $fan->trackCourierOrder($request);

/*
 * TrackCourierOrder response getters:
 *   ->getData()           // raw API data as an array
 *   ->getAll()            // map of CourierOrderTracker objects, keyed by order id
 *   ->getOrder($orderId)  // one CourierOrderTracker object, or false when missing
 */

if ($response->isOk()) {
    print_r($response->getData());
    echo '<pre>' . print_r($response->getAll(), 1) . '</pre>';
    echo '<hr />';

    $order = $response->getOrder('18650990');
    if ($order !== false) {
        echo 'Status: ' . $order->getStatus()['date'] . ': ' . $order->getStatus()['name'] . '<br />';
    }
} else {
    var_dump($response->getErrorMessage());
}

/*
 * The CourierOrderTracker object has the following functions:
 *   ->getOrderId()
 *   ->getOrderNo()
 *   ->getMessage()
 *   ->getEvents()
 *   ->getStatus()
 */
