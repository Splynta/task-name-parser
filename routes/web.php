<?php

use App\Data\NameParserPipelineContainer;
use App\Pipes\NormalizeString;
use App\Pipes\HandleFirstNameOrInitial;
use App\Pipes\HandleLastName;
use App\Pipes\HandleTitle;
use App\Pipes\SplitMultipleUsers;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $path = Storage::disk('local')->path('examples.csv');

    $rows = collect();

    if (($handle = fopen($path, 'r')) !== false) {
        fgetcsv($handle); // Remove first row which is column names

        while (($row = fgetcsv($handle)) !== false) {
            $rows->push(Pipeline::send(new NameParserPipelineContainer($row[0]))
                ->through([
                    NormalizeString::class,
                    SplitMultipleUsers::class,
                    HandleTitle::class,
                    HandleFirstNameOrInitial::class,
                    HandleLastName::class,
                ])
                ->thenReturn());
        }
    }

    return $rows->flatMap(fn(NameParserPipelineContainer $row) => $row->persons)->toArray();
});
