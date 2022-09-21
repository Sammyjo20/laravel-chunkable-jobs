<?php declare(strict_types=1);

namespace Sammyjo20\ChunkableJobs\Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Sammyjo20\ChunkableJobs\Chunk;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Sammyjo20\ChunkableJobs\ChunkableJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TearDownJob extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function defineChunk(): ?Chunk
    {
        return new Chunk(30, 10);
    }

    protected function tearDown(): void
    {
        cache()->increment('tearDown');
    }

    protected function handleChunk(Chunk $chunk): void
    {
        cache()->put($chunk->position, $chunk);

        if ($chunk->position === 2) {
            $this->stopChunking();
        }
    }
}
