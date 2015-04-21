<?php

namespace RAPL\RAPL\Connection;

use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\EmitterInterface;
use GuzzleHttp\Message\Response;

class Connection implements ConnectionInterface
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @param string $baseUrl
     */
    public function __construct($baseUrl)
    {
        $this->httpClient = new Client(['base_url' => $baseUrl]);
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @return RequestInterface
     */
    public function createRequest($method = 'GET', $url = null, array $options = array())
    {
        //NEW
        return $this->httpClient->createRequest($method, $url, $options);
    }

    /**
     * @param RequestInterface $request
     *
     * @return Response
     */
    public function sendRequest(RequestInterface $request)
    {
        return $this->httpClient->send($request);
    }

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $this->httpClient->getEmitter()->attach($subscriber);
    }
}
