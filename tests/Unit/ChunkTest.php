<?php

declare(strict_types=1);

use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkRange;

test('when creating a chunk that has 30 items in with a chunk size of 10 it will create 3 chunks', function () {
    $chunkRange = ChunkRange::create(30, 10);

    $chunks = iterator_to_array($chunkRange);

    expect($chunks)->toHaveCount(3);

    [$chunkOne, $chunkTwo, $chunkThree] = $chunks;

    // Chunk One

    expect($chunkOne->totalItems)->toEqual(30);
    expect($chunkOne->totalChunks)->toEqual(3);
    expect($chunkOne->remainingItems)->toEqual(30);
    expect($chunkOne->remainingChunks)->toEqual(2);
    expect($chunkOne->originalSize)->toEqual(10);
    expect($chunkOne->size)->toEqual(10);
    expect($chunkOne->limit)->toEqual(10);
    expect($chunkOne->offset)->toEqual(0);
    expect($chunkOne->position)->toEqual(1);
    expect($chunkOne->isFirst())->toBeTrue();
    expect($chunkOne->isNotFirst())->toBeFalse();
    expect($chunkOne->isLast())->toBeFalse();
    expect($chunkOne->isNotLast())->toBeTrue();

    // Chunk Two

    expect($chunkTwo->totalItems)->toEqual(30);
    expect($chunkTwo->totalChunks)->toEqual(3);
    expect($chunkTwo->remainingItems)->toEqual(20);
    expect($chunkTwo->remainingChunks)->toEqual(1);
    expect($chunkTwo->originalSize)->toEqual(10);
    expect($chunkTwo->size)->toEqual(10);
    expect($chunkTwo->limit)->toEqual(10);
    expect($chunkTwo->offset)->toEqual(10);
    expect($chunkTwo->position)->toEqual(2);
    expect($chunkTwo->isFirst())->toBeFalse();
    expect($chunkTwo->isLast())->toBeFalse();

    // Chunk Three

    expect($chunkThree->totalItems)->toEqual(30);
    expect($chunkThree->totalChunks)->toEqual(3);
    expect($chunkThree->remainingItems)->toEqual(10);
    expect($chunkThree->remainingChunks)->toEqual(0);
    expect($chunkThree->originalSize)->toEqual(10);
    expect($chunkThree->size)->toEqual(10);
    expect($chunkThree->limit)->toEqual(10);
    expect($chunkThree->offset)->toEqual(20);
    expect($chunkThree->position)->toEqual(3);
    expect($chunkThree->isFirst())->toBeFalse();
    expect($chunkThree->isLast())->toBeTrue();
});

test('when creating a chunk that has 33 items in with a chunk size of 10 it will create 4 chunks to fill in the remainder', function () {
    $chunkRange = chunkRange(33, 10);

    $chunks = iterator_to_array($chunkRange);

    expect($chunks)->toHaveCount(4);

    [$chunkOne, $chunkTwo, $chunkThree, $chunkFour] = $chunks;

    // Chunk One

    expect($chunkOne->totalItems)->toEqual(33);
    expect($chunkOne->totalChunks)->toEqual(4);
    expect($chunkOne->remainingItems)->toEqual(33);
    expect($chunkOne->remainingChunks)->toEqual(3);
    expect($chunkOne->originalSize)->toEqual(10);
    expect($chunkOne->size)->toEqual(10);
    expect($chunkOne->limit)->toEqual(10);
    expect($chunkOne->offset)->toEqual(0);
    expect($chunkOne->position)->toEqual(1);
    expect($chunkOne->isFirst())->toBeTrue();
    expect($chunkOne->isLast())->toBeFalse();

    // Chunk Two

    expect($chunkTwo->totalItems)->toEqual(33);
    expect($chunkTwo->totalChunks)->toEqual(4);
    expect($chunkTwo->remainingItems)->toEqual(23);
    expect($chunkTwo->remainingChunks)->toEqual(2);
    expect($chunkTwo->originalSize)->toEqual(10);
    expect($chunkTwo->size)->toEqual(10);
    expect($chunkTwo->limit)->toEqual(10);
    expect($chunkTwo->offset)->toEqual(10);
    expect($chunkTwo->position)->toEqual(2);
    expect($chunkTwo->isFirst())->toBeFalse();
    expect($chunkTwo->isLast())->toBeFalse();

    // Chunk Three

    expect($chunkThree->totalItems)->toEqual(33);
    expect($chunkThree->totalChunks)->toEqual(4);
    expect($chunkThree->remainingItems)->toEqual(13);
    expect($chunkThree->remainingChunks)->toEqual(1);
    expect($chunkThree->originalSize)->toEqual(10);
    expect($chunkThree->size)->toEqual(10);
    expect($chunkThree->limit)->toEqual(10);
    expect($chunkThree->offset)->toEqual(20);
    expect($chunkThree->position)->toEqual(3);
    expect($chunkThree->isFirst())->toBeFalse();
    expect($chunkThree->isLast())->toBeFalse();

    // Chunk Four

    expect($chunkFour->totalItems)->toEqual(33);
    expect($chunkFour->totalChunks)->toEqual(4);
    expect($chunkFour->remainingItems)->toEqual(3);
    expect($chunkFour->remainingChunks)->toEqual(0);
    expect($chunkFour->originalSize)->toEqual(10);
    expect($chunkFour->size)->toEqual(3);
    expect($chunkFour->limit)->toEqual(3);
    expect($chunkFour->offset)->toEqual(30);
    expect($chunkFour->position)->toEqual(4);
    expect($chunkFour->isFirst())->toBeFalse();
    expect($chunkFour->isLast())->toBeTrue();
});

