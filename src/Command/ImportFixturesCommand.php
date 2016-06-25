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
            $output->writeln(sprintf('<info>Purging:</info> %s"', $contentType));
            $this->loader->purge($contentType);

            foreach ($records as $ref => $record) {
                Assert::isArray($record);
                $output->writeln(sprintf('  <info>Loading:</info> %s', $ref));

                $this->loader->load($contentType, $ref, $record);
            }
        }
    }
}
