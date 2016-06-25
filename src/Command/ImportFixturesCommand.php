<?php

namespace DTL\Bolt\Extension\Fixtures\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use PhpBench\Dom\Document;
use Bolt\Storage\EntityManager;
use Bolt\Helpers\Arr;
use Cocur\Slugify\Slugify;
use Bolt\Storage\Entity\Taxonomy;
use DTL\Bolt\Extension\Fixtures\Fixture\Loader;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class ImportFixturesCommand extends Command
{
    private $loader;

    public function __construct(Loader $loader)
    {
        parent::__construct();
        $this->loader = $loader;
    }

    public function configure()
    {
        $this->setName('dtl:fixtures:load');
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $data = Yaml::parse($file);

        foreach ($data as $contentType => $records) {
            Assert::isArray($records);

            foreach ($records as $ref => $record) {
                Assert::isArray($record);

                $this->loader->load($contentType, $ref, $record);
            }
        }
    }

    private function importPost(\DomElement $postEl)
    {
        $title = $postEl->evaluate('string(./sv:property[@sv:name="title"]/sv:value)');
        $body = $postEl->evaluate('string(./sv:property[@sv:name="content"]/sv:value)');
        $published = $postEl->evaluate('boolean(./sv:property[@sv:name="published"]/sv:value)');
        $date = new \DateTime($postEl->evaluate('string(./sv:property[@sv:name="date"]/sv:value)'));

        $tags = [];
        foreach ($postEl->query('./sv:property[@sv:name="tags"]/sv:value') as $tagEl) {
            $tags[] = $tagEl->nodeValue;
        }

        $values = [
            'title' => $title,
            'body' => $body,
        ];
        $meta = [
            'slug'        => $this->slugify->slugify($title),
            'datecreated' => $date->format('Y-m-d H:i:s'),
            'datepublish' => $published ? $date->format('Y-m-d H:i:s') : null,
            'ownerid'     => 1,
            'status'      => $published ? 'published' : 'not-published'
        ];

        $values = Arr::mergeRecursiveDistinct($values, $meta);

        $record = $this->storage->getEmptyContent('post');
        $record->setValues($values);
        $record->setDatecreated($date);
        $record->setDatepublish($date);
        $record->setTaxonomy('tags', $tags);
        $this->storage->saveContent($record);
    }

    private function importPage(\DomElement $pageEl)
    {
        $title = $pageEl->evaluate('string(./sv:property[@sv:name="title"]/sv:value)');
        $body = $pageEl->evaluate('string(./sv:property[@sv:name="content"]/sv:value)');
        $published = $pageEl->evaluate('boolean(./sv:property[@sv:name="published"]/sv:value)');
        $date = new \DateTime($pageEl->evaluate('string(./sv:property[@sv:name="date"]/sv:value)'));

        $values = [
            'title' => $title,
            'body' => $body,
        ];
        $meta = [
            'slug'        => $this->slugify->slugify($title),
            'ownerid'     => 1,
            'title' => $title,
            'status'      => $published ? 'published' : 'not-published'
        ];

        $values = Arr::mergeRecursiveDistinct($values, $meta);

        $record = $this->storage->getEmptyContent('page');
        $record->setValues($values);
        $this->storage->saveContent($record);
    }
}
