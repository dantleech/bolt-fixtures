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
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;
use Nelmio\Alice\Fixtures\Loader;
use DTL\Bolt\Extension\Fixtures\Alice\Instantiator;
use Bolt\Storage\Entity\Content;
use DTL\Bolt\Extension\Fixtures\Fixture\Purger;

class LoadFixturesCommand extends Command
{
    private $loader;
    private $entityManager;
    private $purger;

    public function __construct(
        Loader $loader,
        EntityManager $entityManager,
        Purger $purger
    )
    {
        parent::__construct();
        $this->loader = $loader;
        $this->entityManager = $entityManager;
        $this->purger = $purger;
    }

    public function configure()
    {
        $this->setName('dtl:fixtures:load');
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        $objects = $this->loader->load($file);

        $purged = [];

        foreach ($objects as $object) {

            if ($object instanceof Content) {
                $class = (string) $object->getContentType();
            } else {
                $class = get_class($object);
            }

            if (!isset($purged[$class])) {
                $output->writeln(sprintf('Purging "%s"', $class));
                $this->purger->purge($class);
                $purged[$class] = true;
            }
        }

        foreach ($objects as $object) {
            $this->entityManager->save($object);
            $output->write('.');
        }
    }
}
