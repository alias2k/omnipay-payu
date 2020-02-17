<?php

require '../vendor/autoload.php';

use Omnipay\PayU\GatewayFactory;
use Omnipay\PayU\Messages\Notification;
use Symfony\Component\HttpFoundation\Request;

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

// default is official sandbox
$posId = isset($_ENV['POS_ID']) ? $_ENV['POS_ID'] : '300746';
$secondKey = isset($_ENV['SECOND_KEY']) ? $_ENV['SECOND_KEY'] : 'b6ca15b0d1020e8094d9b5f8d163db54';
$oAuthClientSecret = isset($_ENV['OAUTH_CLIENT_SECRET']) ? $_ENV['OAUTH_CLIENT_SECRET'] : '2ee86a66e5d97e3fadc400c9f19b065d';
$posAuthKey = isset($_ENV['POS_AUTH_KEY']) ? $_ENV['POS_AUTH_KEY'] : null; // Official sandbox does not provide signature key
$write_to_result_txt = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content     = file_get_contents('php://input');
  $httpRequest = Request::create('/notify', 'POST', [], [], [], [], $content);

  $headers = array();
  foreach (getallheaders() as $name => $value) {
    $headers[$name] = $value;
  }
  $httpRequest->headers->add(
    $headers
  );
  $write_to_result_txt = true;
} else {
  $content = '{"order":{"orderId":"DSKWN8X2XW200213GUEST000P01","extOrderId":"5e45137d21034","orderCreateDate":"2020-02-13T10:14:37.583+01:00","notifyUrl":"http://alias-internal-testing.a2k.it/test-notification.php","customerIp":"127.0.0.1","merchantPosId":"300746","description":"Shopping at myStore.com","currencyCode":"PLN","totalAmount":"15000","buyer":{"customerId":"guest","email":"test@test.com","firstName":"Tester","lastName":"Tester","language":"pl"},"payMethod":{"type":"CARD_TOKEN"},"status":"COMPLETED","products":[{"name":"Lenovo ThinkPad Edge E540","unitPrice":"15000","quantity":"1"}]},"localReceiptDateTime":"2020-02-13T10:14:47.512+01:00","properties":[{"name":"PAYMENT_ID","value":"76258334"}]}';
  $httpRequest = Request::create('/notify', 'POST', [], [], [], [], $content);
  $httpRequest->headers->add(
    [
      'X-OpenPayU-Signature' => 'sender=checkout;signature=724ca6742d0cc50b3d69bdf5aea90d9e;algorithm=MD5;content=DOCUMENT',
      'ContentType' => 'application/json'
    ]);
}


$gateway = GatewayFactory::createInstance($posId, $secondKey, $oAuthClientSecret, true, $posAuthKey, $httpRequest);
if ($write_to_result_txt) {
  $file = 'result.txt';
  // Open the file to get existing content
  $current = file_get_contents($file);

  // Append a new person to the file
  $current .= "Signature: " . var_export($headers,true) . PHP_EOL;
  $current .= "Content: " . $content . PHP_EOL;
}
try {
  $trace_transaction = '';
  if (!$gateway->supportsAcceptNotification()) {
    $trace_transaction .= "This Gateway does not support notifications" . PHP_EOL;
  }

  $response = $gateway->acceptNotification();


  $trace_transaction .= "PaymentId: " . $response->getTransactionReference() . PHP_EOL;
  $trace_transaction .= "Status: " . $response->getTransactionStatus() . PHP_EOL;
  $trace_transaction .= "Complete: " . (string)($response->getTransactionStatus() == $response::STATUS_COMPLETED). PHP_EOL;
  if ($response->getTransactionStatus() == $response::STATUS_COMPLETED ) {
    $trace_message_transaction .= "Message: " . var_export($response->getMessage(), true) . PHP_EOL;
  }


  dump($trace_transaction);
  dump($response->getMessage());

  $current .= $trace_transaction.$trace_message_transaction;

} catch (\Exception $e) {

  dump((string)$e);

  $current .= "error: " . (string)$e . PHP_EOL;
}

if ($write_to_result_txt) {
  // Write the contents back to the file
  file_put_contents($file, $current);
}