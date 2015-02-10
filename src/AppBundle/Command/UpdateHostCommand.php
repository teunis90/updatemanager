<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use AppBundle\Entity\Host;
use AppBundle\Classes\PacketManagerService;


class UpdateHostCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('update:host')
            ->setDescription('Update host(s)')
            ->addArgument(
                'host',
                InputArgument::OPTIONAL,
                'Which host do you want the update? No parameter is all hosts.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	    $doctrine = $this->getContainer()->get('doctrine');
	    $repository = $doctrine->getRepository('AppBundle:Host');
        $inputvariable_host = $input->getArgument('host');
        if ($inputvariable_host) {
	        $host = $repository->findOneByHostname($inputvariable_host);
	        
	        if($host) {
				$hostList[] = $host;
				$output->writeln("<info>Update host: ".$host->getHostname()."</info>\n");
	        } else {
		    	$output->writeln("<error>Can't find host in db: " . $inputvariable_host . "</error>");
		    	exit();
	        }
        } else {
			$hostList = $repository->findAll();
		    $output->writeln("<info>Updating all hosts</info>\n");
        }
        unset($host);
        
        $pms = new PacketManagerService($hostList, $doctrine, $output);
        $pms->executeCron();
        
	    $output->writeln("");
	    $output->writeln("Peak memory usage: " . memory_get_peak_usage());
    }
}