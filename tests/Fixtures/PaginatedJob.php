<?php

namespace Sammyjo20\ChunkableJobs\Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;

class PaginatedJob  extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected function defineChunk(): ?Chunk
    {
        return new Chunk(30, 10);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        ray($chunk);
    }
}
