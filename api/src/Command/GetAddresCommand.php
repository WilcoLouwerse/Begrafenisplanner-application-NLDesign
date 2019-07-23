<?php
// Conduction/CommonGroundBundle/Command/GetaddresCommand.php

/*
 * This file is part of the Conduction Common Ground Bundle
 *
 * (c) Conduction <info@conduction.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


use  App\Service\KadasterService;

class GetAddresCommand extends Command
{
	protected static $defaultName = 'commonground:getaddres';
	
	private $userManager;
	
	public function __construct(KadasterService $kadasterservice)
	{
		$this->kadasterservice= $kadasterservice;
		
		parent::__construct();
	}
	
    protected function configure()
    {
        $this
        	->setDescription('Get a addres on house number and postcode')
        	->setHelp('Get a Dutch addres from the kadaster based on house number and postcode')
        	->addArgument('housenumber', InputArgument::REQUIRED , 'A housenumber without any suffixes')
        	->addArgument('zipcode', InputArgument::REQUIRED , 'A postal code in P6 style, example 1000AA (so without any spaces)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $housenumber = $input->getArgument('housenumber');
        $zipcode = $input->getArgument('zipcode');
               
        $io->note(sprintf('Looking for adresses at: %s %s', $housenumber, $zipcode));

        $addresses = $this->kadasterservice->getAdresOnHuisnummerPostcode($housenumber, $zipcode);
        $io->note(var_dump($addresses));

        $io->success('Addres lookup completed! Now make it your own! Pass --help to see your options.');
    }
}
