<?php

namespace App\Command;

use App\Services\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadDataCommand extends Command
{
    protected static $defaultName = 'app:load-data';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var AppService
     */
    private $appService;

    public function __construct(EntityManagerInterface $entityManager, AppService $appService, string $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->appService = $appService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // @todo: get filename
        $this->appService->loadSongs();

        $io->success('Songs Loaded');

        return 0;
    }
}
