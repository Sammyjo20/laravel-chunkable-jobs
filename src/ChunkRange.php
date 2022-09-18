<?php

declare(strict_types=1);

namespace Sammyjo20\ChunkableJobs;

class ChunkRange
{
    /**
     * Create an array of chunks.
     *
     * @param  int  $totalItems
     * @param  int  $chunkSize
     * @return Chunk[]
     */
    public static function create(int $totalItems, int $chunkSize): array
    {
        $chunk = new Chunk($totalItems, $chunkSize);
        $chunks = [$chunk];

        // We'll create an array of chunks by retrieving the previous
        // chunk value and getting the next chunk. We'll keep doing
        // this until the loop has reached the total chunks.

        for ($i = 1; $i < $chunk->totalChunks; $i++) {
            $chunks[] = end($chunks)->next();
        }

        return $chunks;
    }
}
