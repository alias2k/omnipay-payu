<?php

require '../vendor/autoload.php';

use Omnipay\PayU\GatewayFactory;

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

// default is official sandbox
$posId = isset($_ENV['POS_ID']) ? $_ENV['POS_ID'] : '300746';
$secondKey = isset($_ENV['SECOND_KEY']) ? $_ENV['SECOND_KEY'] : 'b6ca15b0d1020e8094d9b5f8d163db54';
$oAuthClientSecret = isset($_ENV['OAUTH_CLIENT_SECRET']) ? $_ENV['OAUTH_CLIENT_SECRET'] : '2ee86a66e5d97e3fadc400c9f19b065d';

$gateway = GatewayFactory::createInstance($posId, $secondKey, $oAuthClientSecret, true);

try {
    $completeRequest = ['transactionReference' => 'J2585ZGF82200217GUEST000P01'];
    $response = $gateway->completePurchase($completeRequest);

    echo "Is Successful: " . $response->isSuccessful() . PHP_EOL;
    echo "TransactionId: " . $response->getTransactionId() . PHP_EOL;
    echo "State code: " . $response->getCode() . PHP_EOL;
    echo "PaymentId: " , $response->getTransactionReference() . PHP_EOL;
    echo "Data: " . json_encode($response->getData()) . PHP_EOL;

} catch (\Exception $e) {
    dump($e->getResponse()->getBody(true));
    dump((string)$e);
}