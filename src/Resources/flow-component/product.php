<?php
declare(strict_types=1);

use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Portal\Base\Builder\FlowComponent;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\ProductPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;

FlowComponent::explorer(Product::class)
    ->run(static fn (AmiiboApiClient $client): iterable => $client->getAmiiboIds());

FlowComponent::emitter(Product::class)
    ->batch(static function(
        iterable $externalIds,
        ProductPacker $productPacker,
        MediaPacker $mediaPacker,
        AmiiboApiClient $client
    ): iterable {
        $rawAmiibos = $client->getAmiibos(\iterable_to_array($externalIds));
        \array_walk($rawAmiibos, function (array &$amiibo) use ($client): void {
            $imageUrl = $amiibo['image'] ?? null;
            $mimeType = null;

            if ($imageUrl !== null) {
                $mimeType = $client->getImageMimeType($imageUrl);
            }

            $amiibo['image_mimetype'] = $mimeType;
        });
        return \array_map([$productPacker, 'pack'], $rawAmiibos);
    });
