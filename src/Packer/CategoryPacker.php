<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Packer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Product\Category;

class CategoryPacker
{
    public function pack(array $category): Category
    {
        $result = new Category();

        $result->setPrimaryKey((string) $category['key']);
        $result->getName()->setFallback((string) $category['name']);

        if (($category['parent'] ?? '') !== '') {
            $parent = new Category();
            $parent->setPrimaryKey($category['parent']);
            $result->setParent($parent);
        }

        return $result;
    }
}
