<?php

namespace RAPL\RAPL\Connection;

use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use Symfony\Bridge\Monolog\Logger;
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
     * @var Logger
     */
    protected $logger;

    /**
     * @param string $baseUrl
     */
    public function __construct($baseUrl, $logger = null)
    {
        $this->httpClient = new Client(['base_url' => $baseUrl]);
        $this->logger = $logger;
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
        if ($this->logger !== null) {
            $timestart = microtime(true);
        }

        $response = $this->httpClient->send($request);

        if ($this->logger !== null) {
            $timeend = microtime(true);
            $time = $timeend - $timestart;
            $page_load_time = number_format($time, 3);

            $this->logger->addInfo('[RAPL] Webservice called : ' . $request->getMethod() . ' ' .$request->getPath() . ' [' . $page_load_time . 's]');
        }

        return $response;
    }

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $this->httpClient->getEmitter()->attach($subscriber);
    }
}
