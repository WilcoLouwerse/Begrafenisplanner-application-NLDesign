<?php

// src/Command/CreateUserCommand.php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class PubliccodeCommand extends Command
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('app:publiccode:update')
        // the short description shown while running "php bin/console list"
        ->setDescription('Creates a new public chart.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('see ehttps://github.com/italia/publiccode.yml')
        ->setDescription('Publiccode.yml is a metadata standard for repositories containing software developed or acquired by the Public Administration, aimed at making them easily discoverabile and thus reusable by other entities.By including a publiccode.yml file in the root of a repository, and populating it with information about the software, technicians and civil servants can evaluate it. Automatic indexing tools can also be built, since the format is easily readable by both humans and machines.')
        ->addOption('location', null, InputOption::VALUE_OPTIONAL, 'Write output to files in the given location', '/srv/api/public/schema/')
        ->addOption('spec-version', null, InputOption::VALUE_OPTIONAL, 'Publiccode version to use ("0.1.0")', '0.1.0');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $version */
        $version = $input->getOption('spec-version');

        //if (!\in_array($version, ['0.1.0'], true)) {
        //	throw new InvalidOptionException(sprintf('This tool only supports version 2 and 3 of the OpenAPI specification ("%s" given).', $version));
        //}

        $publiccode = $this->twig->render('publiccode/publiccode.yaml.twig');

        if (!empty($location = $input->getOption('location')) && \is_string($location)) {
            file_put_contents($location.'/publiccode.yaml', $publiccode);
            $io->success(sprintf('Data written to %s/publiccode.yaml (specification version %s).', $location, $version));
        } else {
            // outputs multiple lines to the console (adding "\n" at the end of each line)
            $output->writeln([
                'Publiccode Chart',
                '============',
                $chart,
            ]);
        }
    }
}
