<?php

declare(strict_types=1);

namespace Sammyjo20\ChunkableJobs;

use Generator;

class ChunkRange
{
    /**
     * Create an iterator of chunks.
     *
     * @param int $totalItems
     * @param int $chunkSize
     * @param array $metadata
     * @return Generator
     */
    public static function create(int $totalItems, int $chunkSize, array $metadata = []): Generator
    {
        $chunk = new Chunk($totalItems, $chunkSize, 1, $metadata);

        $generator = static function () use ($chunk) {
            for ($i = 0; $i < $chunk->totalChunks; $i++) {
                yield $chunk->move($i + 1);
            }
        };

        return $generator();
    }
}
