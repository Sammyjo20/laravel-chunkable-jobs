<?php

namespace Sammyjo20\ChunkableJobs\Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;
use Sammyjo20\ChunkableJobs\UnknownSizeChunk;

class UnknownLengthJob  extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Define the chunk
     *
     * @return Chunk|null
     */
    protected function defineChunk(): ?Chunk
    {
        return new UnknownSizeChunk(100, 6000);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        ray($chunk);

        if ($chunk->position === 15) {
            $this->stopChunking();
        }
    }
}
