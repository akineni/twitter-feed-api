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

    /**
     * Initialize the TweetService.
     *
     * The constructor optionally sets up the service for testing purposes by skipping
     * the loading of mock tweets. loadMockTweets uses facades that cannot be used in the PHPUnit tests
     *
     * @param bool $test Indicates whether the service is being initialized for testing. 
     *                   If true, the `loadMockTweets` method will not be called.
     */
    public function __construct(bool $test = false)
    {
        // Import the provided mock_tweets.json file into the storage directory for easy access.
        if (!$test) $this->loadMockTweets();        
        $this->maxFetchedTweets = (int) env('MAX_FETCHED_TWEETS', 20);
    }

    /**
     * Create and return a test instance of the TweetService.
     *
     * This method is intended for testing purposes and may initialize the service
     * with mock data or a testing configuration.
     *
     * @return TweetService An instance of the TweetService for testing.
     */
    public static function getTestInstance(): TweetService
    {
        return new TweetService(true);
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
                $this->setTweets($decodedData['data']);
            } else {
                throw new \Exception('Invalid JSON or missing "data" key in ' . $mockDataFilename);
            }
        } catch (FileNotFoundException $e) {
            Log::error("Mock tweets file not found: {$e->getMessage()}", ['file' => $mockDataFilename]);
            $this->setTweets([]); // Set tweets to an empty array
        } catch (\Exception $e) {
            Log::error("Error loading mock tweets: {$e->getMessage()}", ['file' => $mockDataFilename]);
            $this->setTweets([]); // Set tweets to an empty array
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

    /**
     * Set the collection of tweets.
     *
     * @param array $tweets An array of tweet data, where each tweet is an associative array with the following keys:
     *                      - 'id' (string): The unique identifier of the tweet.
     *                      - 'text' (string): The content of the tweet.
     *                      - 'likes' (int): The number of likes the tweet has received.
     *                      - 'comments' (int): The number of comments the tweet has received.
     *                      - 'created_at' (string): The timestamp when the tweet was created.
     * 
     *                      Example:
     *                      [
     *                          ['id' => '1', 'text' => 'Tweet content', 'likes' => 466, 'comments' => 100, 'created_at' => '2024-12-26T23:44:18.581Z'],
     *                          ['id' => '2', 'text' => 'Another tweet', 'likes' => 87, 'comments' => 91, 'created_at' => '2024-12-11T03:51:59.630Z']
     *                      ]
     *
     * @return void
     */
    public function setTweets(array $tweets): void
    {
        $this->tweets = $tweets;
    }

    /**
     * Retrieve the list of tweets.
     *
     * @return array The array of tweets.
     */
    public function getTweets(): array
    {
        return $this->tweets;
    }
}
