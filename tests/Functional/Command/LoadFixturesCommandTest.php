<?php

namespace DTL\Bolt\Extension\Fixtures\Tests\Functional\Command;

use DTL\Bolt\Extension\Fixtures\Tests\Functional\ApplicationTestCase;
use DTL\Bolt\Extension\Fixtures\Command\ImportFixturesCommand;
use Symfony\Component\Console\Tester\CommandTester;
use DTL\Bolt\Extension\Fixtures\Command\LoadFixturesCommand;

class ImportFixturesCommandTest extends ApplicationTestCase
{
    private $tester;
    private $entityManager;

    public function setUp()
    {
        $this->initDatabase();
        $loader = $this->getService('dtl.fixture.loader');
        $this->entityManager = $this->getService('storage');
        $purger = $this->getService('dtl.fixture.purger');
        $command = new LoadFixturesCommand($loader, $this->entityManager, $purger);

        $this->tester = new CommandTester($command);
    }

    /**
     * It should set the slug field.
     */
    public function testSlug()
    {
        $this->tester->execute([
            'path' => __DIR__ . '/fixtures/slug.yml'
        ]);
        $this->assertEquals(0, $this->tester->getStatusCode());

        $records = $this->entityManager->getContent('pages');

        $this->assertCount(1, $records);

        $record = reset($records);
        $this->assertEquals('hellow-world', $record->get('slug'));
    }

    /**
     * It should set taxonomy fields
     */
    public function testTaxonomy()
    {
        $this->tester->execute([
            'path' => __DIR__ . '/fixtures/taxonomy.yml'
        ]);
        $this->assertEquals(0, $this->tester->getStatusCode());

        $records = $this->entityManager->getRepository('pages')->findAll();
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertEquals(2, $record->getTaxonomy()->offsetGet('groups')->count());
        $taxon = $record->getTaxonomy()->offsetGet('groups')->first();

        $this->assertEquals('one', $taxon->getName());
    }

    /**
     * It should set single relations.
     */
    public function testRelations()
    {
        $this->tester->execute([
            'path' => __DIR__ . '/fixtures/relations.yml'
        ]);
        $this->assertEquals(0, $this->tester->getStatusCode());

        $records = $this->entityManager->getContent('pages');
        $this->assertCount(1, $records);
        $record = reset($records);

        $relations = $record->relation;
        $this->assertArrayHasKey('people', $relations);
        $this->assertArrayHasKey('locations', $relations);

        $this->assertCount(1, $relations['locations']);
        $this->assertCount(2, $relations['people']);

        $location = $this->entityManager->find('locations', $relations['locations'][0]);
        $this->assertNotNull($location);
        $this->assertEquals('Location One', $location->getTitle());

        $person = $this->entityManager->find('people', $relations['people'][0]);
        $this->assertNotNull($person);
        $this->assertEquals('Daniel', $person->getName());
    }

    /**
     * It should import records and set fields.
     * It should output progress.
     */
    public function testPages()
    {
        $this->tester->execute([
            'path' => __DIR__ . '/fixtures/pages.yml'
        ]);

        $helloPages = $this->entityManager->getRepository('pages')->findBy([
            'title' => 'Hello',
        ]);
        $this->assertCount(10, $helloPages);

        foreach ($helloPages as $page) {
            $this->assertEquals('Body Hello', $page->get('body'));
        }

        $worldPages = $this->entityManager->getRepository('pages')->findBy([
            'title' => 'World',
        ]);
        $this->assertCount(10, $worldPages);

        foreach ($worldPages as $page) {
            $this->assertEquals('Body World', $page->get('body'));
        }

        $display = $this->tester->getDisplay();
        $this->assertContains(<<<EOT
Purging... pages
Loading objects... pages.yml

....................

Loaded 20 fixtures
EOT
        , $display);
    }
}
