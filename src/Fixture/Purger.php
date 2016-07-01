<?php

namespace DTL\Bolt\Extension\Fixtures\Fixture;

use Bolt\Storage\EntityManager;
use Webmozart\Assert\Assert;

class Purger
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function purge($class)
    {
        Assert::notEmpty($class);
        $repository = $this->entityManager->getRepository($class);

        $entities = $repository->findAll();

        if (!$entities) {
            return;
        }

        foreach ($entities as $entity) {
            $repository->delete($entity);
        }
    }
}
