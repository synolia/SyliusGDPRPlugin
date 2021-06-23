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
use Synolia\SyliusGDPRPlugin\Provider\AnonymizerInterface;

final class AnonymizeProcessCommand extends Command
{
    private const MODULO_FLUSH = 50;
    private const MAX_RETRIES = 10000;
    protected static $defaultName = 'synolia:gdpr:anonymize';

    /**
     * @var AnonymizerInterface
     */
    private $anonymizer;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var bool
     */
    private $reset;
    /**
     * @var int
     */
    private $maxRetries;

    public function __construct(
        AnonymizerInterface $anonymizer,
        EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
        $this->anonymizer = $anonymizer;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Change proprieties data entity which have the annotation anonymize.')
            ->addOption('entity', 'E', InputOption::VALUE_REQUIRED, 'Entity full qualified class name')
            ->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'Object ID')
            ->addOption('all', null, InputOption::VALUE_NONE, 'All entities')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force command')
            ->addOption('reset', null, InputOption::VALUE_OPTIONAL, 'Reset unique', false)
            ->addOption('max-retries', null, InputOption::VALUE_OPTIONAL, 'Maximum unique restries', self::MAX_RETRIES)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $className = $input->getOption('entity');
        $all = (bool) $input->getOption('all');
        $force = (bool) $input->getOption('force');
        $this->reset = (bool) $input->getOption('reset');
        $this->maxRetries = \is_string($input->getOption('max-retries')) ? (int) $input->getOption('max-retries') : self::MAX_RETRIES;

        try {
            if (true === $all) {
                if (false === $force) {
                    if (true !== $this->io->confirm('Are you sure to anonymize every entities ? Data will be changed without back-up.', false)) {
                        $this->io->error('No data has been changed.');

                        return 0;
                    }
                }

                $this->anonymizeAllEntities();
                $this->io->success('Your data has been changed with success !');

                return 0;
            }

            if (null !== $className) {
                $id = $input->getOption('id');
                if (\is_array($id) || \is_array($className)) {
                    throw new \LogicException('Invalid parameters');
                }
                if (null === $id) {
                    $this->anonymizeEntityForClassName((string) $className, null, $force);

                    return 0;
                }
                $this->anonymizeEntityForClassName((string) $className, (string) $id, $force);

                return 0;
            }
            $this->io->error('Options are empty. Use --help to get the doc.');

            return 0;
        } catch (\LogicException $exception) {
            $this->io->error($exception->getMessage());

            return 1;
        }
    }

    private function anonymizeAllEntities(): void
    {
        $entities = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if (empty($entities)) {
            throw new \LogicException('No entities found.');
        }
        foreach ($entities as $entity) {
            $this->processWithClassMetadata($entity);
        }
    }

    private function anonymizeEntityForClassName(string $className, $force, ?string $id = null): void
    {
        try {
            $entity = $this->entityManager->getMetadataFactory()->getMetadataFor($className);
        } catch (\Exception $exception) {
            throw new \LogicException('Entity does not exist');
        }
        if (false === $force) {
            $response = $this->io->confirm(
                'Are you sure to anonymize this entity (' . $className . ') ? Data will be changed without back-up.',
                false
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
        $this->anonymizeEntities($results);
    }

    private function anonymizeEntities(array $results): void
    {
        foreach ($results as $index => $result) {
            $this->anonymizeEntity($result);

            if (0 === $index % self::MODULO_FLUSH) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();
    }

    private function anonymizeEntity($result): void
    {
        $this->anonymizer->anonymize($result, $this->reset, $this->maxRetries);
    }
}
