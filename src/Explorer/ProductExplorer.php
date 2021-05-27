<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Explorer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Portal\Base\Exploration\Contract\ExploreContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Exploration\Contract\ExplorerContract;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;

class ProductExplorer extends ExplorerContract
{
    public function supports(): string
    {
        return Product::class;
    }

    protected function run(ExploreContextInterface $context): iterable
    {
        $container = $context->getContainer();
        /** @var AmiiboApiClient $client */
        $client = $container->get(AmiiboApiClient::class);

        yield from $client->getAmiiboIds();
    }
}
