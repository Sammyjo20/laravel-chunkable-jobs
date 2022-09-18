<?php

namespace Sammyjo20\ChunkableJobs\Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;

class NullChunkJob extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected function defineChunk(): ?Chunk
    {
        return null;
    }

    protected function handleChunk(Chunk $chunk): void
    {
        cache()->put('processed', true);
    }
}
