<?php

declare(strict_types=1);

use App\Config;
use App\Enums\AppEnvironment;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Twig\Extra\Intl\IntlExtension;
use function DI\create;

return [
  Config::class                 => create(Config::class)->constructor(require CONFIG_PATH . '/app.php'),
  EntityManager::class          => function (Config $config) {
    $setup = ORMSetup::createAttributeMetadataConfiguration(
      paths: $config->get('doctrine.entity_dir'),
      isDevMode: $config->get('doctrine.dev_mode'),

    );
    $connection = DriverManager::getConnection($config->get('doctrine.connection'), $setup);
    return new EntityManager($connection, $setup);
  },
  Twig::class                    => function (Config $config,  ContainerInterface $container) {
    $twig = Twig::create(VIEW_PATH, [
      'cache' => STORAGE_PATH . '/cache',
      'auto_reload' =>  AppEnvironment::isDevelopment($config->get('app_environment')),
    ]);

    $twig->addExtension(new IntlExtension());
    $twig->addExtension(new EntryFilesTwigExtension($container));
    //$twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));
    return $twig;
  },
  /**
   * The following two bindings are needed for EntryFilesTwigExtension & AssetExtension to work for Twig
   */
  'webpack_encore.packages'     => fn () => new Packages(
    new Package(new JsonManifestVersionStrategy(BUILD_PATH . '/manifest.json'))
  ),
  'webpack_encore.tag_renderer' => fn (ContainerInterface $container) => new TagRenderer(
    new EntrypointLookupCollection($container, BUILD_PATH . '/entrypoints.json'),
    $container->get('webpack_encore.packages')
  ),
];
