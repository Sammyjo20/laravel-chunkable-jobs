<?php declare(strict_types=1);

namespace Sammyjo20\ChunkableJobs\Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Sammyjo20\ChunkableJobs\Chunk;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Sammyjo20\ChunkableJobs\ChunkableJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InvisibleIgnoredPropertiesJob extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $ignoredProperties = ['name'];

    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @var string|null
     */
    private ?string $codeword = null;

    public function __construct()
    {
        $this->name = 'Sam';
        $this->codeword = 'Flip';
    }

    public function defineChunk(): ?Chunk
    {
        return new Chunk(30, 10);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        cache()->put($chunk->position, $this->name . $this->codeword);
    }

    protected function modifyClone(ChunkableJob $job): static
    {
        unset($job->codeword);

        return $job;
    }
}
