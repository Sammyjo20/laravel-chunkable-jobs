<?php

namespace Sammyjo20\ChunkableJobs;

class UnknownSizeChunk extends Chunk
{
    /**
     * Constructor
     *
     * @param int $chunkSize
     */
    public function __construct(int $chunkSize)
    {
        parent::__construct(PHP_INT_MAX, $chunkSize);
    }
}
