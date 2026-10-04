<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Container;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class HttpDependencyContainer
{
    public function __construct(
        public ClientInterface $httpClient,
        public RequestFactoryInterface $requestFactory,
        public StreamFactoryInterface $streamFactory,
    ) {
    }
}
