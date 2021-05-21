<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Provider;

interface AnonymizerInterface
{
    public function anonymize($result, $reset, $maxRetries): void;
}
