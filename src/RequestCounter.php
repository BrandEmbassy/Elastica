<?php declare(strict_types = 1);

namespace Elastica;

use Ramsey\Uuid\Uuid;

class RequestCounter implements RequestCounterInterface
{
    private int $count = 0;

    private string $id;


    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
    }


    public function incrementCount(): void
    {
        ++$this->count;
    }


    public function getCount(): int
    {
        return $this->count;
    }


    public function getId(): string
    {
        return $this->id;
    }
}
