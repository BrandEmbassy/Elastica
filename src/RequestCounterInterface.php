<?php declare(strict_types = 1);

namespace Elastica;

interface RequestCounterInterface
{
    public function incrementCount(): void;

    public function getCount(): int;

    public function getId(): string;
}
