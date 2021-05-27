<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Packer;

use Heptacom\HeptaConnect\Core\Storage\NormalizationRegistry;
use Heptacom\HeptaConnect\Core\Storage\Struct\SerializableStream;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Psr\Http\Message\ResponseInterface;

class MediaPacker
{
    private NormalizationRegistry $normalizationRegistry;

    public function __construct(NormalizationRegistry $normalizationRegistry)
    {
        $this->normalizationRegistry = $normalizationRegistry;
    }

    public function pack(ResponseInterface $response): Media
    {
        $result = new Media();
        $blob = new SerializableStream($response->getBody());

        $result->setNormalizedStream($this->normalizationRegistry->getNormalizer($blob)->normalize($blob));
        $result->setMimeType($response->getHeaderLine('Content-Type'));

        return $result;
    }
}
