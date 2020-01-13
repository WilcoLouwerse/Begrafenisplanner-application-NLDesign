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

class ApiHelmCommand extends Command
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
        ->setName('app:helm:update')
        // the short description shown while running "php bin/console list"
        ->setDescription('Creates a new helm chart.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to create a new hel chart from the helm template')
        ->setAliases(['app:helm:export'])
        ->setDescription('Dump the OpenAPI documentation')
        ->addOption('location', null, InputOption::VALUE_OPTIONAL, 'Write output to files in the given location', '/srv/api/helm')
        ->addOption('spec-version', null, InputOption::VALUE_OPTIONAL, 'Helm version to use ("0.1.0")', '0.1.0');
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

        $values = $this->twig->render('helm/Values.yaml.twig');
        $chart = $this->twig->render('helm/Chart.yaml.twig');

        if (!empty($location = $input->getOption('location')) && \is_string($location)) {
            file_put_contents($location.'/values.yaml', $values);
            file_put_contents($location.'/Chart.yaml', $chart);
            $io->success(sprintf('Data written to %s (specification version %s).', $location, $version));
        } else {
            // outputs multiple lines to the console (adding "\n" at the end of each line)
            $output->writeln([
                'Helm Chart Creator',
                '============',
                $chart,
            ]);
        }
    }
}
