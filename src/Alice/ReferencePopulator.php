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

        if ($mapping['fieldtype'] == RelationType::class) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $values)
    {
        $metadata = $this->getMetadataForClass($fixture->getClass());
        $mapping = $this->getFieldMapping($fixture->getClass(), $property);

        if ($mapping['data']['multiple'] === false) {
            if (!is_object($values)) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected single object for non-multiple relattionship for "%s/%s", got "%s"',
                    $fixture->getClass(), $property, gettype($value)
                ));
            }

            $values = [ $values ];
        }

        if (!is_array($values)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected array for multiple relattionship for "%s/%s", got "%s"',
                $fixture->getClass(), $property, gettype($value)
            ));
        }

        foreach ($values as $value) {
            // hmm.. ensure we have an ID
            $this->getEntityManager()->save($value);

            $newentity = new EntityRelations($d = [
                'from_contenttype' => (string) $metadata->getBoltName(),
                'from_id'          => $object->getId(),
                'to_contenttype'   => $value->getContentType(),
                'to_id'            => $value->getId(),
            ]);
            $object->getRelation()->add($newentity);
        }
    }
}