test('when creating a chunk that has less items than the chunk size it will create one chunk', function () {
    $chunk = new Chunk(10, 100);

    expect($chunk->totalItems)->toEqual(10);
    expect($chunk->totalChunks)->toEqual(1);
    expect($chunk->remainingItems)->toEqual(10);
    expect($chunk->remainingChunks)->toEqual(0);
    expect($chunk->originalSize)->toEqual(100);
    expect($chunk->size)->toEqual(10);
    expect($chunk->limit)->toEqual(10);
    expect($chunk->offset)->toEqual(0);
    expect($chunk->position)->toEqual(1);
    expect($chunk->isFirst())->toBeTrue();
    expect($chunk->isLast())->toBeTrue();
});

test('you can get the next chunk', function () {
    $chunkOne = new Chunk(30, 10);

    // Chunk One

    expect($chunkOne->totalItems)->toEqual(30);
    expect($chunkOne->totalChunks)->toEqual(3);
    expect($chunkOne->remainingItems)->toEqual(30);
    expect($chunkOne->remainingChunks)->toEqual(2);
    expect($chunkOne->originalSize)->toEqual(10);
    expect($chunkOne->size)->toEqual(10);
    expect($chunkOne->limit)->toEqual(10);
    expect($chunkOne->offset)->toEqual(0);
    expect($chunkOne->position)->toEqual(1);
    expect($chunkOne->isFirst())->toBeTrue();
    expect($chunkOne->isLast())->toBeFalse();

    // Chunk Two

    $chunkTwo = $chunkOne->next();

    expect($chunkTwo->totalItems)->toEqual(30);
    expect($chunkTwo->totalChunks)->toEqual(3);
    expect($chunkTwo->remainingItems)->toEqual(20);
    expect($chunkTwo->remainingChunks)->toEqual(1);
    expect($chunkTwo->originalSize)->toEqual(10);
    expect($chunkTwo->size)->toEqual(10);
    expect($chunkTwo->limit)->toEqual(10);
    expect($chunkTwo->offset)->toEqual(10);
    expect($chunkTwo->position)->toEqual(2);
    expect($chunkTwo->isFirst())->toBeFalse();
    expect($chunkTwo->isLast())->toBeFalse();

    // Chunk Three

    $chunkThree = $chunkTwo->next();

    expect($chunkThree->totalItems)->toEqual(30);
    expect($chunkThree->totalChunks)->toEqual(3);
    expect($chunkThree->remainingItems)->toEqual(10);
    expect($chunkThree->remainingChunks)->toEqual(0);
    expect($chunkThree->originalSize)->toEqual(10);
    expect($chunkThree->size)->toEqual(10);
    expect($chunkThree->limit)->toEqual(10);
    expect($chunkThree->offset)->toEqual(20);
    expect($chunkThree->position)->toEqual(3);
    expect($chunkThree->isFirst())->toBeFalse();
    expect($chunkThree->isLast())->toBeTrue();

    // We'll make sure each object has not been mutated.

    expect($chunkOne)->not->toBe($chunkThree);

    // We'll test that when running next() on the last chunk it will return the same object

    $chunkFour = $chunkThree->next();

    expect($chunkFour)->toBe($chunkThree);
});

