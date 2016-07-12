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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

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
        $this->addArgument('path', InputArgument::REQUIRED);
        $this->addOption('no-purge', null, InputOption::VALUE_NONE, 'Do not purge the fixture classes before loading');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        if (is_file($path)) {
            $files = new \ArrayIterator([ new \SplFileInfo($path) ]);
        } else {
            $files = new Finder();
            $files->in($path);
            $files->name('*.yml');
        }

        $files = iterator_to_array($files);


        $start = microtime(true);
        if (false === $input->getOption('no-purge')) {
            $output->write('<info>Purging...</>');
            foreach ($files as $file) {
                $purged = [];
                $yaml = Yaml::parse(file_get_contents($file->getPathname()));

                if (null === $yaml) {
                    continue;
                }

                foreach (array_keys($yaml) as $class) {
                    $output->write(sprintf(' <comment>%s</>', $class));
                    $this->purger->purge($class);
                    $purged[$class] = true;
                }
            }
        }

        asort($files);

        $objects = [];
        $output->write(PHP_EOL);
        $output->write('<info>Loading objects...</info>');
        foreach ($files as $file) {
            $output->write(sprintf(' <comment>%s</>', $file->getFilename()));
            foreach ($this->loader->load($file->getPathname()) as $object) {
                $objects[] = $object;
            }
        }
        $output->write(PHP_EOL . PHP_EOL);

        $progress = 1;
        $nbObjects = count($objects);
        foreach ($objects as $object) {
            $this->entityManager->save($object);

            $output->write('.');

            if ($progress > 0 && $progress % 60 === 0) {
                $output->writeln(sprintf(
                    ' %3s / %3s (%3s%%)', $progress, $nbObjects, 
                    floor(($progress / $nbObjects) * 100)
                ));
            }
            $progress++;
        }
        $end = microtime(true);

        $output->write(PHP_EOL);
        $output->write(PHP_EOL);
        $output->writeln(sprintf('<info>Loaded %s fixtures in %s seconds</>', $nbObjects, number_format($end - $start, 2)));
    }
}
