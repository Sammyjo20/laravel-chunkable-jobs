<?php declare(strict_types=1);

use Sammyjo20\ChunkableJobs\Chunk;
use Illuminate\Support\Facades\Bus;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\ConstructorArgsJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\PaginatedJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\UnknownSizeJob;
use Sammyjo20\ChunkableJobs\Exceptions\BulkChunkDispatcherException;

test('it will dispatch all jobs at once', function () {
    Bus::fake();

    PaginatedJob::dispatchAllChunks();

    Bus::assertDispatchedTimes(PaginatedJob::class, 3);
});

test('the dispatched jobs wont run the next chain', function () {
    PaginatedJob::dispatchAllChunks();

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

    // Every chunk should be considered last as the next is disabled.

    expect($chunkOne->isLast())->toBeTrue();
    expect($chunkTwo->isLast())->toBeTrue();
    expect($chunkThree->isLast())->toBeTrue();
});

test('it throws an exception if you try to use a job with UnknownSizeChunk', function () {
    $this->expectException(BulkChunkDispatcherException::class);
    $this->expectExceptionMessage('You cannot iterate through an UnknownSizeChunk.');

    UnknownSizeJob::dispatchAllChunks();
});

test('arguments are passed into the constructor from dispatch all chunks method', function () {
    ConstructorArgsJob::dispatchAllChunks('Sammy');

    $chunkOne = cache()->get('1');
    $chunkTwo = cache()->get('2');
    $chunkThree = cache()->get('3');

    expect($chunkOne)->toEqual('Sammy');
    expect($chunkTwo)->toEqual('Sammy');
    expect($chunkThree)->toEqual('Sammy');
});
