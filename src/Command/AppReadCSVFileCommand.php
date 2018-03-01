<?php

namespace App\Command;

use App\Service\ArrayToEntitySaver\ArrayToEntitySaver;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityValidator\EntityValidator;
use App\Service\FileReader\FileReader;
use App\Service\FileReaderToBD\ControllersReading\StreamFileReaderToBD;
use App\Service\FileReaderToBD\FileReaderToBD;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ValidatorBuilder;

class AppReadCSVFileCommand extends Command
{
    protected static $defaultName = 'app:readCSVFile';
    protected $fileReader;
    protected $arrayToEntitySaver;
    protected $entityManager;
    protected $entityConverter;
    protected $entityValidator;
    protected $validatorBuilder;
    protected $fileReaderToBD;

    public function __construct(
        ?string $name = null,
        FileReader $fileReader,
        ArrayToEntitySaver $arrayToEntitySaver,
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        EntityValidator $entityValidator,
        FileReaderToBD $fileReaderToBD
    ) {
        parent::__construct($name);
        $this->entityConverter = $entityConverter;
        $this->entityValidator = $entityValidator;
        $this->entityManager = $entityManager;
        $this->arrayToEntitySaver = $arrayToEntitySaver;
        $this->fileReader = $fileReader;
        $this->fileReaderToBD = $fileReaderToBD;
        $this->validatorBuilder = new ValidatorBuilder();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to File')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reportProcessing = [
            'failedRecords' => [],
            'amountFailedItems' => 0,
            'amountProcessedItems' => 0,
            'amountSuccessesItems' => 0,
        ];

        $io = new SymfonyStyle($input, $output);
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        foreach ($this->entityManager->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->entityManager->getEventManager()->removeEventListener($event, $listener);
            }
        }

        $filePath = $input->getArgument('filePath');

        if ($filePath) {
            $io->note(sprintf('You passed an argument: %s', $filePath));

            $file = new \SplFileObject($filePath, 'r');
            $start = microtime(true);

            $streamFileReader = new StreamFileReaderToBD(
                $this->fileReader,
                $this->arrayToEntitySaver,
                $this->entityManager,
                $this->entityConverter,
                $this->entityValidator,
                $this->validatorBuilder,
                $input->getOption('test')
            );

            $readingReport = $this->fileReaderToBD->readFileToBD($file, $streamFileReader);
            $reportProcessing = array_merge($reportProcessing, $readingReport);

            $time = microtime(true) - $start;

            $io->note(sprintf('Processed Items: %s', $reportProcessing['amountProcessedItems']));
            $io->note(sprintf('Failure Items: %s', $reportProcessing['amountFailedItems']));
            $io->note(sprintf('Success Items: %s', $reportProcessing['amountSuccessesItems']));

            $io->note(sprintf('Time processed: %s', $time));
            $io->note(sprintf('Get memory: %s', (int)(memory_get_peak_usage() / 1024).' KB'));


        }
        $io->success('File was reading successful');
    }
}
