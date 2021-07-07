<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo;

use Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalContract;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Portal extends PortalContract
{
    public const CONFIG_PREVIEW = 'preview';

    public const CONFIG_PREVIEW_LIMIT = 'preview_limit';

    public function getConfigurationTemplate(): OptionsResolver
    {
        return parent::getConfigurationTemplate()->setDefaults([
            self::CONFIG_PREVIEW => true,
            self::CONFIG_PREVIEW_LIMIT => 10,
        ])
            ->setAllowedTypes(self::CONFIG_PREVIEW, 'bool')
            ->setAllowedTypes(self::CONFIG_PREVIEW_LIMIT, 'int');
    }
}
