<?php

namespace Sammyjo20\ChunkableJobs\Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;

class NextChunkJob extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected function defineChunk(): ?Chunk
    {
        return new Chunk(30, 10);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        cache()->put($chunk->totalItems . ':' . $chunk->position, $chunk);

        if ($chunk->position === 2 && $chunk->totalItems === 30) {
            $this->setNextChunk(new Chunk(100, 10));
        }

        if ($chunk->position === 2 && $chunk->totalItems === 100) {
            $this->stopChunking();
        }
    }
}
