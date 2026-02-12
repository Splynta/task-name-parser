<?php

declare(strict_types=1);

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use App\Pipes\HandleLastName;
use Illuminate\Support\Collection;

it('sets lastName from the first element of nameContents (shift) for a single person', function () {
    $person = new Person(
        nameContents: collect(['Doe', 'Jane'])
    );

    $container = new NameParserPipelineContainer(
        row: 'Doe Jane',
        persons: [$person],
    );

    $pipe = new HandleLastName();

    $received = null;

    $result = $pipe->handle($container, function (NameParserPipelineContainer $nextValue) use (&$received) {
        $received = $nextValue;

        return $nextValue;
    });

    expect($result)->toBeInstanceOf(NameParserPipelineContainer::class);

    expect($received)->not->toBeNull();
    expect($received->row)->toBe('Doe Jane');
    expect($received->persons)->toHaveCount(1);

    /** @var Person $outPerson */
    $outPerson = $received->persons[0];

    expect($outPerson->lastName)->toBe('Doe');
    expect($outPerson->nameContents)->toBeInstanceOf(Collection::class);
    expect($outPerson->nameContents->all())->toBe(['Jane']);
});

it('leaves lastName null when nameContents is empty (single person)', function () {
    $person = new Person(
        nameContents: collect([])
    );

    $container = new NameParserPipelineContainer(
        row: 'Empty',
        persons: [$person],
    );

    $pipe = new HandleLastName();

    $result = $pipe->handle($container, fn (NameParserPipelineContainer $nextValue) => $nextValue);

    expect($result->persons)->toHaveCount(1);
    expect($result->persons[0])->toBeInstanceOf(Person::class);
    expect($result->persons[0]->lastName)->toBeNull();
});

it('propagates a non-null lastName to other persons with null lastName when there are multiple persons', function () {
    $personWithLastName = new Person(
        nameContents: collect(['Smith', 'John'])
    );

    $personWithoutLastName = new Person(
        nameContents: collect([]) // shift() will yield null
    );

    $container = new NameParserPipelineContainer(
        row: 'Smith John & Jane',
        persons: [$personWithLastName, $personWithoutLastName],
    );

    $pipe = new HandleLastName();

    $result = $pipe->handle($container, fn (NameParserPipelineContainer $nextValue) => $nextValue);

    expect($result->persons)->toHaveCount(2);

    /** @var Person $p1 */
    $p1 = $result->persons[0];
    /** @var Person $p2 */
    $p2 = $result->persons[1];

    expect($p1->lastName)->toBe('Smith');
    expect($p2->lastName)->toBe('Smith');
});

it('does not overwrite an existing lastName for other persons when there are multiple persons', function () {
    $p1 = new Person(
        nameContents: collect(['Alpha', 'A'])
    );

    $p2 = new Person(
        nameContents: collect(['Beta', 'B'])
    );

    $container = new NameParserPipelineContainer(
        row: 'Alpha A, Beta B',
        persons: [$p1, $p2],
    );

    $pipe = new HandleLastName();

    $result = $pipe->handle($container, fn (NameParserPipelineContainer $nextValue) => $nextValue);

    expect($result->persons)->toHaveCount(2);
    expect($result->persons[0]->lastName)->toBe('Alpha');
    expect($result->persons[1]->lastName)->toBe('Beta');
});
