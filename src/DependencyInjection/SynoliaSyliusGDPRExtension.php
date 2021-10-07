<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Synolia\SyliusGDPRPlugin\DependencyInjection\CompilerPass\RegisterAdvancedActionsFormDataProcessorsPass;
use Synolia\SyliusGDPRPlugin\Loader\LoaderInterface;
use Synolia\SyliusGDPRPlugin\Processor\AdvancedActions\AdvancedActionsFormDataProcessorInterface;
use Synolia\SyliusGDPRPlugin\Validator\ArrayMappingValidator;

final class SynoliaSyliusGDPRExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(LoaderInterface::class)
            ->addTag('anonymization_loader');
        $mapping = $this->retrieveMappings($configs, $config['disable_default_mappings']);

        $container->setParameter('synolia_anonymization_mapping', $mapping);

        $container
            ->registerForAutoconfiguration(AdvancedActionsFormDataProcessorInterface::class)
            ->addTag(RegisterAdvancedActionsFormDataProcessorsPass::PROCESSOR_SERVICE_TAG)
        ;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    /**
     * @see https://www.php.net/manual/en/function.array-merge-recursive.php#96201
     *
     * @param mixed[] $base
     * @param mixed[] $replacement
     *
     * @return array<string, array>
     */
    private static function mergeConfig(array $base, array $replacement): array
    {
        foreach ($replacement as $key => $value) {
            if (!\array_key_exists($key, $base) && !\is_numeric($key)) {
                $base[$key] = $replacement[$key];

                continue;
            }
            if (\is_array($value) || (\array_key_exists($key, $base) && \is_array($base[$key]))) {
                $base[$key] = self::mergeConfig($base[$key], $replacement[$key]);
            } elseif (\is_numeric($key)) {
                if (!\in_array($value, $base, true)) {
                    $base[] = $value;
                }
            } else {
                $base = $replacement;
            }
        }

        return $base;
    }

    private function retrieveMappings(array $configs, bool $disableDefaultMappings = false): array
    {
        $defaultConfig = [
            'anonymization' => [
                'mappings' => [
                    'paths' => [
                        __DIR__ . '/../Resources/config/mappings/',
                    ],
                ],
            ],
        ];

        $mappings = $disableDefaultMappings ? [] : $this->retrieveMapping($defaultConfig);

        foreach ($configs as $config) {
            $mappings = array_merge($mappings, $this->retrieveMapping($config));
        }

        return $mappings;
    }

    private function retrieveMapping(array $config): array
    {
        $mappings = [];
        if (!isset($config['anonymization']['mappings']['paths'])) {
            return $mappings;
        }
        foreach ($config['anonymization']['mappings']['paths'] as $filePath) {
            if (!\file_exists($filePath) || !\is_dir($filePath)) {
                throw new \LogicException('The directory ' . $filePath . ' does not exist.');
            }
            $mappings[] = $this->validateYamlParse($filePath);
        }

        return $mappings;
    }

    private function validateYamlParse($filePath): array
    {
        $finder = new Finder();
        $finder->in($filePath)->name(['*.yml', '*.yaml'])->sortByName(true)->reverseSorting();
        $mappings = [];
        foreach ($finder as $file) {
            $mappings = $this::mergeConfig($mappings, Yaml::parse($file->getContents()) ?? []);
        }

        foreach ($mappings as $className => $content) {
            (new ArrayMappingValidator())->checkParse($content, $className);
        }

        return $mappings;
    }
}
