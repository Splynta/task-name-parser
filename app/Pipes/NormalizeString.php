<?php

declare(strict_types=1);

namespace App\Pipes;

use App\Data\NameParserPipelineContainer;
use Closure;
use Illuminate\Support\Str;

class NormalizeString
{
    public function handle(NameParserPipelineContainer $value, Closure $next)
    {
        return $next(new NameParserPipelineContainer(
            row: Str::replace('and', '&', $value->row, false),
            persons: $value->persons,
        ));
    }
}
