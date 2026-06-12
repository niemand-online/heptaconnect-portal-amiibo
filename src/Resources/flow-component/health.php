<?php

declare(strict_types=1);

use Heptacom\HeptaConnect\Portal\Base\Builder\FlowComponent;
use Heptacom\HeptaConnect\Portal\Base\StatusReporting\Contract\StatusReporterContract;
use NiemandOnline\HeptaConnect\Portal\Amiibo\Support\AmiiboApiClient;

FlowComponent::statusReporter(StatusReporterContract::TOPIC_HEALTH)->run(function (
    AmiiboApiClient $client
): bool {
    $client->lastUpdated();

    return true;
});
