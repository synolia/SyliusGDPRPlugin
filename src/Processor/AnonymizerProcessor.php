<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

final class AnonymizerProcessor
{
    private const MODULO_FLUSH = 50;

    /** @var AnonymizerInterface */
    private $anonymizer;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        AnonymizerInterface $anonymizer,
        EntityManagerInterface $entityManager
    ) {
        $this->anonymizer = $anonymizer;
        $this->entityManager = $entityManager;
    }

    public function anonymizeEntities(array $entities, bool $reset = false, int $maxRetries = 50): void
    {
        foreach ($entities as $index => $entity) {
            if (null === $entity) {
                continue;
            }
            $this->anonymizeEntity($entity, $reset, $maxRetries);

            if (0 !== $index && 0 === $index % self::MODULO_FLUSH) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
    }

    private function anonymizeEntity(Object $entity, bool $reset = false, int $maxRetries = 50): void
    {
        $this->anonymizer->anonymize($entity, $reset, $maxRetries);
    }
}
