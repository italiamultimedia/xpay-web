<?php

declare(strict_types=1);

namespace Tests\Unit\TestDouble;

use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function array_shift;

final class QueueHttpClient implements ClientInterface
{
    private ?RequestInterface $request = null;

    /**
     * @var array<\Psr\Http\Message\ResponseInterface>
     */
    private array $responses;

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = $responses;
    }

    public function getRequest(): RequestInterface
    {
        if ($this->request === null) {
            throw new RuntimeException('No request was sent.');
        }

        return $this->request;
    }

    #[Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $response = array_shift($this->responses);
        if (!$response instanceof ResponseInterface) {
            throw new RuntimeException('No queued response.');
        }

        return $response;
    }
}
