<?php

namespace DTL\Bolt\Extension\Fixtures\Fixture;

use Bolt\Storage\EntityManager;
use Bolt\Helpers\Arr;
use Webmozart\Assert\Assert;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Bolt\Storage\Entity\Content;
use Bolt\Storage\Collection\Relations;
use Bolt\Storage\Entity\Relations as EntityRelations;
use Bolt\Storage\Collection\Taxonomy as TaxonomyCollection;
use Bolt\Storage\Entity\Taxonomy;

class Loader
{
    private $storage;
    private $references;

    public function __construct(EntityManager $storage)
    {
        $this->storage = $storage;
        $this->references = [];
    }

    public function purge($contentType)
    {
        $repository = $this->storage->getRepository($contentType);
        foreach ($repository->findAll() as $entity) {
            $repository->delete($entity);
        }
    }

    /**
     * @var array $import
     */
    public function load($contentType, $ref, array $data)
    {
        $meta = [
            'status' => 'published',
            'datecreated' => date('Y-m-d H:i:s'),
            'ownerid'     => 1,
        ];

        $values = Arr::mergeRecursiveDistinct($data, $meta);

        $record = $this->storage->create($contentType, []);
        $propertyAccessor = new PropertyAccessor();
        foreach ($values as $index => $value) {
            if (is_array($value)) {
                $value[$index] = $this->resolveNode($value, $record);
            }

            $propertyAccessor->setValue($record, $index, $value);
            $record->set($index, $value);
        }

        $this->storage->save($record);

        $this->references[$contentType][$ref] = $record;
    }

    private function resolveNode(array $node, Content $record)
    {
        if (!isset($node['type'])) {
            return $node;
        }
        $validTypes = [ 'reference' ];

        switch ($node['type']) {
            case 'reference':
                return $this->resolveReference($node, $record);
            case 'taxonomy':
                return $this->resolveTaxon($node, $record);
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

        $relations = new Relations();
        $newentity = new EntityRelations([
            'from_contenttype' => $record->getContentType(),
            'from_id'          => $record->getId(),
            'to_contenttype'   => $nodeContentType,
            'to_id'            => $value,
        ]);
        $relations->add($newentity);
        $record->setRelation($relations);
    }

    private function resolveTaxon(array $node, Content $record)
    {
        Assert::keyExists($node, 'taxons');
        Assert::keyExists($node, 'taxonomytype');

        $collection = new TaxonomyCollection();

        foreach ($node['taxons'] as $value) {
            $taxentity = new Taxonomy([
                'name'         => $value,
                'content_id'   => $record->getId(),
                'contenttype'  => (string) $record->getContenttype(),
                'taxonomytype' => $node['taxonomytype'],
                'slug'         => $value,
            ]);
            $collection->add($taxentity);
        }

        $record->setTaxonomy($collection);
    }
}
