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

class TemplateFieldsPopulator extends AbstractPopulator
{
    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        $mapping = $this->getFieldMapping($fixture->getClass(), $property);

        if ($mapping['fieldtype'] == TemplateFieldsType::class) {
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

        if (!$template = $object->getTemplate()) {
            throw new \InvalidArgumentException(sprintf(
                'Template property must be set before template fields in "%s/%s"',
                $fixture->getClass(), $property
            ));
        }

        $config = $mapping['config'];

        if (!isset($config[$template])) {
            throw new \InvalidArgumentException(sprintf(
                'Template "%s" is not an available template, available templates: "%s"',
                implode('", "', array_keys($config))
            ));
        }

        $fields = [];
        foreach ($config[$template]['fields'] as $field => $fieldData) {
            if (isset($value[$field])) {
                $fields[$field] = $value;
                continue;
            }

            $fields[$field] = isset($fieldData['default']) ? $fieldData['default'] : null;
        }

        $object->setTemplatefields($fields);
    }
}
