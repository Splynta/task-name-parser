<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Person implements Arrayable
{
    public function __construct(
        public string|null $title = null,
        public string|null $firstName = null,
        public string|null $lastName = null,
        public string|null $initial = null,
        public Collection $nameContents = new Collection(),
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'initial' => $this->initial,
        ];
    }
}
