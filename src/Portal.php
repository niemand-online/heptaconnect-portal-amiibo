<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo;

use Heptacom\HeptaConnect\Core\Storage\NormalizationRegistry;
use Heptacom\HeptaConnect\Portal\Base\Emission\EmitterCollection;
use Heptacom\HeptaConnect\Portal\Base\Exploration\ExplorerCollection;
use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalContract;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Container\ContainerInterface as C;

class Portal extends PortalContract
{
    public function getExplorers(): ExplorerCollection
    {
        return new ExplorerCollection([
            new Explorer\CategoryExplorer(),
            new Explorer\ProductExplorer(),
        ]);
    }

    public function getEmitters(): EmitterCollection
    {
        return new EmitterCollection([
            new Emitter\CategoryEmitter(),
            new Emitter\ProductEmitter(),
        ]);
    }

    public function getServices(): array
    {
        $services = parent::getServices();

        $services[Support\AmiiboApiClient::class] = static fn (C $c): Support\AmiiboApiClient => new Support\AmiiboApiClient(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findUriFactory(),
        );
        $services[Packer\CategoryPacker::class] = static fn (C $c): Packer\CategoryPacker => new Packer\CategoryPacker();
        $services[Packer\ProductPacker::class] = static fn (C $c): Packer\ProductPacker => new Packer\ProductPacker();
        $services[Packer\MediaPacker::class] = static fn (C $c): Packer\MediaPacker => new Packer\MediaPacker(
            $c->get(NormalizationRegistry::class),
        );

        return $services;
    }
}
