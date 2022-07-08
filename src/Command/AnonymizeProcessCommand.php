<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor;

final class AnonymizeProcessCommand extends Command
{
    private const MAX_RETRIES = 10000;

    protected static $defaultName = 'synolia:gdpr:anonymize';

    private AnonymizerProcessor $anonymizerProcessor;

    private EntityManagerInterface $entityManager;

    private SymfonyStyle $io;

    private bool $reset;

    private int $maxRetries;

    public function __construct(
        AnonymizerProcessor $anonymizerProcessor,
        EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);

        $this->anonymizerProcessor = $anonymizerProcessor;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Change proprieties data entity which have the annotation anonymize.')
            ->addOption('entity', 'E', InputOption::VALUE_REQUIRED, 'Entity full qualified class name')
            ->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'Object ID')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force command')
            ->addOption('reset', null, InputOption::VALUE_OPTIONAL, 'Reset unique', false)
            ->addOption('max-retries', null, InputOption::VALUE_OPTIONAL, 'Maximum unique retries', (string) self::MAX_RETRIES)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $className = $input->getOption('entity');
        $force = (bool) $input->getOption('force');
        $this->reset = (bool) $input->getOption('reset');
        $this->maxRetries = \is_string($input->getOption('max-retries')) ? (int) $input->getOption('max-retries') : self::MAX_RETRIES;

        try {
            if (null !== $className) {
                /** @var int|string|array|null $id */
                $id = $input->getOption('id');
                if (\is_array($id) || !is_string($className)) {
                    throw new \LogicException('Invalid parameters');
                }
                if (null === $id) {
                    $this->anonymizeEntityForClassName($className, null, $force);

                    return 0;
                }
                $this->anonymizeEntityForClassName($className, (string) $id, $force);

                return 0;
            }
            $this->io->error('Options are empty. Use --help to get the doc.');

            return 0;
        } catch (\LogicException $exception) {
            $this->io->error($exception->getMessage());

            return 1;
        }
    }

    private function anonymizeEntityForClassName(string $className, ?string $id = null, bool $force): void
    {
        try {
            $entity = $this->entityManager->getMetadataFactory()->getMetadataFor($className);
        } catch (\Exception $exception) {
            throw new \LogicException('Entity does not exist', 1, $exception);
        }
        if (false === $force) {
            $response = $this->io->confirm(
                'Are you sure to anonymize this entity (' . $className . ') ? Data will be changed without back-up.',
                false,
            );
            if (true !== $response) {
                throw new \LogicException('No data has been changed.');
            }
        }

        $this->processWithClassMetadata($entity, $id);

        $this->io->success('Your data has been changed with success !');
    }

    private function processWithClassMetadata(ClassMetadata $entity, ?string $id = null): void
    {
        $results = null;
        if (null !== $id) {
            $results = [$this->entityManager->getRepository($entity->getName())->find($id)];
            if (null === $results[0]) {
                throw new \LogicException('The ID ' . $id . ' does not exist.');
            }
        }

        if (null === $results) {
            $results = $this->entityManager->getRepository($entity->getName())->findAll();
        }

        $this->anonymizerProcessor->anonymizeEntities($results, $this->reset, $this->maxRetries);
    }
}
