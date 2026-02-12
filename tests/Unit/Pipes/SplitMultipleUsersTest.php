<?php

declare(strict_types=1);

use App\Data\NameParserPipelineContainer;
use App\Data\Person;
use App\Pipes\SplitMultipleUsers;
use Illuminate\Support\Collection;

it('splits multiple users on ampersand and preserves the original row', function (): void {
    $pipe = new SplitMultipleUsers();

    $input = new NameParserPipelineContainer(row: 'John Doe & Jane Smith');

    $result = $pipe->handle($input, function (NameParserPipelineContainer $container) {
        expect($container->row)->toBe('John Doe & Jane Smith');
        expect($container->persons)->toHaveCount(2);

        expect($container->persons[0])->toBeInstanceOf(Person::class);
        expect($container->persons[1])->toBeInstanceOf(Person::class);

        expect($container->persons[0]->nameContents)->toBeInstanceOf(Collection::class);
        expect($container->persons[0]->nameContents->all())->toBe(['John', 'Doe']);

        expect($container->persons[1]->nameContents)->toBeInstanceOf(Collection::class);
        expect($container->persons[1]->nameContents->all())->toBe(['Jane', 'Smith']);

        return $container;
    });

    expect($result)->toBeInstanceOf(NameParserPipelineContainer::class);
});

it('trims each user segment before splitting into name parts', function (): void {
    $pipe = new SplitMultipleUsers();

    $input = new NameParserPipelineContainer(row: '  John   Doe  &   Jane   Smith   ');

    $pipe->handle($input, function (NameParserPipelineContainer $container) {
        expect($container->persons)->toHaveCount(2);

        // Note: SplitMultipleUsers trims segments, but splits on a single space,
        // so repeated spaces produce empty strings in the resulting collection.
        expect($container->persons[0]->nameContents->all())->toBe(['John', '', '', 'Doe']);
        expect($container->persons[1]->nameContents->all())->toBe(['Jane', '', '', 'Smith']);

        return $container;
    });
});

it('creates a single person when no ampersand is present', function (): void {
    $pipe = new SplitMultipleUsers();

    $input = new NameParserPipelineContainer(row: 'Madonna');

    $pipe->handle($input, function (NameParserPipelineContainer $container) {
        expect($container->persons)->toHaveCount(1);
        expect($container->persons[0])->toBeInstanceOf(Person::class);
        expect($container->persons[0]->nameContents->all())->toBe(['Madonna']);

        return $container;
    });
});

it('returns whatever the next closure returns', function (): void {
    $pipe = new SplitMultipleUsers();

    $input = new NameParserPipelineContainer(row: 'John Doe & Jane Smith');

    $result = $pipe->handle($input, function (NameParserPipelineContainer $container) {
        return ['count' => count($container->persons)];
    });

    expect($result)->toBe(['count' => 2]);
});
