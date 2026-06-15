<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Container;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class HttpDependencyContainer
{
    public function __construct(
        public readonly ClientInterface $httpClient,
        public readonly RequestFactoryInterface $requestFactory,
        public readonly StreamFactoryInterface $streamFactory,
    ) {
    }
}
