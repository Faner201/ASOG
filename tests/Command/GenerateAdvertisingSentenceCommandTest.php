<?php

declare(strict_types=1);

namespace Tests\Command;

use App\Command\GenerateAdvertisingSentenceCommand;
use App\Service\AdvertisingSentenceGeneratorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class GenerateAdvertisingSentenceCommandTest extends TestCase
{
    public function testCommandDelegatesToGenerator(): void
    {
        $projectRoot     = dirname(__DIR__, 2);
        $inputPath       = $projectRoot.'/tests/Command/files/test.txt';
        $outputDirectory = $this->createTemporaryDirectory();
        $outputPath      = $outputDirectory.'/generated.txt';

        $sentenceGenerator = $this->createMock(AdvertisingSentenceGeneratorService::class);
        $sentenceGenerator
            ->expects(self::once())
            ->method('generate')
            ->with($inputPath, $outputPath);

        $command = new GenerateAdvertisingSentenceCommand($sentenceGenerator);
        $tester  = new CommandTester($command);

        try {
            $exitCode = $tester->execute([
                'input-file'  => $inputPath,
                'output-file' => $outputPath,
            ]);

            self::assertSame(Command::SUCCESS, $exitCode);
            self::assertStringContainsStringIgnoringCase('сгенерирован', $tester->getDisplay());
        } finally {
            $this->cleanupOutput($outputDirectory);
        }
    }

    private function createTemporaryDirectory(): string
    {
        $directory = sys_get_temp_dir().'/ad-command-output-'.bin2hex(random_bytes(6));

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Не удалось создать временную директорию.');
        }

        return $directory;
    }

    private function cleanupOutput(string $outputDirectory): void
    {
        if (is_dir($outputDirectory)) {
            rmdir($outputDirectory);
        }
    }
}
