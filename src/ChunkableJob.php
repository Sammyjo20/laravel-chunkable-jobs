<?php

namespace Sammyjo20\ChunkableJobs;

abstract class ChunkableJob
{
    /**
     * The chunk that powers the chunkable job.
     *
     * @var Chunk|null
     */
    protected ?Chunk $chunk = null;

    /**
     * The delay interval between chunks.
     *
     * @var int
     */
    protected int $chunkInterval = 0;

    /**
     * Should we process the next chunk.
     *
     * @var bool
     */
    protected bool $processNextChunk = true;

    /**
     * Any extra unset properties.
     *
     * @var array
     */
    protected array $extraUnsetProperties = [];

    /**
     * Process the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (is_null($this->chunk)) {
            $this->setUp();

            $this->setChunk($this->defineChunk());
        }

        $chunk = $this->chunk;

        if ($chunk instanceof Chunk && $chunk->isNotEmpty()) {
            $this->handleChunk($chunk);

            $this->prependNextJob();
        }
    }

    /**
     * Extend this method to write logic before the chunkable job is processed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        //
    }

    /**
     * Prepend the next job in the chunkable job chain.
     *
     * @return void
     */
    protected function prependNextJob(): void
    {
        $chunk = $this->chunk;

        // We don't want to process the next chunk if it's the last chunk, we've stopped chunking
        // or if the job has been released/deleted.

        if ($this->processNextChunk === false || $chunk->isLast() || $this?->job->isDeletedOrReleased()) {
            return;
        }

        // We will need to unset the following properties because they can cause issues
        // when Haystack needs to serialize the cloned job and store it in the
        // database. We are unsetting the haystack metadata as Haystack will
        // set this for us.

        $unsetProperties = array_merge([
            'job', 'middleware', 'haystack', 'haystackBaleId', 'haystackBaleAttempts', 'chunk'
        ], $this->extraUnsetProperties);

        $clone = clone $this;

        foreach ($unsetProperties as $property) {
            unset($clone->$property);
        }

        // Next, we'll set the chunk of the clone to the next chunk.

        $clone->setChunk($chunk->next());

        // Finally, we'll dispatch the next chunk

        $this->dispatchNextChunk($clone);
    }

    /**
     * Dispatch the next chunk
     *
     * @param object $job
     * @return void
     */
    protected function dispatchNextChunk(object $job): void
    {
        if (is_null($job->delay)) {
            $job->delay($this->chunkInterval);
        }

        // We'll now dispatch the job to the queue, and it will
        // be processed with the chunk and delay.

        dispatch($job);
    }

    /**
     * Set the chunk on the chunkable job
     *
     * @param Chunk|null $chunk
     * @return $this
     */
    public function setChunk(?Chunk $chunk): static
    {
        $this->chunk = $chunk;

        return $this;
    }

    /**
     * Stop chunking
     *
     * @return $this
     */
    public function stopChunking(): static
    {
        $this->processNextChunk = false;

        return $this;
    }

    /**
     * Define the chunk. If it's null or a chunk with zero items the chunkable job will stop early.
     *
     * @return Chunk|null
     */
    abstract protected function defineChunk(): ?Chunk;

    /**
     * Handle the chunk.
     *
     * @param Chunk $chunk
     * @return void
     */
    abstract protected function handleChunk(Chunk $chunk): void;
}
