<?php

declare(strict_types=1);

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use App\Pipes\HandleTitle;
use Illuminate\Support\Collection;

it('moves the first nameContents item into title for a single person', function (): void {
    $pipe = new HandleTitle();

    $person = new Person(
        title: null,
        firstName: 'Jane',
        lastName: 'Doe',
        initial: null,
        nameContents: collect(['Dr', 'Jane', 'Doe']),
    );

    $container = new NameParserPipelineContainer(
        row: 'raw-row',
        persons: [$person],
    );

    $result = $pipe->handle($container, function (NameParserPipelineContainer $nextContainer) {
        return $nextContainer;
    });

    expect($result)->toBeInstanceOf(NameParserPipelineContainer::class);
    expect($result->row)->toBe('raw-row');

    expect($result->persons)->toHaveCount(1);
    expect($result->persons[0])->toBeInstanceOf(Person::class);

    expect($result->persons[0]->title)->toBe('Dr');
    expect($result->persons[0]->nameContents)->toBeInstanceOf(Collection::class);
    expect($result->persons[0]->nameContents->all())->toBe(['Jane', 'Doe']);
});

it('handles multiple persons independently', function (): void {
    $pipe = new HandleTitle();

    $personA = new Person(nameContents: collect(['Mr', 'John', 'Smith']));
    $personB = new Person(nameContents: collect(['Ms', 'Ada', 'Lovelace']));

    $container = new NameParserPipelineContainer(
        row: 'raw-row',
        persons: [$personA, $personB],
    );

    $result = $pipe->handle($container, fn (NameParserPipelineContainer $c) => $c);

    expect($result->persons[0]->title)->toBe('Mr');
    expect($result->persons[0]->nameContents->all())->toBe(['John', 'Smith']);

    expect($result->persons[1]->title)->toBe('Ms');
    expect($result->persons[1]->nameContents->all())->toBe(['Ada', 'Lovelace']);
});

it('sets title to null when nameContents is empty', function (): void {
    $pipe = new HandleTitle();

    $person = new Person(nameContents: collect([]));

    $container = new NameParserPipelineContainer(
        row: 'raw-row',
        persons: [$person],
    );

    $result = $pipe->handle($container, fn (NameParserPipelineContainer $c) => $c);

    expect($result->persons[0]->title)->toBeNull();
    expect($result->persons[0]->nameContents->all())->toBe([]);
});

it('passes a new container instance to the next closure while preserving row', function (): void {
    $pipe = new HandleTitle();

    $container = new NameParserPipelineContainer(
        row: 'raw-row',
        persons: [new Person(nameContents: collect(['Dr']))],
    );

    $seenByNext = null;

    $result = $pipe->handle($container, function (NameParserPipelineContainer $nextContainer) use (&$seenByNext) {
        $seenByNext = $nextContainer;

        return $nextContainer;
    });

    expect($seenByNext)->toBeInstanceOf(NameParserPipelineContainer::class);
    expect($seenByNext)->not->toBe($container); // new instance
    expect($seenByNext->row)->toBe('raw-row');
    expect($result)->toBe($seenByNext);
    expect($seenByNext->persons[0]->title)->toBe('Dr');
});
