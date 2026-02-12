<?php

declare(strict_types=1);

use App\Data\NameParserPipelineContainer;
use App\Pipes\NormalizeString;

it('replaces "and" with "&" (case-insensitive) and forwards a new container to the next pipe', function (): void {
    $pipe = new NormalizeString();

    $input = new NameParserPipelineContainer(
        row: 'Alice and Bob',
        persons: ['keep-me'],
    );

    $received = null;

    $result = $pipe->handle($input, function (NameParserPipelineContainer $value) use (&$received) {
        $received = $value;

        return $value;
    });

    expect($result)->toBeInstanceOf(NameParserPipelineContainer::class);
    expect($received)->toBeInstanceOf(NameParserPipelineContainer::class);

    // Row normalized
    expect($received->row)->toBe('Alice & Bob');

    // Persons preserved
    expect($received->persons)->toBe(['keep-me']);

    // A new container instance is created (input is not forwarded as-is)
    expect($received)->not->toBe($input);
});

it('replaces all occurrences of "and" regardless of casing', function (): void {
    $pipe = new NormalizeString();

    $input = new NameParserPipelineContainer(
        row: 'A AND B and C AnD D',
        persons: [],
    );

    $output = $pipe->handle($input, fn (NameParserPipelineContainer $value) => $value);

    expect($output->row)->toBe('A & B & C & D');
});

it('does not change the row when "and" does not occur', function (): void {
    $pipe = new NormalizeString();

    $input = new NameParserPipelineContainer(
        row: 'Alice Bob',
        persons: [],
    );

    $output = $pipe->handle($input, fn (NameParserPipelineContainer $value) => $value);

    expect($output->row)->toBe('Alice Bob');
});

it('returns whatever the next closure returns', function (): void {
    $pipe = new NormalizeString();

    $input = new NameParserPipelineContainer(
        row: 'Alice and Bob',
        persons: [],
    );

    $result = $pipe->handle($input, fn () => 'next-result');

    expect($result)->toBe('next-result');
});
