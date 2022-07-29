<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\EventSubscriber;

use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Synolia\SyliusGDPRPlugin\Event\BeforeAnonymize;
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

class AnonymizeAddressLogEntrySubscriber implements EventSubscriberInterface
{
    private RepositoryInterface $addressLogEntryRepository;

    private AnonymizerInterface $anonymizer;

    public function __construct(AnonymizerInterface $anonymizer, RepositoryInterface $addressLogEntryRepository)
    {
        $this->addressLogEntryRepository = $addressLogEntryRepository;
        $this->anonymizer = $anonymizer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeAnonymize::class => [
                'process',
            ],
        ];
    }

    public function process(BeforeAnonymize $beforeAnonymize): void
    {
        $address = $beforeAnonymize->getEntity();
        if (!$address instanceof AddressInterface) {
            return;
        }

        $addressLogEntries = $this->addressLogEntryRepository->findBy(['objectId' => $address->getId()]);
        if ([] === $addressLogEntries) {
            return;
        }

        foreach ($addressLogEntries as $addressLogEntry) {
            $this->anonymizer->anonymize($addressLogEntry);
        }
    }
}
