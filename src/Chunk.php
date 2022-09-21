<?php

declare(strict_types=1);

namespace Sammyjo20\ChunkableJobs;

use InvalidArgumentException;

class Chunk
{
    /**
     * The total items inside the chunk.
     *
     * @var int
     */
    public int $totalItems;

    /**
     * The total number of chunks.
     *
     * @var int
     */
    public int $totalChunks;

    /**
     * The remaining items in the chunk.
     *
     * @var int
     */
    public int $remainingItems;

    /**
     * The number of chunks remaining.
     *
     * @var int
     */
    public int $remainingChunks;

    /**
     * The original size of the chunk.
     *
     * @var int
     */
    public int $originalSize;

    /**
     * The actual size of each chunk.
     *
     * @var int
     */
    public int $size;

    /**
     * The limit of each chunk. Alias of size.
     *
     * @var int
     */
    public int $limit;

    /**
     * The current offset of the chunk.
     *
     * @var int
     */
    public int $offset;

    /**
     * The position of the chunk.
     *
     * @var int
     */
    public int $position;

    /**
     * Metadata on the chunk
     *
     * @var array
     */
    public array $metadata = [];

    /**
     * Disable fetching the next chunk.
     *
     * @var bool
     */
    protected bool $disableNext = false;

    /**
     * Create a new chunk
     *
     * @param int $totalItems
     * @param int $chunkSize
     * @param int $startingPosition
     * @param array $metadata
     */
    public function __construct(int $totalItems, int $chunkSize, int $startingPosition = 1, array $metadata = [])
    {
        if ($totalItems < 0) {
            throw new InvalidArgumentException('The totalItems argument must not be less than 0.');
        }

        if ($chunkSize <= 0) {
            throw new InvalidArgumentException('The chunkSize argument must be greater than or equal to 1.');
        }

        $totalChunks = (int)ceil($totalItems / $chunkSize);

        $this->originalSize = $chunkSize;
        $this->totalItems = $totalItems;
        $this->totalChunks = $totalChunks;
        $this->remainingItems = $totalItems;
        $this->remainingChunks = $totalChunks > 0 ? $totalChunks - 1 : 0;
        $this->limit = min($totalItems, $chunkSize);
        $this->size = $this->limit;
        $this->offset = 0;
        $this->position = 1;
        $this->metadata = $metadata;

        if ($startingPosition !== 1) {
            $this->move($startingPosition, true);
        }
    }

    /**
     * Return the next chunk.
     *
     * @return Chunk
     */
    public function next(): Chunk
    {
        if ($this->isLast() || $this->isNextDisabled()) {
            return $this;
        }

        return $this->move($this->position + 1);
    }

    /**
     * Move the chunk to a specific position
     *
     * @param int $position
     * @param bool $mutable
     * @return $this
     */
    public function move(int $position, bool $mutable = false): Chunk
    {
        if ($position === $this->position) {
            return $this;
        }

        if ($position <= 0 || $position > $this->totalChunks) {
            throw new InvalidArgumentException(sprintf('The position must be between 1 and %s.', $this->totalChunks));
        }

        // We'll calculate the remaining items with some maths

        $remaining = ((($position * $this->originalSize) - $this->totalItems) * -1) + $this->originalSize;

        // Now we'll create a new chunk to process it with.

        $newChunk = clone $this;

        $newChunk->position = $position;
        $newChunk->remainingItems = $remaining;
        $newChunk->remainingChunks = $this->totalChunks - $position;
        $newChunk->offset = ($position - 1) * $this->originalSize;
        $newChunk->limit = min($remaining, $this->originalSize);
        $newChunk->size = $newChunk->limit;

        return $mutable === true ? $this->replace($newChunk) : $newChunk;
    }

    /**
     * Replace the object with another chunk.
     *
     * @param Chunk $chunk
     * @return $this
     */
    public function replace(Chunk $chunk): static
    {
        $this->totalItems = $chunk->totalItems;
        $this->totalChunks = $chunk->totalChunks;
        $this->remainingItems = $chunk->remainingItems;
        $this->remainingChunks = $chunk->remainingChunks;
        $this->originalSize = $chunk->originalSize;
        $this->size = $chunk->size;
        $this->limit = $chunk->limit;
        $this->offset = $chunk->offset;
        $this->position = $chunk->position;
        $this->metadata = $chunk->metadata;

        return $this;
    }

    /**
     * Determine if the chunk is the first chunk.
     *
     * @return bool
     */
    public function isFirst(): bool
    {
        return $this->offset === 0;
    }

    /**
     * Determine if the chunk is not the first.
     *
     * @return bool
     */
    public function isNotFirst(): bool
    {
        return ! $this->isFirst();
    }

    /**
     * Determine if the chunk is the last chunk.
     *
     * @return bool
     */
    public function isLast(): bool
    {
        return $this->remainingChunks === 0;
    }

    /**
     * Determine if the chunk is not the last.
     *
     * @return bool
     */
    public function isNotLast(): bool
    {
        return ! $this->isLast();
    }

    /**
     * Determines if the chunk is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->totalItems === 0;
    }

    /**
     * Determines if the chunk is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Disable the next chunk functionality
     *
     * @return $this
     */
    public function disableNext(): Chunk
    {
        $this->disableNext = true;

        return $this;
    }

    /**
     * Enable the next chunk functionality
     *
     * @return $this
     */
    public function enableNext(): Chunk
    {
        $this->disableNext = false;

        return $this;
    }

    /**
     * Check if the next is disabled.
     *
     * @return bool
     */
    public function isNextDisabled(): bool
    {
        return $this->disableNext === true;
    }
}
