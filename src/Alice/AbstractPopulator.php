<?php

namespace DTL\Bolt\Extension\Fixtures\Alice;

use Nelmio\Alice\Instances\Populator\Methods\MethodInterface;
use Bolt\Storage\EntityManager;

abstract class AbstractPopulator implements MethodInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function getMetadataForClass($class)
    {
        $metadataDriver = $this->entityManager->getMapper();
        $metadata = $metadataDriver->loadMetadataForClass($class);

        return $metadata;
    }

    protected function getFieldMapping($class, $property)
    {
        $metadata = $this->getMetadataForClass($class);
        $fieldMappings = $metadata->getFieldMappings();

        if (!isset($fieldMappings[$property])) {
            throw new \InvalidArgumentException(sprintf(
                'Field "%s" for fixture class "%s" is not mapped, mapped fields: "%s"',
                $property, $fixture->getClass(), implode('", "', array_keys($fieldMappings))
            ));
        }

        $fieldMapping = $fieldMappings[$property];

        return $fieldMapping;
    }
}
