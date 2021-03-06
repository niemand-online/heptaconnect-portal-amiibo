<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Emitter;

use Heptacom\HeptaConnect\Dataset\Base\Contract\DatasetEntityContract;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Category;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitContextInterface;
use Heptacom\HeptaConnect\Portal\Base\Emission\Contract\EmitterContract;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Packer\CategoryPacker;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;

class CategoryEmitter extends EmitterContract
{
    private AmiiboApiClient $client;

    private CategoryPacker $packer;

    public function __construct(AmiiboApiClient $client, CategoryPacker $packer)
    {
        $this->client = $client;
        $this->packer = $packer;
    }

    public function supports(): string
    {
        return Category::class;
    }

    protected function run(string $externalId, EmitContextInterface $context): ?DatasetEntityContract
    {
        $primaryKey = (array) \json_decode($externalId, true, 5, \JSON_THROW_ON_ERROR);
        $pkType = (string) $primaryKey['type'];
        $pkId = (string) $primaryKey['id'];
        $payload = [];

        switch ($pkType) {
            case 'root':
                $payload = [
                    'key' => $externalId,
                    'name' => $pkId,
                ];
                break;
            case 'character':
                $payload = $this->client->getCharacter($pkId);
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
                $payload = $this->client->getType($pkId);
                $payload['key'] = \json_encode([
                    'type' => 'type',
                    'id' => $payload['key'],
                ]);
                $payload['parent'] = \json_encode([
                    'type' => 'root',
                    'id' => 'type',
                ]);
                break;
        }

        return $this->packer->pack($payload);
    }
}
