<?php

declare(strict_types=1);

namespace App\Pipes;

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use Closure;
use Illuminate\Support\Str;

class SplitMultipleUsers
{
    public function handle(NameParserPipelineContainer $value, Closure $next) {
        $users = Str::of($value->row)
            ->explode('&')
            ->transform(fn (string $item) => new Person(nameContents: Str::of($item)->trim()->explode(' ')))
            ->all();

        return $next(new NameParserPipelineContainer(
            row: $value->row,
            persons: $users,
        ));
    }
}
