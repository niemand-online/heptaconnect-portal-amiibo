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
        return \array_keys($this->requestKeyValue('character'));
    }

    public function getTypeIds(): array
    {
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
        $response = $this->client->sendRequest($request);

        if (300 <= $response->getStatusCode() || $response->getStatusCode() < 200) {
            return [];
        }

        $characterData = \json_decode(
            $response->getBody()->getContents(),
            true,
            50,
            \JSON_THROW_ON_ERROR
        );

        if (!\is_array($characterData) || $characterData === []) {
            return [];
        }

        $result = [];

        foreach ($characterData['amiibo'] as $amiibo) {
            $result[] = $amiibo['head'].$amiibo['tail'];
        }

        return $this->configPreview ? \array_slice($result, 0, $this->configPreviewLimit) : $result;
    }

    public function getAmiibo(string $id): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri('amiibo', [
            'id' => $id,
            'showusage' => '',
        ]));
        $response = $this->client->sendRequest($request);

        if (300 <= $response->getStatusCode() || $response->getStatusCode() < 200) {
            return [];
        }

        $characterData = \json_decode(
            $response->getBody()->getContents(),
            true,
            50,
            \JSON_THROW_ON_ERROR
        );

        if (!\is_array($characterData) || $characterData === []) {
            return [];
        }

        $amiibo = (array) $characterData['amiibo'];

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
        $response = $this->client->sendRequest($request);

        if (300 <= $response->getStatusCode() || $response->getStatusCode() < 200) {
            return null;
        }

        $data = \json_decode($response->getBody()->getContents(), true, 5, \JSON_THROW_ON_ERROR);

        if (!\is_array($data) || $data === []) {
            return null;
        }

        return $data['amiibo'][0]['key'] ?? null;
    }

    protected function requestResource(string $resource, string $key): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri($resource, [
            'key' => $key,
        ]));
        $response = $this->client->sendRequest($request);

        if (300 <= $response->getStatusCode() || $response->getStatusCode() < 200) {
            return [];
        }

        $data = \json_decode($response->getBody()->getContents(), true, 5, \JSON_THROW_ON_ERROR);

        if (!\is_array($data) || $data === []) {
            return [];
        }

        return (array) $data['amiibo'];
    }

    protected function requestKeyValue(string $resource): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getUri($resource));
        $response = $this->client->sendRequest($request);

        if (300 <= $response->getStatusCode() || $response->getStatusCode() < 200) {
            return [];
        }

        $data = \json_decode($response->getBody()->getContents(), true, 5, \JSON_THROW_ON_ERROR);

        if (!\is_array($data) || $data === []) {
            return [];
        }

        $amiiboData = $data['amiibo'];

        if (!\is_array($amiiboData) || $amiiboData === []) {
            return [];
        }

        return \array_combine(
            \array_column($amiiboData, 'key'),
            \array_column($amiiboData, 'name')
        );
    }

    protected function getUri(string $path, array $params = []): UriInterface
    {
        return $this->uriFactory
            ->createUri('https://amiiboapi.com/')
            ->withPath(\rtrim('api/'.$path, '/').'/')
            ->withQuery(\http_build_query($params));
    }
}
