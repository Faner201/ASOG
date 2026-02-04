<?php

declare(strict_types=1);

namespace App\Service\Parser;

use App\Entity\KeyWord;
use App\Exception\EmptyFileException;

class TextParser extends StrategyParserAbstract
{
    public function parse(mixed $input): array
    {
        $content = $this->getContent($input);
        $lines   = preg_split('/\R/', $content);
        $sets    = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $this->isBoundaryLine($line)) {
                continue;
            }

            $rawItems = array_filter(array_map('trim', explode(',', $line)));
            $sets[]   = array_map(fn (string $word): KeyWord => new KeyWord($word), array_values($rawItems));
        }

        if ($sets === []) {
            throw new EmptyFileException();
        }

        return $sets;
    }

    public function supports(string $format): bool
    {
        return $format === 'txt';
    }

    private function isBoundaryLine(string $line): bool
    {
        if (str_contains($line, '<<<') || str_contains($line, '>>>')) {
            return !str_contains($line, ',');
        }

        return false;
    }
}
