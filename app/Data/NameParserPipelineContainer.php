<?php

declare(strict_types=1);

namespace App\Data;

class NameParserPipelineContainer
{
    public function __construct(
        public string $row,
        public array $persons = [],
    ) {}
}
