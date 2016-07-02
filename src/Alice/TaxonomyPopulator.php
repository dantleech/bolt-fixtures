<?php

namespace DTL\Bolt\Extension\Fixtures\Alice;

use Nelmio\Alice\Instances\Populator\Methods\MethodInterface;
use Nelmio\Alice\Fixtures\Fixture;
use Bolt\Storage\EntityManager;
use Bolt\Storage\Field\Type\RelationType;
use Bolt\Storage\Entity\Relations as EntityRelations;
use Bolt\Storage\Collection\Taxonomy as TaxonomyCollection;
use Bolt\Storage\Collection\Relations;
use Bolt\Storage\Field\Type\TaxonomyType;
use Bolt\Storage\Entity\Taxonomy;

class TaxonomyPopulator extends AbstractPopulator
{
    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        $mapping = $this->getFieldMapping($fixture->getClass(), $property);

        if ($mapping['fieldtype'] == TaxonomyType::class) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $mapping = $this->getFieldMapping($fixture->getClass(), $property);

        $values = (array) $value;

        $data = $mapping['data'];

        foreach ($values as $value) {
            if (null === $object->getId()) {
                $this->getEntityManager()->save($object);
            }

            $taxentity = new Taxonomy([
                'name'         => $value,
                'content_id'   => $object->getId(),
                'contenttype'  => (string) $object->getContenttype(),
                'taxonomytype' => $property,
                'slug'         => $value,
            ]);

            $object->getTaxonomy()->add($taxentity);
        }
    }
}
