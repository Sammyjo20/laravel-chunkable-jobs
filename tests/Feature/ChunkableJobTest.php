<?php

use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\ChunkIntervalJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\EarlyFinishJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\ExtraPropertiesJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\ExtraUnsetPropertiesJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\FailedJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\NextChunkJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\NullChunkJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\PaginatedJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\ReleasedJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\SetUpJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\UnknownSizeJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\ZeroItemsJob;

test('when dispatching a job that has 30 items with a chunk size of 10, three jobs will be dispatched', function () {
    PaginatedJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');
    $chunkFour = cache()->get('4'); // Four shouldn't exist

    expect($chunkOne)->toBeInstanceOf(Chunk::class);
    expect($chunkTwo)->toBeInstanceOf(Chunk::class);
    expect($chunkThree)->toBeInstanceOf(Chunk::class);
    expect($chunkFour)->toBeNull();

    expect($chunkOne->offset)->toEqual(0);
    expect($chunkTwo->offset)->toEqual(10);
    expect($chunkThree->offset)->toEqual(20);
});

test('when dispatching a job you can stop chunking early', function () {
    EarlyFinishJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toBeInstanceOf(Chunk::class);
    expect($chunkTwo)->toBeInstanceOf(Chunk::class);
    expect($chunkThree)->toBeNull();

    expect($chunkOne->offset)->toEqual(0);
    expect($chunkTwo->offset)->toEqual(10);
});

test('when dispatching a job that returns null as the chunk it wont run handleChunk', function () {
    cache()->put('processed', false);

    NullChunkJob::dispatch();

    expect(cache()->get('processed'))->toBeFalse();
});

test('when dispatching a job that returns zero items inside of the chunk it wont run handleChunk', function () {
    cache()->put('processed', false);

    ZeroItemsJob::dispatch();

    expect(cache()->get('processed'))->toBeFalse();
});

test('the setUp callback is executed the first time a chunked job is run', function () {
    SetUpJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toBeInstanceOf(Chunk::class);
    expect($chunkTwo)->toBeInstanceOf(Chunk::class);
    expect($chunkThree)->toBeInstanceOf(Chunk::class);

    $count = cache()->get('setUp');

    expect($count)->toEqual(1);
});

test('if the job is released it wont dispatch the next chunk', function () {
    ReleasedJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toBeInstanceOf(Chunk::class);
    expect($chunkTwo)->toBeInstanceOf(Chunk::class);
    expect($chunkThree)->toBeNull();
});

test('if a job fails it wont dispatch the next chunk', function () {
    FailedJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toBeInstanceOf(Chunk::class);
    expect($chunkTwo)->toBeInstanceOf(Chunk::class);
    expect($chunkThree)->toBeNull();
});

test('if a job uses the UnknownSizeChunk it wont be stopped until the job is stopped manually', function () {
    UnknownSizeJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toBeInstanceOf(Chunk::class);
    expect($chunkTwo)->toBeInstanceOf(Chunk::class);
    expect($chunkThree)->toBeInstanceOf(Chunk::class);

    expect($chunkOne->offset)->toEqual(0);
    expect($chunkTwo->offset)->toEqual(100);
    expect($chunkThree->offset)->toEqual(200);

    $max = PHP_INT_MAX;

    expect($chunkThree->remainingItems)->toEqual($max - 200);
});

test('if the job specifies the nextChunk it will use that instead', function () {
    NextChunkJob::dispatch();

    $chunkOne = cache()->get('30:1');
    $chunkTwo = cache()->get('30:2');
    $chunkThree = cache()->get('100:1');
    $chunkFour = cache()->get('100:2');

    expect($chunkOne)->toBeInstanceOf(Chunk::class);
    expect($chunkTwo)->toBeInstanceOf(Chunk::class);
    expect($chunkThree)->toBeInstanceOf(Chunk::class);
    expect($chunkFour)->toBeInstanceOf(Chunk::class);

    expect($chunkOne->totalItems)->toEqual(30);
    expect($chunkTwo->totalItems)->toEqual(30);
    expect($chunkThree->totalItems)->toEqual(100);
    expect($chunkThree->totalItems)->toEqual(100);
});

test('a chunkInterval can be defined for a delay between jobs', function () {
    ChunkIntervalJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toBeNull();
    expect($chunkTwo)->toEqual(5);
    expect($chunkThree)->toEqual(5);
});

test('extra properties are copied over to every job', function () {
    ExtraPropertiesJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toEqual('Sam');
    expect($chunkTwo)->toEqual('Sam');
    expect($chunkThree)->toEqual('Sam');
});

test('you can specify extra properties to be unset', function () {
    ExtraUnsetPropertiesJob::dispatch();

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toEqual('Sam');
    expect($chunkTwo)->toBeNull();
    expect($chunkThree)->toBeNull();
});
