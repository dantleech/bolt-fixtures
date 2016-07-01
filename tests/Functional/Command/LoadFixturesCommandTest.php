<?php

namespace DTL\Bolt\Extension\Fixtures\Tests\Functional\Command;

use DTL\Bolt\Extension\Fixtures\Tests\Functional\ApplicationTestCase;
use DTL\Bolt\Extension\Fixtures\Command\ImportFixturesCommand;
use Symfony\Component\Console\Tester\CommandTester;
use DTL\Bolt\Extension\Fixtures\Command\LoadFixturesCommand;

class ImportFixturesCommandTest extends ApplicationTestCase
{
    private $tester;

    public function setUp()
    {
        $this->initDatabase();
        $loader = $this->getService('dtl.fixture.loader');
        $entityManager = $this->getService('storage');
        $purger = $this->getService('dtl.fixture.purger');
        $command = new LoadFixturesCommand($loader, $entityManager, $purger);

        $this->tester = new CommandTester($command);
    }

    /**
     * It should import records.
     */
    public function testSomething()
    {
        $connection = $this->getApplication()['db'];
        $stmt = $connection->query('DELETE FROM bolt_pages');
        $this->tester->execute([
            'path' => __DIR__ . '/fixtures'
        ]);

        $stmt = $connection->query('SELECT * FROM bolt_pages ORDER BY id ASC');
        $records = $stmt->fetchAll();
        $this->assertCount(20, $records);
    }
}
