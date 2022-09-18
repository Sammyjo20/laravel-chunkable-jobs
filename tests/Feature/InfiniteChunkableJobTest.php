<?php

use Sammyjo20\ChunkableJobs\Tests\Fixtures\PaginatedJob;
use Sammyjo20\ChunkableJobs\Tests\Fixtures\UnknownLengthJob;

test('it works', function () {
    UnknownLengthJob::dispatch();
});
