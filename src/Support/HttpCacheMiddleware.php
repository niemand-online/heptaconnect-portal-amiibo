<?php

declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Support;

use Heptacom\HeptaConnect\Portal\Base\Web\Http\Contract\HttpClientMiddlewareInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpCacheMiddleware implements HttpClientMiddlewareInterface
{
    private array $cache = [];

    public function process(RequestInterface $request, ClientInterface $handler): ResponseInterface
    {
        $cacheKey = (string) $request->getUri();

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $response = $handler->sendRequest($request);

        return $this->cache[$cacheKey] = $response;
    }
}
