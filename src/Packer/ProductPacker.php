<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Packer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Price\Price;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Category;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Tax\TaxGroup;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Tax\TaxGroupRule;

class ProductPacker
{
    private float $configFakePriceGross;

    private float $configFakePriceTaxRate;

    public function __construct(float $configFakePriceGross, float $configFakePriceTaxRate)
    {
        $this->configFakePriceGross = $configFakePriceGross;
        $this->configFakePriceTaxRate = $configFakePriceTaxRate;
    }

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

        $price = new Price();
        $price->setPrimaryKey($result->getPrimaryKey());
        $price->setGross($this->configFakePriceGross);
        $price->setNet($this->configFakePriceGross / ((100 + $this->configFakePriceTaxRate) / 100.0));
        $price->setTaxStatus(Price::TAX_STATUS_GROSS);

        $result->getPrices()->push([$price]);

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

        $result->setPrimaryKey((string) $this->configFakePriceTaxRate);
        $rule->setPrimaryKey((string) $this->configFakePriceTaxRate);
        $rule->setRate($this->configFakePriceTaxRate);
        $result->getRules()->push([$rule]);

        return $result;
    }
}
