<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class AmiiboApiClient
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private UriFactoryInterface $uriFactory;

    private bool $configPreview;

    private int $configPreviewLimit;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        bool $configPreview,
        int $configPreviewLimit
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->configPreview = $configPreview;
        $this->configPreviewLimit = $configPreviewLimit;
    }

    public function getCharacterIds(): array
    {
        if ($this->configPreview) {
            $request = $this->requestFactory->createRequest('GET', $this->getUri('amiibo'));
            $amiibos = $this->unwrapResponse($this->client->sendRequest($request)) ?? [];
            $result = \array_unique(\array_column(\array_slice($amiibos, 0, $this->configPreviewLimit), 'character'));

            return \array_map(
                fn (string $name): string => $this->requestResourceIdByName('character', $name),
                $result
            );
        }

        return \array_keys($this->requestKeyValue('character'));
    }

    public function getTypeIds(): array
    {
        if ($this->configPreview) {
            $request = $this->requestFactory->createRequest('GET', $this->getUri('amiibo'));
            $amiibos = $this->unwrapResponse($this->client->sendRequest($request)) ?? [];
            $result = \array_unique(\array_column(\array_slice($amiibos, 0, $this->configPreviewLimit), 'type'));

            return \array_map(
                fn (string $name): string => $this->requestResourceIdByName('type', $name),
                $result
            );
        }

        return \array_keys($this->requestKeyValue('type'));
    }

    public function getCharacter(string $key): array
    {
        return $this->requestResource('character', $key);
    }

    public function getType(string $key): array
    {
        return $this->requestResource('type', $key);
    }

    public function getAmiiboIds(): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri('amiibo'));
        $amiibos = $this->unwrapResponse($this->client->sendRequest($request)) ?? [];

        foreach ($amiibos as $amiibo) {
            $result[] = $amiibo['head'].$amiibo['tail'];
        }

        return $this->configPreview ? \array_slice($result, 0, $this->configPreviewLimit, true) : $result;
    }

    public function getAmiibo(string $id): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri('amiibo', [
            'id' => $id,
            'showusage' => '',
        ]));
        $amiibo = $this->unwrapResponse($this->client->sendRequest($request));

        if ($amiibo === null) {
            return [];
        }

        $amiibo['characterId'] = $this->requestResourceIdByName('character', (string) $amiibo['character']);
        $amiibo['typeId'] = $this->requestResourceIdByName('type', (string) $amiibo['type']);

        return $amiibo;
    }

    public function getImage(string $url): ?ResponseInterface
    {
        $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $url));

        if ($response->getStatusCode() < 200 || 300 <= $response->getStatusCode()) {
            return null;
        }

        return $response;
    }

    protected function requestResourceIdByName(string $resource, string $name): ?string
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri($resource, [
            'name' => $name,
        ]));

        return $this->unwrapResponse($this->client->sendRequest($request))[0]['key'] ?? null;
    }

    protected function requestResource(string $resource, string $key): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri($resource, [
            'key' => $key,
        ]));

        return $this->unwrapResponse($this->client->sendRequest($request)) ?? [];
    }

    protected function requestKeyValue(string $resource): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri($resource));

        return \array_column($this->unwrapResponse($this->client->sendRequest($request)) ?? [], 'name', 'key');
    }

    protected function unwrapResponse(ResponseInterface $response): ?array
    {
        if (300 <= $response->getStatusCode() || $response->getStatusCode() < 200) {
            return null;
        }

        $data = \json_decode($response->getBody()->getContents(), true, 5, \JSON_THROW_ON_ERROR);

        if (!\is_array($data) || $data === []) {
            return null;
        }

        $amiiboData = $data['amiibo'];

        if (!\is_array($amiiboData) || $amiiboData === []) {
            return null;
        }

        return $amiiboData;
    }

    protected function getUri(string $path, array $params = []): UriInterface
    {
        return $this->uriFactory
            ->createUri('https://amiiboapi.com/')
            ->withPath(\rtrim('api/'.$path, '/').'/')
            ->withQuery(\http_build_query($params));
    }
}
