<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Packer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Category;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Tax\TaxGroup;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Tax\TaxGroupRule;

class ProductPacker
{
    public function pack(array $source): Product
    {
        $result = new Product();

        $result->setPrimaryKey($source['head'].$source['tail']);
        $result->setNumber($source['head'].$source['tail']);
        $result->setActive(true);
        $result->setGtin($source['head'].$source['tail']);
        $result->setInventory(0);
        $result->getName()->setFallback((string) $source['name']);
        $result->setTaxGroup($this->getTaxGroup());

        $result->getCategories()->push([
            $this->getCategoryReference('type', $source['typeId'] ?? null),
            $this->getCategoryReference('character', $source['characterId'] ?? null),
        ]);

        return $result;
    }

    protected function getCategoryReference(string $type, ?string $key): ?Category
    {
        if ($key === null) {
            return null;
        }

        $result = new Category();

        $result->setPrimaryKey(\json_encode([
            'type' => $type,
            'id' => $key,
        ]));

        return $result;
    }

    protected function getTaxGroup(): TaxGroup
    {
        $result = new TaxGroup();
        $rule = new TaxGroupRule();

        $result->setPrimaryKey('19');
        $rule->setPrimaryKey('19');
        $rule->setRate(19);
        $result->getRules()->push([$rule]);

        return $result;
    }
}
