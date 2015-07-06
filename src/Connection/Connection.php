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
use Symfony\Component\Stopwatch\Stopwatch;

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
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * @param string $baseUrl
     */
    public function __construct($baseUrl, Logger $logger = null, Stopwatch $stopwatch = null)
    {
        $this->httpClient = new Client(['base_url' => $baseUrl]);
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
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
        $displayBody = false;
        $bodyLength = 100;

        if ($this->logger !== null) {
            $timestart = microtime(true);
            if ($displayBody && $request->getBody()) {
                $body = ' ' . substr($request->getBody(), 0, $bodyLength);
                if ($request->getBody()->getSize() > $bodyLength) {
                    $body .= '...';
                }
            } else {
                $body = '';
            }

            if ($request->getQuery()->count() > 0) {
                $query = $request->getQuery()->toArray();
                $params = '?' . implode('&', array_map(function ($v, $k) { return $k . '=' . $v; }, $query, array_flip($query)));
            } else {
                $params = '';
            }

            $this->logger->addInfo('[RAPL] Webservice called : ' . $request->getMethod() . ' ' .$request->getPath() . $params . $body);
        }
        if ($this->stopwatch) {
            $this->stopwatch->start('rapl.rest');
        }

        $response = $this->httpClient->send($request);

        if ($this->logger !== null) {
            $timeend = microtime(true);
            $time = $timeend - $timestart;
            $page_load_time = number_format($time, 3);
            if ($displayBody) {
                $body = $response->getBody() ? ' ' . substr($response->getBody(), 0, $bodyLength) : null;
                if ($response->getBody()->getSize() > $bodyLength) {
                    $body .= '...';
                }
            } else {
                $body = '';
            }

            $this->logger->addInfo('[RAPL] Webservice called : [' . $page_load_time . 's]' . $body);
            $this->logger->addInfo('');
        }
        if ($this->stopwatch) {
            $this->stopwatch->stop('rapl.rest');
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