test('it throws an exception if you provide total items that is less than 0', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The totalItems argument must not be less than 0.');

    $chunk = new Chunk(-1, 10);
});

test('it throws an exception if you provide zero or less as the chunk size', function ($chunkSize) {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The chunkSize argument must be greater than or equal to 1.');

    $chunk = new Chunk(10, $chunkSize);
})->with([0, -1]);

test('you can check if a chunk is empty', function () {
    $chunk = new Chunk(30, 10);

    expect($chunk->isEmpty())->toBeFalse();
    expect($chunk->isNotEmpty())->toBeTrue();

    $emptyChunk = new Chunk(0, 10);

    expect($emptyChunk->isEmpty())->toBeTrue();
    expect($emptyChunk->isNotEmpty())->toBeFalse();
});

test('you can create a chunk with a different starting position', function () {
    $chunk = new Chunk(30, 10, 3);

    expect($chunk->totalItems)->toEqual(30);
    expect($chunk->totalChunks)->toEqual(3);
    expect($chunk->remainingItems)->toEqual(10);
    expect($chunk->remainingChunks)->toEqual(0);
    expect($chunk->originalSize)->toEqual(10);
    expect($chunk->size)->toEqual(10);
    expect($chunk->limit)->toEqual(10);
    expect($chunk->offset)->toEqual(20);
    expect($chunk->position)->toEqual(3);
    expect($chunk->isFirst())->toBeFalse();
    expect($chunk->isLast())->toBeTrue();
});

test('you can move the chunk to a different position but it wont mutate the original value', function () {
    $chunk = new Chunk(30, 10);

    $movedChunk = $chunk->move(2);

    expect($movedChunk->totalItems)->toEqual(30);
    expect($movedChunk->totalChunks)->toEqual(3);
    expect($movedChunk->remainingItems)->toEqual(20);
    expect($movedChunk->remainingChunks)->toEqual(1);
    expect($movedChunk->originalSize)->toEqual(10);
    expect($movedChunk->size)->toEqual(10);
    expect($movedChunk->limit)->toEqual(10);
    expect($movedChunk->offset)->toEqual(10);
    expect($movedChunk->position)->toEqual(2);
    expect($movedChunk->isFirst())->toBeFalse();
    expect($movedChunk->isLast())->toBeFalse();

    expect($movedChunk)->not->toBe($chunk);
});

test('it throws an exception if you try to move to an invalid position', function ($position) {
    $chunk = new Chunk(30, 10);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The position must be between 1 and 3.');

    $chunk->move($position);
})->with([-1, 0, 4]);

test('when you move to the same position it will return the same object', function () {
    $chunk = new Chunk(30, 10);

    $movedChunk = $chunk->move(1);

    expect($chunk)->toBe($movedChunk);
});

test('metadata can be added to a chunk', function () {
    $chunk = new Chunk(30, 10);

    expect($chunk->metadata)->toBeEmpty();

    $chunk->metadata['name'] = 'Sam';

    expect($chunk->metadata)->toEqual(['name' => 'Sam']);
});

test('metadata is retained when moving to a new position', function () {
    $chunk = new Chunk(30, 10);

    $chunk->metadata['name'] = 'Sam';

    $movedChunk = $chunk->move(3);

    expect($movedChunk->metadata)->toEqual(['name' => 'Sam']);
});

test('metadata can be passed in to the constructor', function () {
    $chunk = new Chunk(30, 10, 1, ['name' => 'Sam']);

    expect($chunk->metadata)->toEqual(['name' => 'Sam']);
});

test('you can disable and enable the next chunk functionality', function () {
    $chunk = new Chunk(30, 10);

    $nextChunk = $chunk->next();

    expect($nextChunk->position)->toEqual(2);
    expect($nextChunk->isLast())->toBeFalse();

    $nextChunk->disableNext();

    expect($nextChunk->isLast())->toBeFalse();
    expect($nextChunk->next())->toBe($nextChunk);
    expect($nextChunk->isNextDisabled())->toBeTrue();

    $nextChunk->enableNext();

    $lastChunk = $chunk->move(3);

    expect($nextChunk->isLast())->toBeFalse();
    expect($nextChunk->next())->toEqual($lastChunk);
    expect($nextChunk->isNextDisabled())->toBeFalse();
});
