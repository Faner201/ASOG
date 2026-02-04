<?php

declare(strict_types=1);

namespace App\Service\Parser;

abstract class StrategyParser
{
    abstract public function parse(mixed $input): array;

    abstract public function supports(string $format): bool;

    final protected function getContent(mixed $input): string
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException('Источник данных должен быть строкой.');
        }

        if (is_file($input)) {
            $content = file_get_contents($input);
            if ($content === false) {
                throw new \RuntimeException("Не удалось прочитать контент из {$input}");
            }

            return $content;
        }

        return $input;
    }
}
