<?php

namespace DTL\Bolt\Extension\Fixtures\Tests\Functional;

use Silex\Application;
use DTL\Bolt\Extension\Fixtures\DtlBoltFixturesExtension;

abstract class ApplicationTestCase extends \PHPUnit_Framework_TestCase
{
    static $application;

    protected function getApplication()
    {
        if (self::$application) {
            return self::$application;
        }

        self::$application = require_once(__DIR__ . '/../../vendor/bolt/bolt/app/bootstrap.php');

        $extension = new DtlBoltFixturesExtension(self::$application);
        $extension->setContainer(self::$application);
        $extension->register(self::$application);

        return self::$application;
    }

    protected function initDatabase()
    {
        $this->getApplication()['schema']->update();
    }

    protected function getService($serviceId)
    {
        $application = $this->getApplication();
        return $application[$serviceId];
    }
}
