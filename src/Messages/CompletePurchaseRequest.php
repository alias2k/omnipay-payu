<?php
namespace Omnipay\PayU\Messages;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

class CompletePurchaseRequest extends AbstractRequest
{

    public function setAccessToken($accessToken)
    {
        $this->setParameter('accessToken', $accessToken);
    }

    public function setApiUrl($apiUrl)
    {
        $this->setParameter('apiUrl', $apiUrl);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->getParameters();
    }

    /**
     * @param  mixed $data The data to send
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $headers = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => $data['accessToken']
        ];
        $url = $data['apiUrl'] . '/api/v2_1/orders/' . urlencode($this->getTransactionReference());
        $httpResponse = $this->httpClient->request('GET',$url, $headers);
        $response = new CompletePurchaseResponse($this, json_decode($httpResponse->getBody()->getContents(),true));

        return $this->response = $response;
    }
}
