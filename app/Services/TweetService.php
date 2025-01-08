<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TweetService
{
    private $tweets, $maxFetchedTweets;
    private const CACHE_KEY = 'tweets';

    public function __construct()
    {
        // Import the provided mock_tweets.json file into the storage directory for easy access.
        $this->loadMockTweets();
        $this->maxFetchedTweets = (int) env('MAX_FETCHED_TWEETS', 20);
    }

    private function loadMockTweets(): void
    {
        $mockDataFilename = env('MOCK_DATA_FILENAME', 'mock_tweets.json');

        try {
            // Check if the file exists first
            if (!Storage::exists($mockDataFilename)) {
                throw new FileNotFoundException("The file {$mockDataFilename} was not found.");
            }
            $fileContents = Storage::get($mockDataFilename); // Try to get the file contents
            $decodedData = json_decode($fileContents, true); // Decode the JSON

            // Check if decoding was successful
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['data'])) {
                $this->tweets = $decodedData['data'];
            } else {
                throw new \Exception('Invalid JSON or missing "data" key in ' . $mockDataFilename);
            }
        } catch (FileNotFoundException $e) {
            Log::error("Mock tweets file not found: {$e->getMessage()}", ['file' => $mockDataFilename]);
            $this->tweets = []; // Set tweets to an empty array
        } catch (\Exception $e) {
            Log::error("Error loading mock tweets: {$e->getMessage()}", ['file' => $mockDataFilename]);
            $this->tweets = []; // Set tweets to an empty array
        }
    }

    /**
     * Get a single tweet by its ID.
     *
     * @param int $tweetId
     * @return array|null
     */
    public function getSingleTweet($tweetId): ?array
    {
        // Find the tweet with the specified ID
        return collect($this->tweets)->firstWhere('id', $tweetId) ?: null;
    }

    /**
     * Get paginated tweets with optional pagination.
     *
     * @param int $page
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getAllTweets(int $page = 1, ?int $perPage = null): LengthAwarePaginator
    {
        // Use the class property if perPage is not provided
        $perPage = $perPage ?? $this->maxFetchedTweets;

        // Use Laravel's caching mechanism to store results for 5 minutes
        return Cache::remember($this->generateCacheKey($page, $perPage), 300, function () use ($page, $perPage) {
            // Paginate the tweets
            $offset = ($page - 1) * $perPage;
            $paginatedTweets = array_slice($this->tweets, $offset, $perPage);

            // Returns a paginator of 20 tweets from a specific public Twitter user’s timeline.
            return new LengthAwarePaginator(
                $paginatedTweets,
                count($this->tweets), // Total number of items
                $perPage,
                $page,
                [
                    'path' => request()->url(), // Current URL
                    'query' => request()->query(), // Preserve query parameters
                ]
            );
        });
    }

    /**
     * Get the most liked tweet.
     *
     * @return array|null
     */
    public function getMostLikedTweet(): ?array
    {
        // Returns the tweet with the highest number of likes from the user’s timeline.
        return collect($this->tweets)->sortByDesc('likes')->first();
    }

    /**
     * Get the most commented tweet.
     *
     * @return array|null
     */
    public function getMostCommentedTweet(): ?array
    {
        // Returns the tweet with the highest number of replies (comments) from the user’s timeline.
        return collect($this->tweets)->sortByDesc('comments')->first();
    }

    /**
     * Generate a cache key for the given pagination.
     *
     * @param int $page
     * @param int $perPage
     * @return string
     */
    private function generateCacheKey(int $page, int $perPage): string
    {
        return self::CACHE_KEY . "_page_{$page}_per_page_{$perPage}";
    }
}
