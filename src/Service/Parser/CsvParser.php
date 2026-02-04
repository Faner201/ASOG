<?php

declare(strict_types=1);

namespace App\Service\Parser;

use App\Entity\KeyWord;
use App\Exception\EmptyFileException;
use App\Exception\FailProcessFileException;

final class CsvParser extends StrategyParserAbstract
{
    public function parse(mixed $input): array
    {
        $content = $this->getContent($input);
        $lines   = preg_split('/\R/', $content);
        if ($lines === false) {
            throw new FailProcessFileException();
        }

        $sets = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $columns = str_getcsv($line);

            $columns = array_map(static fn (string $value): string => trim($value), $columns);
            $columns = array_filter($columns, static fn (string $value): bool => $value !== '');
            if ($columns === []) {
                continue;
            }

            $sets[] = array_map(
                static fn (string $word): KeyWord => new KeyWord($word),
                array_values($columns)
            );
        }

        if ($sets === []) {
            throw new EmptyFileException();
        }

        return $sets;
    }

    public function supports(string $format): bool
    {
        return $format === 'csv';
    }
}
