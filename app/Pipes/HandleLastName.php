<?php

declare(strict_types=1);

namespace App\Pipes;

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use Closure;
use Illuminate\Support\Collection;

class HandleLastName
{
    public function handle(NameParserPipelineContainer $value, Closure $next) {
        $persons = collect($value->persons)->transform(function (Person $person) {
            $person->lastName = $person->nameContents->shift();

            return $person;
        });

        if ($this->hasMultipleUsers($persons)) {
            $lastName = $persons->pluck(fn(Person $person) => $person->lastName ?? null)
                ->filter()
                ->first();

            $persons->transform(function (Person $person) use ($lastName) {
                if ($person->lastName === null) {
                    $person->lastName = $lastName;
                }

                return $person;
            });
        }

        return $next(new NameParserPipelineContainer(
            row: $value->row,
            persons: $persons->all(),
        ));
    }

    private function hasMultipleUsers(Collection $persons): bool {
        return $persons->count() > 1;
    }
}
