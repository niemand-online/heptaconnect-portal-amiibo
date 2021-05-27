<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Emitter;

use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Category;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitterContract;
use Heptacom\HeptaConnect\Portal\Base\Mapping\Contract\MappingInterface;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\CategoryPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;

class CategoryEmitter extends EmitterContract
{
    public function supports(): string
    {
        return Category::class;
    }

    protected function run(MappingInterface $mapping, EmitContextInterface $context): ?DatasetEntityContract
    {
        $primaryKey = (array) \json_decode($mapping->getExternalId(), true, 5, \JSON_THROW_ON_ERROR);
        $pkType = (string) $primaryKey['type'];
        $pkId = (string) $primaryKey['id'];

        $container = $context->getContainer();
        /** @var CategoryPacker $packer */
        $packer = $container->get(CategoryPacker::class);
        /** @var AmiiboApiClient $client */
        $client = $container->get(AmiiboApiClient::class);
        $payload = [];

        switch ($pkType) {
            case 'root':
                $payload = [
                    'key' => $mapping->getExternalId(),
                    'name' => $pkId,
                ];
                break;
            case 'character':
                $payload = $client->getCharacter($pkId);
                $payload['key'] = \json_encode([
                    'type' => 'character',
                    'id' => $payload['key'],
                ]);
                $payload['parent'] = \json_encode([
                    'type' => 'root',
                    'id' => 'character',
                ]);
                break;
            case 'type':
                $payload = $client->getType($pkId);
                $payload['key'] = \json_encode([
                    'type' => 'type',
                    'id' => $payload['key'],
                ]);
                $payload['parent'] = \json_encode([
                    'type' => 'root',
                    'id' => 'type',
                ]);
                break;
            case 'serie':
                $payload = $client->getSerie($pkId);
                $payload['key'] = \json_encode([
                    'type' => 'serie',
                    'id' => $payload['key'],
                ]);
                $payload['parent'] = \json_encode([
                    'type' => 'root',
                    'id' => 'serie',
                ]);
                break;
            case 'gameserie':
                $payload = $client->getGameSerie($pkId);
                $payload['key'] = \json_encode([
                    'type' => 'gameserie',
                    'id' => $payload['key'],
                ]);
                $payload['parent'] = \json_encode([
                    'type' => 'root',
                    'id' => 'gameserie',
                ]);
                break;

        }

        return $packer->pack($payload);
    }
}
