<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Emitter;

use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitterContract;
use Heptacom\HeptaConnect\Portal\Base\Mapping\Contract\MappingInterface;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\MediaPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\ProductPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;

class ProductEmitter extends EmitterContract
{
    public function supports(): string
    {
        return Product::class;
    }

    protected function run(MappingInterface $mapping, EmitContextInterface $context): ?DatasetEntityContract
    {
        $primaryKey = $mapping->getExternalId();
        $container = $context->getContainer();
        /** @var ProductPacker $productPacker */
        $productPacker = $container->get(ProductPacker::class);
        /** @var MediaPacker $mediaPacker */
        $mediaPacker = $container->get(MediaPacker::class);
        /** @var AmiiboApiClient $client */
        $client = $container->get(AmiiboApiClient::class);
        $amiibo = $client->getAmiibo($primaryKey);
        $result = $productPacker->pack($amiibo);

        $this->attachMedia($mediaPacker, $client, $amiibo['image'], $result);

        return $result;
    }

    protected function attachMedia(MediaPacker $mediaPacker, AmiiboApiClient $client, string $imageUrl, Product $result): void
    {
        $imageResponse = $client->getImage($imageUrl);

        if ($imageResponse === null) {
            return;
        }

        $image = $mediaPacker->pack($imageResponse);

        $image->setPrimaryKey($imageUrl);
        $result->attach($image);
    }
}
