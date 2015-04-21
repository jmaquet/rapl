<?php

namespace RAPL\RAPL\Connection;

use GuzzleHttp\Message\RequestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\Response;

interface ConnectionInterface
{
    /**
     * @param string $method
     * @param string $uri
     *
     * @return RequestInterface
     */
    public function createRequest($method = 'GET', $url = null, array $options = array());
    //NEW

    /**
     * @param RequestInterface $request
     *
     * @return Response
     */
    public function sendRequest(RequestInterface $request);

    /**
     * @param EventSubscriberInterface $subscriber
     *
     * @return void
     */
    public function addSubscriber(SubscriberInterface $subscriber);
}
