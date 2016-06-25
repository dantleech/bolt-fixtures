<?php

namespace DTL\Bolt\Extension\Fixtures;

use Bolt\Extension\SimpleExtension;
use DTL\Bolt\Extension\Fixtures\Command\ImportFixturesCommand;
use DTL\Bolt\Extension\Fixtures\Fixture\Loader;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\NutTrait;
use Silex\ServiceProviderInterface;
use Silex\Application;

class DtlBoltFixturesExtension extends AbstractExtension implements ServiceProviderInterface
{
    use NutTrait;

    public function registerNutCommands(Application $app)
    {
        return [
            new ImportFixturesCommand($app['dtl.fixture.loader'])
        ];
    }

    public function register(Application $app) 
    {
        $app['dtl.fixture.loader'] = function ($app) {
            return new Loader($app['storage']);
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
