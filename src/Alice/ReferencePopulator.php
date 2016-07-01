<?php

namespace DTL\Bolt\Extension\Fixtures\Alice;

use Nelmio\Alice\Instances\Populator\Methods\MethodInterface;
use Nelmio\Alice\Fixtures\Fixture;
use Bolt\Storage\EntityManager;
use Bolt\Storage\Field\Type\RelationType;
use Bolt\Storage\Entity\Relations as EntityRelations;
use Bolt\Storage\Collection\Relations;

class ReferencePopulator extends AbstractPopulator
{
    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        $mapping = $this->getFieldMapping($fixture->getClass(), $property);

        if ($fieldMapping['fieldtype'] == RelationType::class) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $relations = new Relations();
        $metadata = $this->getMetadataForClass($fixture->getClass());

        $values = (array) $value;

        foreach ($values as $value) {
            // hmm.. ensure we have an ID
            $this->getEntityManager()->save($value);

            $newentity = new EntityRelations([
                'from_contenttype' => $metadata->getName(),
                'from_id'          => $object->getId(),
                'to_contenttype'   => $value->getContentType(),
                'to_id'            => $value->getId(),
            ]);
            $relations->add($newentity);
        }
        $object->setRelation($relations);
    }
}
