<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

final class AnonymizerProcessor
{
    private const MODULO_FLUSH = 50;

    /** @var AnonymizerInterface */
    private $anonymizer;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $anonymizedEntity = 0;

    public function __construct(
        AnonymizerInterface $anonymizer,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->anonymizer = $anonymizer;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->logger = $logger;
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

        $this->logger->info(sprintf('%d %s', $this->getAnonymizedEntityCount(), $this->translator->trans('sylius.ui.admin.synolia_gdpr.advanced_actions.customer_anonymized_count')));
    }

    public function getAnonymizedEntityCount(): int
    {
        return $this->anonymizedEntity;
    }

    private function anonymizeEntity(Object $entity, bool $reset = false, int $maxRetries = 50): void
    {
        $this->anonymizer->anonymize($entity, $reset, $maxRetries);

        ++$this->anonymizedEntity;
    }
}
