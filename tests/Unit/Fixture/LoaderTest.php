<?php

namespace DTL\Bolt\Extension\Fixtures\Tests\Unit\Fixture;

use Bolt\Storage\EntityManager;
use DTL\Bolt\Extension\Fixtures\Fixture\Loader;
use Bolt\Legacy\Content;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    private $storage;

    public function setUp()
    {
        $this->storage = $this->prophesize(EntityManager::class);
        $this->loader = $this->prophesize(Loader::class);

        $this->content = $this->prophesize(Content::class);
    }

    /**
     * It should load a record.
     *
     * NOTE: Cannot use EntityManager here because we use legacy methods
     *       via. its magic methods!!
     */
    public function testLoader()
    {
        $this->loader->load('contenttype', 'ref1', [
            'key' => 'value',
        ]);

        $this->storage->getEmptyContent()->willReturn($this->content->reveal());
        $this->content->setValues([
            'datecreated' => Argument::type('string'),
            'ownerid' => 1,
        ])->shouldBeCalled();

        $this->storage->saveContent($this->record->reveal())->shouldBeCalled();
    }
}
