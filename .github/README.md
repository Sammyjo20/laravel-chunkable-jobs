<div align="center">

# Laravel Chunkable Jobs

![Build Status](https://github.com/sammyjo20/laravel-chunkable-jobs/actions/workflows/tests.yml/badge.svg)

</div>

This package allows you to split up a process into multiple jobs with their own chunk. This is perfect for processing lots of data as you can delegate the processing into separate jobs or if you are retrieving data from a paginated API. It works by processing the job and then queueing another job to process the next chunk until it reaches the end.

## Example

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;

class GetPageOfPokemon extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function defineChunk(): ?Chunk
    {
        $response = Http::asJson()->get('https://pokeapi.co/api/v2/pokemon');

    	$count = $response->json('count'); // 1154

    	return new Chunk(totalItems: $count, chunkSize: 1, startingPosition: 1);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        $response = Http::asJson()->get(sprintf('https://pokeapi.co/api/v2/pokemon?limit=%s&offset=%s', $chunk->limit, $chunk->offset));

    	$data = $response->json();

    	// Store data of response
    }
}
```

## Installation

Install the package through Composer. This package requires PHP 8.1+ and Laravel 8 or higher.

```bash
composer require sammyjo20/laravel-chunkable-jobs
```

## Getting Started

Create a new job and remove the `handle` method from the job. Next, extend the `ChunkableJob` class. You will be required to add two methods to your class, a `defineChunk` method and `handleChunk` method. In my example, I will be fetching every Pokemon from the Pokemon API and storing it into my application. You should have something like the following.

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;

class GetPageOfPokemon extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function defineChunk(): ?Chunk
    {
        //
    }

    protected function handleChunk(Chunk $chunk): void
    {
        //
    }
}
```

Next, we’ll need to define our chunk, this tells the chunkable job how many items it has to process and the size of the chunks so it knows how many times to run the “handleChunk” method. Inside of this method, you can return a chunk. This chunk accepts three arguments: `totalItems` , `chunkSize` and `startingPosition` . If you return null or a chunk without any totalItems, `handleChunk` will not be processed.

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;

public function defineChunk(): ?Chunk
{
    $response = Http::asJson()->get('https://pokeapi.co/api/v2/pokemon');

    $count = $response->json('count'); // 1154

    return new Chunk(totalItems: $count, chunkSize: 1, startingPosition: 1);
}
```

### Chunk Constructor

- **totalItems**: The amount of items that you want to chunk through, for example if I had 100 items with a chunk size of 10, it would create 10 chunks.
- **chunkSize:** The size of each chunk. If you are dealing with a paginated API, this is the same as the per page of that API.
- **startingPosition:** The starting position of the chunk, it defaults to 1 but if you want to resume a job, you can change this startingPosition.

Next, we’ll want to write the logic to process each chunk. In my example, I want to make an API call for that chunk and then store the response. The `handleMethod`  will be executed on every chunk and will contain useful information about the chunk.

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;

protected function handleChunk(Chunk $chunk): void
{
    $response = Http::asJson()->get(sprintf('https://pokeapi.co/api/v2/pokemon?limit=%s&offset=%s', $chunk->limit, $chunk->offset));

    $data = $response->json();

    // Store data of response
}
```

### Chunk Properties

- **totalItems:** The total items provided when the chunk was created. This property does not change.
- **totalChunks:** The total number of chunks generated when the chunk was created. This property does not change.
- **remainingItems:** The remaining items in the chunk. This property decreases as the chunked jobs are dispatched.
- **remainingChunks:** The remaining chunks. This property decreases as the chunked jobs are dispatched.
- **originalSize:** The size of the chunk. This property does not change.
- **size:** The size of the current chunk. This property will only change on the last chunk if there is a remainder.
- **limit:** The limit of the current chunk. Similar to size, it is designed to help you interact with APIs that operate a limit/offset
- **offset:** The offset of the current chunk. This property increases as the chunked jobs are dispatched
- **position:** The current position of the chunk. It is designed to act as “page” if you are dealing with a paginated API. This will increase as the chunked jobs are dispatched.
- **metadata:** An array if you would like to apply any metadata to the chunk. Metadata will be passed to all next chunks.

### Chunk Methods

- **next:** Allows you to get the next chunk. It is an immutable method so the original object is not affected.
- **move:** Allows you to move to a given chunk position. It is immutable by default but you can make it immutable.
- **replace:** Allows you to replace the current object with another chunk.
- **isFirst:** Specifies if the chunk is the first chunk.
- **isNotFirst:** Opposite of isFirst
- **isLast:** Specifies if the chunk is the last chunk.
- **isNotLast:** Opposite of isFirst
- **isEmpty:** Specifies if the chunk is empty, which means the totalItems property is zero.
- **isNotEmpty:** Opposite of isEmpty

