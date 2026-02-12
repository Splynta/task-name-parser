<?php

use Illuminate\Support\Facades\Storage;

it('returns parsed people as JSON from examples.csv', function () {
    Storage::fake('local');

    Storage::disk('local')->put('examples.csv', implode("\n", [
        'name', // header row (skipped by the route)
        'Mr John Smith',
        'Dr J. Bloggs',
        'Mr John Smith and Mrs Jane Smith', // "and" should be normalized to "&" and split into 2 people
        '',
    ]));

    $response = $this->get('/');

    $response->assertOk();

    $response->assertExactJson([
        [
            'title' => 'Mr',
            'firstName' => 'John',
            'lastName' => 'Smith',
            'initial' => null,
        ],
        [
            'title' => 'Dr',
            'firstName' => null,
            'lastName' => 'Bloggs',
            'initial' => 'J',
        ],
        [
            'title' => 'Mr',
            'firstName' => 'John',
            'lastName' => 'Smith',
            'initial' => null,
        ],
        [
            'title' => 'Mrs',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'initial' => null,
        ],
    ]);
});
