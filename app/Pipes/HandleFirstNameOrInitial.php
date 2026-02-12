<?php

declare(strict_types=1);

namespace App\Pipes;

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use Closure;
use Illuminate\Support\Str;

class HandleFirstNameOrInitial
{
    public function handle(NameParserPipelineContainer $value, Closure $next) {
        $persons = collect($value->persons)->transform(function (Person $person) {
            if ($person->nameContents->count() > 1) {
                $word = Str::of($person->nameContents->shift())
                    ->trim('.');

                if ($word->length() === 1) {
                    $person->initial = $word->toString();
                } else {
                    $person->firstName = $word->toString();
                }
            }

            return $person;
        });

        return $next(new NameParserPipelineContainer(
            row: $value->row,
            persons: $persons->all(),
        ));
    }
}
