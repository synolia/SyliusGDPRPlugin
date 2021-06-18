<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor;

use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;
use Doctrine\ORM\EntityManagerInterface;

class AnonymizerProcessor
{
    private const MODULO_FLUSH = 50;

    private const MAX_RETRIES = 10000;

    /** @var AnonymizerInterface */
    private $anonymizer;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var bool */
    private $reset;

    /** @var int */
    private $maxRetries;

    public function __construct(AnonymizerInterface $anonymizer, EntityManagerInterface $entityManager)
    {
        $this->anonymizer = $anonymizer;
        $this->entityManager = $entityManager;
        $this->reset = false;
        $this->maxRetries = self::MAX_RETRIES;
    }

    public function anonymizeEntities(array $entities): void
    {
        foreach ($entities as $index => $entity) {
            if (null === $entity) {
                continue;
            }
            $this->anonymizeEntity($entity);

            if (0 === $index % self::MODULO_FLUSH) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();
    }

    private function anonymizeEntity(Object $entity): void
    {
        $this->anonymizer->anonymize($entity, $this->reset, $this->maxRetries);
    }
}
