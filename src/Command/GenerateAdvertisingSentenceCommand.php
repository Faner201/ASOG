<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\AdvertisingSentenceGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateAdvertisingSentenceCommand extends Command
{
    private AdvertisingSentenceGeneratorService $sentenceGeneratorService;

    public function __construct(AdvertisingSentenceGeneratorService $sentenceGeneratorService)
    {
        parent::__construct();

        $this->sentenceGeneratorService = $sentenceGeneratorService;
    }

    protected static $defaultName        = 'app:generate-advertising-sentence';
    protected static $defaultDescription = 'Считывает текстовый файл с фразами рекламной компании и создает файл с рекламными предложениями';

    protected function configure(): void
    {
        $this
            ->addArgument('input-file', InputArgument::REQUIRED, 'Путь к текстовому файлу с исходными наборами фраз')
            ->addArgument('output-file', InputArgument::OPTIONAL, 'Путь к файлу для сохранения результата', 'storage/advertising-sentence');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io         = new SymfonyStyle($input, $output);
        $inputPath  = (string) $input->getArgument('input-file');
        $outputPath = (string) $input->getArgument('output-file');
        if (!is_file($inputPath) || !is_readable($inputPath)) {
            $io->error(sprintf('Файл %s не доступен для чтения.', $inputPath));

            return Command::INVALID;
        }

        try {
            $this->sentenceGeneratorService->generate($inputPath, $outputPath);
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Сгенерирован набор рекламных предложений и сохранен в директорию storage');

        return Command::SUCCESS;
    }
}
