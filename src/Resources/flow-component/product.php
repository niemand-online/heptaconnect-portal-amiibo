<?php
declare(strict_types=1);

use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Portal\Base\Builder\FlowComponent;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\ProductPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;
use Psr\Http\Message\ResponseInterface;

FlowComponent::explorer(Product::class)
    ->run(static fn (AmiiboApiClient $client): iterable => $client->getAmiiboIds());

FlowComponent::emitter(Product::class)
    ->run(static function(
        string $id,
        ProductPacker $productPacker,
        MediaPacker $mediaPacker,
        AmiiboApiClient $client
    ): Product {
        $amiibo = $client->getAmiibo($id);
        $result = $productPacker->pack($amiibo);

        $imageResponse = $client->getImage($amiibo['image']);

        if ($imageResponse instanceof ResponseInterface) {
            $image = $mediaPacker->pack($imageResponse);

            $image->setPrimaryKey($amiibo['image']);
            $result->attach($image);
        }

        return $result;
    });
