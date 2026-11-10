<?php

namespace CoinsPay\Tests\Support;

use CoinsPay\Http\HttpClientInterface;
use CoinsPay\Http\Response;
use CoinsPay\Http\TransportException;

final class FakeHttp implements HttpClientInterface
{
    /** @var array<int, array<string, mixed>> */
    public $requests = [];

    /** @var array<int, array<string, mixed>> */
    private $steps;

    /**
     * @param array<int, array<string, mixed>> $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    public function send($method, $url, array $headers, $body, $timeout, $connectTimeout)
    {
        $named = [];
        foreach ($headers as $line) {
            list($name, $value) = explode(': ', $line, 2);
            $named[$name] = $value;
        }
        $parts = parse_url($url);
        $this->requests[] = [
            'method' => $method,
            'path' => preg_replace('#^/api/v1#', '', $parts['path']),
            'query' => isset($parts['query']) ? $parts['query'] : '',
            'headers' => $named,
            'body' => $body,
        ];
        $step = array_shift($this->steps);
        if ($step === null) {
            throw new \RuntimeException('Out of steps: request ' . count($this->requests));
        }
        if (isset($step['network'])) {
            switch ($step['network']) {
                case 'refused':
                    throw new TransportException('Connection refused', false);
                case 'reset':
                    throw new TransportException('Connection reset', true);
                default:
                    throw new TransportException('Operation timed out', true, true);
            }
        }
        $text = isset($step['text']) ? $step['text'] : json_encode($step['body']);
        return new Response($step['status'], isset($step['headers']) ? $step['headers'] : [], $text);
    }
}
