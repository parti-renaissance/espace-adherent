<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),
            new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
            new Lexik\Bundle\PayboxBundle\LexikPayboxBundle(),
            new Csa\Bundle\GuzzleBundle\CsaGuzzleBundle(),
            new Scheb\TwoFactorBundle\SchebTwoFactorBundle(),
            new Bazinga\Bundle\GeocoderBundle\BazingaGeocoderBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle(),

            // A2lix
            new A2lix\I18nDoctrineBundle\A2lixI18nDoctrineBundle(),
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),

            // Sonata
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Pix\SortableBehaviorBundle\PixSortableBehaviorBundle(),

            // KnpLabs
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Knp\Bundle\TimeBundle\KnpTimeBundle(),

            new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),

            new ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle(),
            new AppBundle\AppBundle(),
        ];

        if ('prod' === $this->getEnvironment()) {
            $bundles[] = new Algolia\AlgoliaSearchBundle\AlgoliaAlgoliaSearchBundle();
        }

        if (\in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        if ('test' === $this->getEnvironment()) {
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
            $bundles[] = new DAMA\DoctrineTestBundle\DAMADoctrineTestBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return \dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return \dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
