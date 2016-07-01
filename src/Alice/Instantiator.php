<?php

namespace DTL\Bolt\Extension\Fixtures\Alice;

use Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface;
use Nelmio\Alice\Fixtures\Fixture;
use Bolt\Storage\EntityManager;

class Instantiator implements MethodInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function canInstantiate(Fixture $fixture)
    {
        $mapping = $this->entityManager->getMapper();

        try {
            $mapping->loadMetadataForClass($fixture->getClass());

            return true;
        } catch (\StorageException $e) {
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate(Fixture $fixture)
    {
        return $this->entityManager->create($fixture->getClass(), [
            'status' => 'published',
            'datepublish' => date('Y-m-d H:i:s'),
            'datecreated' => date('Y-m-d H:i:s'),
            'ownerid'     => 1,
        ]);
    }
}