## Dispatching

To dispatch a chunkable job, it's exactly the same. The default behaviour of chunkable jobs is to process one job, then dispatch the next after it has been successfully processed. 

```php
<?php

GetPageOfPokemon::dispatch();
```

## Dispatching every chunked job at once

Sometimes you may want to throw as much resource as you can to a specific chunked job. If processing one chunk at a time is not suitable and you would rather dispatch every chunk straight away, you can use the `dispatchAllChunks` static method on the chunkable job. It will accept constructor arguments through the parameters. Alternatively, you can use the `BulkChunkDispatcher` class.

```php
<?php

use Sammyjo20\ChunkableJobs\BulkChunkDispatcher;

// Will dispatch all jobs at once 🚀

GetPageOfPokemon::dispatchAllChunks();

// or

BulkChunkDispatcher::dispatch(new GetPageOfPokemon);
```

## Stopping Chunking Early

Sometimes you might want to stop the chunking process early. You can use the `stopChunking` method and the job won’t dispatch the next chunk.

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;

protected function handleChunk(Chunk $chunk): void
{
    $response = Http::asJson()->get(sprintf('https://pokeapi.co/api/v2/pokemon?limit=%s&offset=%s', $chunk->limit, $chunk->offset));

    // Stop chunking early...

    if ($response->failed()) {
        $this->stopChunking();
    }
}
```

## Customising The Starting Chunk

Sometimes you might want to resume a chunkable job where it may have failed previously or paused. You can set the chunk on the job instance before you dispatch the job.

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;

$job = new GetPageOfPokemon;

$job->setChunk(new Chunk(totalItems: 100, chunkSize: 10, startingPosition: 5));

dispatch($job);
```

## Using `ChunkRange` to iterate over all chunks

If you need to iterate over every chunk, you can use the `ChunkRange` class. This will return a generator that you can iterate over to get every chunk.

```php
use Sammyjo20\ChunkableJobs\ChunkRange;

$chunkRange = ChunkRange::create(30, 10);

foreach($chunkRange as $chunk) {
    // Handle $chunks
}
```

## Unknown Size Chunking

Sometimes you might not know the size/limit that you want to chunk for and therefor you want to keep chunking infinitely and stop when you know when you have reached a limit. If you would like to do this, you can use the `UnknownSizeChunk` which will set the size to `PHP_MAX_INT` (which is a really really big number) and you can stop when you like.

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;
use Sammyjo20\ChunkableJobs\UnknownSizeChunk;

class GetPageOfPokemon extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function defineChunk(): ?Chunk
    {
        return UnknownSizeChunk(chunkSize: 100);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        // Keep processing
        // When ready to stop: 
				
        if ($stop === true) {
            $this->stopChunking();
        }
	}
}
```

## Setting the next chunk

Sometimes you might want to change the chunking entirely, if you would like to do this, you can use the `nextChunk` method when chunking and the next chunk will be replaced by this chunk.

```php
<?php

protected function handleChunk(Chunk $chunk): void
{
    $chunk = new Chunk(100, 10)
    $chunk = $chunk->move(5);

    $this->setNextChunk($chunk);
}
```

## SetUp & TearDown
The `setUp` and `tearDown` methods are called before and after the chunking process. This is useful if you need to do some setup before the chunking starts and some cleanup after all the job chunks have finished processing.

```php
<?php

use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;
use Illuminate\Support\Facades\Log;

class GetPageOfPokemon extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function defineChunk(): ?Chunk
    {
        $response = Http::asJson()->get('https://pokeapi.co/api/v2/pokemon');

    	$count = $response->json('count'); // 1154

    	return new Chunk(totalItems: $count, chunkSize: 1, startingPosition: 1);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        $response = Http::asJson()->get(sprintf('https://pokeapi.co/api/v2/pokemon?limit=%s&offset=%s', $chunk->limit, $chunk->offset));

    	$data = $response->json();

    	// Store data of response
    }
    
    protected function setUp(): void
    {
        Log::info('Starting the retrieval process...');
    }
    
    protected function tearDown(): void
    {
        Log::info('Finished the retrieval process!');
    }
}
```

## Support This Project

<a href='https://ko-fi.com/sammyjo20' target='_blank'><img height='35' style='border:0px;height:46px;' src='https://az743702.vo.msecnd.net/cdn/kofi3.png?v=0' border='0' alt='Buy Me a Coffee at ko-fi.com' />
