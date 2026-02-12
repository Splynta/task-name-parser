<?php

declare(strict_types=1);

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use App\Pipes\HandleFirstNameOrInitial;
use Illuminate\Support\Collection;

it('sets initial when the first remaining token is a single letter (trimming trailing dot)', function () {
    $pipe = new HandleFirstNameOrInitial();

    $person = new Person(
        nameContents: new Collection(['J.', 'Doe'])
    );

    $container = new NameParserPipelineContainer(
        row: 'J. Doe',
        persons: [$person],
    );

    $result = $pipe->handle($container, function (NameParserPipelineContainer $next) {
        return $next;
    });

    expect($result)->toBeInstanceOf(NameParserPipelineContainer::class);
    expect($result->row)->toBe('J. Doe');
    expect($result->persons)->toHaveCount(1);

    /** @var Person $updated */
    $updated = $result->persons[0];

    expect($updated->initial)->toBe('J');
    expect($updated->firstName)->toBeNull();

    // shift() should have removed the first token
    expect($updated->nameContents->values()->all())->toBe(['Doe']);
});

it('sets firstName when the first remaining token has more than one character (trimming trailing dot)', function () {
    $pipe = new HandleFirstNameOrInitial();

    $person = new Person(
        nameContents: new Collection(['John.', 'Doe'])
    );

    $container = new NameParserPipelineContainer(
        row: 'John. Doe',
        persons: [$person],
    );

    $result = $pipe->handle($container, function (NameParserPipelineContainer $next) {
        return $next;
    });

    /** @var Person $updated */
    $updated = $result->persons[0];

    expect($updated->firstName)->toBe('John');
    expect($updated->initial)->toBeNull();
    expect($updated->nameContents->values()->all())->toBe(['Doe']);
});

it('does nothing when nameContents has 0 or 1 token', function () {
    $pipe = new HandleFirstNameOrInitial();

    $p0 = new Person(nameContents: new Collection([]));
    $p1 = new Person(nameContents: new Collection(['Doe']));

    $container = new NameParserPipelineContainer(
        row: 'Doe',
        persons: [$p0, $p1],
    );

    $result = $pipe->handle($container, function (NameParserPipelineContainer $next) {
        return $next;
    });

    /** @var Person $u0 */
    $u0 = $result->persons[0];
    /** @var Person $u1 */
    $u1 = $result->persons[1];

    expect($u0->firstName)->toBeNull()
        ->and($u0->initial)->toBeNull()
        ->and($u0->nameContents->values()->all())->toBe([]);

    expect($u1->firstName)->toBeNull()
        ->and($u1->initial)->toBeNull()
        ->and($u1->nameContents->values()->all())->toBe(['Doe']);
});

it('transforms all persons and preserves row', function () {
    $pipe = new HandleFirstNameOrInitial();

    $p1 = new Person(nameContents: new Collection(['A.', 'Smith']));
    $p2 = new Person(nameContents: new Collection(['Alice', 'Jones']));
    $p3 = new Person(nameContents: new Collection(['Solo'])); // should remain unchanged

    $container = new NameParserPipelineContainer(
        row: 'A. Smith; Alice Jones; Solo',
        persons: [$p1, $p2, $p3],
    );

    $result = $pipe->handle($container, function (NameParserPipelineContainer $next) {
        return $next;
    });

    expect($result->row)->toBe('A. Smith; Alice Jones; Solo');
    expect($result->persons)->toHaveCount(3);

    /** @var Person $u1 */
    $u1 = $result->persons[0];
    /** @var Person $u2 */
    $u2 = $result->persons[1];
    /** @var Person $u3 */
    $u3 = $result->persons[2];

    expect($u1->initial)->toBe('A')
        ->and($u1->firstName)->toBeNull()
        ->and($u1->nameContents->values()->all())->toBe(['Smith']);

    expect($u2->firstName)->toBe('Alice')
        ->and($u2->initial)->toBeNull()
        ->and($u2->nameContents->values()->all())->toBe(['Jones']);

    expect($u3->firstName)->toBeNull()
        ->and($u3->initial)->toBeNull()
        ->and($u3->nameContents->values()->all())->toBe(['Solo']);
});
