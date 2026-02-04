<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising;

use App\Service\CorrectorAdvertising\SentenceRule\SentenceRuleInterface;
use Psr\Container\ContainerInterface;

final readonly class CorrectorAdvertisingSentenceFactory
{
    /**
     * @param iterable<SentenceRuleInterface> $sentenceRules
     */
    public function __construct(
        private ContainerInterface $container,
        private iterable $sentenceRules,
    ) {
    }

    /**
     * @param array<string, string[]> $carNegativeWords
     */
    public function create(array $carNegativeWords): CorrectorAdvertisingSentenceAbstract
    {
        /** @var CorrectorAdvertisingSentence $corrector */
        $corrector = $this->container->get(CorrectorAdvertisingSentence::class);
        $corrector->setSentenceRules($this->sentenceRules);
        $corrector->setCarNegativeWords($carNegativeWords);

        return $corrector;
    }
}
