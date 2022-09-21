<?php declare(strict_types=1);

namespace Sammyjo20\ChunkableJobs;

use Sammyjo20\ChunkableJobs\Exceptions\BulkChunkDispatcherException;

class BulkChunkDispatcher
{
    /**
     * Bulk Dispatch Chunks
     *
     * @param ChunkableJob $job
     * @return void
     * @throws BulkChunkDispatcherException
     */
    public static function dispatch(ChunkableJob $job): void
    {
        $chunk = $job->defineChunk();

        if (is_null($chunk)) {
            return;
        }

        if ($chunk instanceof UnknownSizeChunk) {
            throw new BulkChunkDispatcherException('You cannot iterate through an UnknownSizeChunk.');
        }

        $chunkRange = ChunkRange::create($chunk->totalItems, $chunk->originalSize);

        // Now we'll loop through the chunk range and dispatch every job. We'll
        // also make sure to disable the next functionality of every chunk
        // so they don't chain, that would be bad!

        foreach ($chunkRange as $chunk) {
            $chunk->disableNext();

            $dispatchJob = (clone $job)->setChunk($chunk);

            dispatch($dispatchJob);
        }
    }
}
