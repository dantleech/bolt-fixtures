<?php

namespace DTL\Bolt\Extension\Fixtures\Alice;

use Nelmio\Alice\Instances\Populator\Methods\MethodInterface;
use Nelmio\Alice\Fixtures\Fixture;
use Bolt\Storage\EntityManager;
use Bolt\Storage\Field\Type\RelationType;
use Bolt\Storage\Entity\Relations as EntityRelations;
use Bolt\Storage\Collection\Relations;
use Bolt\Storage\Field\Type\TemplateFieldsType;
use Bolt\Storage\Entity\TemplateFields;
use Bolt\Storage\Field\Type\SlugType;
use Cocur\Slugify\Slugify;

class SlugPopulator extends AbstractPopulator
{
    private $slugify;

    public function __construct(EntityManager $entityManager, Slugify $slugify)
    {
        parent::__construct($entityManager);
        $this->slugify = $slugify;
    }

    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        $mapping = $this->getFieldMapping($fixture->getClass(), $property);

        if ($mapping['fieldtype'] == SlugType::class) {
            return !empty($object->getTitle());
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $object->setSlug($this->slugify->slugify($object->getTitle()));
    }
}
