<?php

namespace DTL\Bolt\Extension\Fixtures\Tests\Functional\Command;

use DTL\Bolt\Extension\Fixtures\Tests\Functional\ApplicationTestCase;
use DTL\Bolt\Extension\Fixtures\Command\ImportFixturesCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ImportFixturesCommandTest extends ApplicationTestCase
{
    private $tester;

    public function setUp()
    {
        $this->initDatabase();
        $loader = $this->getService('dtl.fixture.loader');
        $command = new ImportFixturesCommand($loader);

        $this->tester = new CommandTester($command);
    }

    public function testSomething()
    {
        $this->tester->execute([
            'file' => __DIR__ . '/fixtures/fixtures1.yml'
        ]);

        $connection = $this->getApplication()['db'];
        $stmt = $connection->query('SELECT * FROM bolt_pages ORDER BY id ASC');
        $records = $stmt->fetchAll();
        $this->assertCount(2, $records);

        $stmt = $connection->query('SELECT * FROM bolt_entries ORDER BY id ASC');
        $records = $stmt->fetchAll();
        $this->assertCount(3, $records);
    }
}
