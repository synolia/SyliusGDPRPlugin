<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Provider;

interface AnonymizerInterface
{
    public function anonymize(Object $entity, bool $reset = false, int $maxRetries = 10000): void;
}
