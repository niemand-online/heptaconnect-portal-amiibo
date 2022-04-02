<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Packer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Price\Price;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Category;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Product;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Tax\TaxGroup;
use Heptacom\HeptaConnect\Dataset\Ecommerce\Tax\TaxGroupRule;
use Heptacom\HeptaConnect\Portal\Base\File\FileReferenceFactoryContract;
use Psr\Http\Message\UriFactoryInterface;

class ProductPacker
{
    private UriFactoryInterface $uriFactory;

    private FileReferenceFactoryContract $file;

    private float $configFakePriceGross;

    private float $configFakePriceTaxRate;

    public function __construct(
        UriFactoryInterface $uriFactory,
        FileReferenceFactoryContract $file,
        float $configFakePriceGross,
        float $configFakePriceTaxRate
    ) {
        $this->uriFactory = $uriFactory;
        $this->file = $file;
        $this->configFakePriceGross = $configFakePriceGross;
        $this->configFakePriceTaxRate = $configFakePriceTaxRate;
    }

    /**
     * @param array{head: string, tail: string, name: string, typeId: string, characterId: string, image: string, image_mimetype: string|null} $source
     */
    public function pack(array $source): Product
    {
        $result = new Product();

        $result->setPrimaryKey($source['head'].$source['tail']);
        $result->setNumber($source['head'].$source['tail']);
        $result->setActive(true);
        $result->setGtin($source['head'].$source['tail']);
        $result->setInventory(0);
        $result->getName()->setFallback($source['name']);
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

        $imageUrl = $this->uriFactory->createUri($source['image']);
        $imageMimetype = $source['image_mimetype'];

        if ($imageMimetype !== null) {
            $media = new Media();
            $media->setFile($this->file->fromPublicUrl((string) $imageUrl));
            $media->setFilename(\basename($imageUrl->getPath()));
            $media->setMimeType($imageMimetype);
            $result->getMedias()->push([$media]);
        }

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
