<?php

use Sammyjo20\ChunkableJobs\Tests\Fixtures\PaginatedJob;

test('when dispatching a job that has 30 items with a chunk size of 10, three jobs will be dispatched', function () {
    //
});

test('when dispatching a job you can stop chunking early', function () {

});

test('when dispatching a job that returns null as the chunk it wont run handleChunk', function () {

});

test('when dispatching a job that returns zero items inside of the chunk it wont run handleChunk', function () {

});

test('the setUp callback is executed the first time a chunked job is run', function () {

});

test('if the job is released it wont dispatch the next chunk', function () {

});

test('if a job fails it wont dispatch the next chunk', function () {

});

test('if a job uses the UnknownLengthChunk it wont be stopped until the job is stopped manually', function () {

});
