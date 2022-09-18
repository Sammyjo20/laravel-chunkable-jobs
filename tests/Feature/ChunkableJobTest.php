<?php

use Sammyjo20\ChunkableJobs\Tests\Fixtures\PaginatedJob;

test('it works', function () {
    PaginatedJob::dispatch();
});
