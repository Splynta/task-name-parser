<?php

declare(strict_types=1);

namespace App\Pipes;

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use Closure;

class HandleTitle
{
    public function handle(NameParserPipelineContainer $value, Closure $next) {
        $persons = collect($value->persons)->transform(function (Person $person) {
            $person->title = $person->nameContents->shift();

            return $person;
        })->all();

        return $next(new NameParserPipelineContainer(
            row: $value->row,
            persons: $persons,
        ));
    }
}
