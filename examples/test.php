<?php

require '../vendor/autoload.php';

use Guzzle\Http\Exception\ClientErrorResponseException;
use Omnipay\PayU\GatewayFactory;

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

// default is official sandbox
$posId = isset($_ENV['POS_ID']) ? $_ENV['POS_ID'] : '300746';
$secondKey = isset($_ENV['SECOND_KEY']) ? $_ENV['SECOND_KEY'] : 'b6ca15b0d1020e8094d9b5f8d163db54';
$oAuthClientSecret = isset($_ENV['OAUTH_CLIENT_SECRET']) ? $_ENV['OAUTH_CLIENT_SECRET'] : '2ee86a66e5d97e3fadc400c9f19b065d';
$gateway = GatewayFactory::createInstance($posId, $secondKey, $oAuthClientSecret, true);
try {
    $orderNo = uniqid();
    $returnUrl = "http://alias-internal-testing.a2k.it/test-finish.php?extOrderId=$orderNo";
    $notifyUrl = 'http://alias-internal-testing.a2k.it/test-notification.php';
    $description = 'Shopping at myStore.com';

    $purchaseRequest = [
        'purchaseData' => [
            'customerIp'    => '127.0.0.1',
            'continueUrl'   => $returnUrl,
            'notifyUrl'     => $notifyUrl,
            'merchantPosId' => $posId,
            'description'   => $description,
            'currencyCode'  => 'PLN',
            'totalAmount'   => 15000,
            'extOrderId'    => $orderNo,
            'buyer'         => (object)[
                'email'     => 'test@test.com',
                'firstName' => 'Tester',
                'lastName'  => 'Tester',
                'language'  => 'pl'
            ],
            'products'      => [
                (object)[
                    'name'      => 'Lenovo ThinkPad Edge E540',
                    'unitPrice' => 15000,
                    'quantity'  => 1
                ]
            ],
            'payMethods'    => (object)[
                'payMethod' => (object)[
                    'type'  => 'PBL', // this is for card-only forms (no bank transfers available)
                    'value' => 'c'
                ]
            ]
        ]
    ];

    $response = $gateway->purchase($purchaseRequest);
    //check if is a redirect call and do the redirect, in test.php is disabled
    if (false && $response->isRedirect()) {
      $response->redirect();
    }

    echo "TransactionId: " . $response->getTransactionId() . PHP_EOL;
    echo "TransactionReference: " . $response->getTransactionReference() . PHP_EOL;
    echo 'Is Successful: ' . (bool)$response->isSuccessful() . PHP_EOL;
    echo 'Is redirect: ' . (bool)$response->isRedirect() . PHP_EOL;

    if ($response->isRedirect()) {
      // Payment init OK, redirect to the payment gateway
      echo '<a href="' . $response->getRedirectUrl() . '" target="_blank">' . $response->getRedirectUrl() . '</a>' . PHP_EOL;
    }
} catch (ClientErrorResponseException $e) {
    dump((string)$e);
    dump($e->getResponse()->getBody(true));
}