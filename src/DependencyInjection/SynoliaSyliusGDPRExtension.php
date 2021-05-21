<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\DependencyInjection;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Synolia\SyliusGDPRPlugin\DependencyInjection\Configuration;
use Synolia\SyliusGDPRPlugin\Loader\LoaderInterface;
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

        $this->arrayMappingValidator = new ArrayMappingValidator();

        $mapping = $this->retrieveMappings($configs);
        $container->setParameter('synolia_anonymization_mapping', $mapping);
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
                $base[$key] = $value;
            }
        }

        return $base;
    }

    private function retrieveMappings(array $configs): array
    {
        $mappings = [];
        foreach ($configs as $config) {
            $mappings = array_merge($mappings, $this->retrieveMapping($config));
        }

        return $mappings;
    }

    private function retrieveMapping(array $configs): array
    {
        $mappings = [];
        if (!isset($configs['anonymization']['mappings']['path'])) {
            return $mappings;
        }
        foreach ($configs['anonymization']['mappings']['path'] as $filePath) {
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
        $finder->in($filePath)->name(['*.yml', '*.yaml']);
        $mappings = [];
        foreach ($finder as $file) {
            $mappings = $this::mergeConfig($mappings, Yaml::parse($file->getContents()) ?? []);
        }

        foreach ($mappings as $className => $content) {
            $this->arrayMappingValidator->checkParse($content, $className);
        }

        return $mappings;
    }
}
