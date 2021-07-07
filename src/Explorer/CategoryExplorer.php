<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Explorer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Category;
use Heptacom\HeptaConnect\Portal\Base\Exploration\Contract\ExploreContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Exploration\Contract\ExplorerContract;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;

class CategoryExplorer extends ExplorerContract
{
    public function supports(): string
    {
        return Category::class;
    }

    protected function run(ExploreContextInterface $context): iterable
    {
        $container = $context->getContainer();
        /** @var AmiiboApiClient $client */
        $client = $container->get(AmiiboApiClient::class);

        yield from \array_map(
            static fn (string $id): string => \json_encode([
                'type' => 'root',
                'id' => $id,
            ]),
            [
                'character',
                'type',
            ]
        );
        yield from \array_map(
            static fn (string $id): string => \json_encode([
                'type' => 'character',
                'id' => $id,
            ]),
            $client->getCharacterIds()
        );
        yield from \array_map(
            static fn (string $id): string => \json_encode([
                'type' => 'type',
                'id' => $id,
            ]),
            $client->getTypeIds()
        );
    }
}
