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
        $relations = new Relations();
        $mapping = $this->getFieldMapping($fixture->getClass(), $property);

        $values = (array) $value;

        $collection = new TaxonomyCollection();
        $data = $mapping['data'];

        foreach ($values as $value) {
            $taxentity = new Taxonomy([
                'name'         => $data['label'],
                'content_id'   => $object->getId(),
                'contenttype'  => (string) $object->getContenttype(),
                'taxonomytype' => $data['behaves_like'],
                'slug'         => $value,
            ]);
            $collection->add($taxentity);
        }

        $object->setTaxonomy($collection);
    }
}
