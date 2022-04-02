<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo;

use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalContract;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AmiiboPortal extends PortalContract
{
    public const CONFIG_PREVIEW = 'preview';

    public const CONFIG_PREVIEW_LIMIT = 'preview_limit';

    public const CONFIG_FAKE_PRICE_GROSS = 'fake_price_gross';

    public const CONFIG_FAKE_PRICE_TAX_RATE = 'fake_price_tax_rate';

    public function getConfigurationTemplate(): OptionsResolver
    {
        return parent::getConfigurationTemplate()->setDefaults([
            self::CONFIG_PREVIEW => true,
            self::CONFIG_PREVIEW_LIMIT => 10,
            self::CONFIG_FAKE_PRICE_GROSS => 20.00,
            self::CONFIG_FAKE_PRICE_TAX_RATE => 20.0,
        ])
            ->setAllowedTypes(self::CONFIG_PREVIEW, 'bool')
            ->setAllowedTypes(self::CONFIG_PREVIEW_LIMIT, 'int')
            ->setAllowedTypes(self::CONFIG_FAKE_PRICE_GROSS, 'float')
            ->setAllowedTypes(self::CONFIG_FAKE_PRICE_TAX_RATE, 'float');
    }
}
