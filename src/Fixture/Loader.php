<?php

namespace DTL\Bolt\Extension\Fixtures\Fixture;

use Bolt\Storage\EntityManager;
use Bolt\Helpers\Arr;
use Webmozart\Assert\Assert;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Bolt\Legacy\Content;

class Loader
{
    private $storage;
    private $references;

    public function __construct(EntityManager $storage)
    {
        $this->storage = $storage;
        $this->references = [];
    }

    /**
     * @var array $import
     */
    public function load($contentType, $ref, array $data)
    {
        $meta = [
            'slug'        => $slug,
            'datecreated' => date('Y-m-d H:i:s'),
            'datepublish' => $status == 'published' ? date('Y-m-d H:i:s') : null,
            'ownerid'     => 1,
        ];

        $values = Arr::mergeRecursiveDistinct($data, $meta);

        $record = $this->storage->getEmptyContent($contentType);
        foreach ($values as $index => $value) {
            if (is_array($value)) {
                $this->resolveNode($value, $record);
            }
        }

        $record->setValues($values);
        $this->storage->saveContent($record);

        $this->references[$contentType][$ref] = $record;
    }

    private function resolveNode(array $node, Content $record)
    {
        Assert::keyExists($node, 'type');
        $validTypes = [ 'reference' ];

        switch ($node['type']) {
            case 'reference':
                return $this->resolveReference($node, $record);
            default:
                throw new \RuntimeException(sprintf(
                    'Unknown reference fixture node type "%s", good types: "%s"',
                    $node['type'], implode('", "', $validTypes)
                ));
        }
    }

    private function resolveReference(array $node, Content $record)
    {
        $accessor = new PropertyAccessor();
        Assert::keyExists($node, 'reference', 'Fixture node reference node must have "reference" property');
        Assert::keyExists($node, 'property', 'Fixture node reference node must have "property" property');
        Assert::keyExists($node, 'contenttype', 'Fixture node reference node must have "contenttype" property');

        $nodeReference = $node['reference'];
        $nodeContentType = $node['contenttype'];

        if (!isset($this->references[$nodeContentType])) {

            throw new \RuntimeException(sprintf(
                'Unknown fixture reference content type "%s". Order matters. Known references: "%s"',
                $nodeContentType, implode('", "', array_keys($this->references))
            ));
        }

        if (!isset($this->references[$nodeContentType][$nodeReference])) {
            throw new \RuntimeException(sprintf(
                'Unknown reference for content type "%s". Known references: "%s"',
                $nodeContentType, $nodeReference, implode('", "', array_keys($this->references[$nodeContentType]))
            ));
        }

        $reference = $this->references[$nodeContentType][$nodeReference];
        $value = $accessor->getValue($reference, $node['property']);

        $record->setRelation($nodeContentType, $value);
    }
}

