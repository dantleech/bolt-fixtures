<?php

namespace DTL\Bolt\Extension\Fixtures;

use Bolt\Extension\SimpleExtension;
use DTL\Bolt\Extension\Fixtures\Command\LoadFixturesCommand;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\NutTrait;
use Silex\ServiceProviderInterface;
use Silex\Application;
use Nelmio\Alice\Fixtures\Loader;
use DTL\Bolt\Extension\Fixtures\Alice\Instantiator;
use DTL\Bolt\Extension\Fixtures\Alice\ReferencePopulator;
use DTL\Bolt\Extension\Fixtures\Alice\TaxonomyPopulator;
use DTL\Bolt\Extension\Fixtures\Alice\TemplateFieldsPopulator;
use DTL\Bolt\Extension\Fixtures\Fixture\Purger;
use DTL\Bolt\Extension\Fixtures\Alice\SlugPopulator;

class DtlBoltFixturesExtension extends AbstractExtension implements ServiceProviderInterface
{
    use NutTrait;

    public function registerNutCommands(Application $app)
    {
        return [
            new LoadFixturesCommand($app['dtl.fixture.loader'], $app['storage'], $app['dtl.fixture.purger'])
        ];
    }

    public function register(Application $app) 
    {
        $app['dtl.fixture.loader'] = function ($app) {
            $loader = new Loader('fr_FR');
            $loader->addInstantiator(new Instantiator($app['storage']));
            $loader->addPopulator(new ReferencePopulator($app['storage']));
            $loader->addPopulator(new TaxonomyPopulator($app['storage']));
            $loader->addPopulator(new TemplateFieldsPopulator($app['storage']));
            $loader->addPopulator(new SlugPopulator($app['storage'], $app['slugify']));
            return $loader;
        };

        $app['dtl.fixture.purger'] = function ($app) {
            return new Purger($app['storage']);
        };

        $this->extendNutService();
    }

    public function boot(Application $app)
    {
    }

    public function getServiceProviders()
    {
        return [ $this ];
    }
}
