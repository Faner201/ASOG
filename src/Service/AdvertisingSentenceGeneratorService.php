<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\DontOpenForRecordingFile;
use App\Exception\DontReadingForFile;
use App\Exception\EmptyArrayParsersException;
use App\Exception\NotCreateDirectory;
use App\Exception\NotFountParserException;
use App\Exception\UnknowParserException;
use App\Service\CorrectorAdvertising\CorrectorAdvertisingSentenceFacade;
use App\Service\Generator\KeyWordCartesianProductGenerator;
use App\Service\Parser\StrategyParser;
use App\Service\Resolver\KeyWordConflictResolver;

class AdvertisingSentenceGeneratorService
{
    /**
     * @var StrategyParser[]
     */
    private array $parsers = [];
    private KeyWordConflictResolver $conflictResolver;
    private CorrectorAdvertisingSentenceFacade $correctorFacade;

    public function __construct(
        iterable $parsers,
        KeyWordConflictResolver $conflictResolver,
        CorrectorAdvertisingSentenceFacade $correctorFacade,
    ) {
        foreach ($parsers as $parser) {
            if (!$parser instanceof StrategyParser) {
                throw new UnknowParserException();
            }

            $this->parsers[] = $parser;
        }

        if ($this->parsers === []) {
            throw new EmptyArrayParsersException();
        }

        $this->conflictResolver = $conflictResolver;
        $this->correctorFacade  = $correctorFacade;
    }

    public function generate(string $inputPath, string $outputPath): void
    {
        if (!is_file($inputPath) || !is_readable($inputPath)) {
            throw new DontReadingForFile($inputPath);
        }

        $extension = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'txt';
        }

        $sets   = $this->getParserForExtension($extension)->parse($inputPath);
        $carSet = $sets[0];

        $carNegativeWords = $this->conflictResolver->resolve($carSet);
        $generator        = new KeyWordCartesianProductGenerator($sets);
        $corrector        = $this->correctorFacade->create($carNegativeWords);
        try {
            $this->ensureDirectory($outputPath);
        } catch (NotCreateDirectory $e) {
            throw new DontOpenForRecordingFile($e->getMessage());
        }

        $handle = fopen($outputPath.'.'.$extension, 'wb');
        if ($handle === false) {
            throw new DontOpenForRecordingFile($outputPath);
        }

        try {
            foreach ($generator->generate() as $combination) {
                $row = $corrector->corrector($combination);
                fwrite($handle, $row.PHP_EOL);
            }
        } finally {
            fclose($handle);
        }
    }

    private function getParserForExtension(string $extension): StrategyParser
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($extension)) {
                return $parser;
            }
        }

        throw new NotFountParserException($extension);
    }

    private function ensureDirectory(string $filePath): void
    {
        $directory = dirname($filePath);
        if ($directory === '' || $directory === '.' || is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new NotCreateDirectory($directory);
        }
    }
}
