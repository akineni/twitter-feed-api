<?php

namespace Tests\Unit;

use App\Services\TweetService;
use PHPUnit\Framework\TestCase;

class TweetServiceTest extends TestCase
{
    private TweetService $tweetService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tweetService = TweetService::getTestInstance();
    }

    public function testGetSingleTweetReturnsTweetIfFound()
    {
        $tweets = [
            [
                "id" => "1",
                "text" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                "likes" => 466
            ],
            [
                "id" => "2",
                "text" => "At vero eos et accusamus et iusto odio dignissimos.",
                "likes" => 87
            ]
        ];

        $this->tweetService->setTweets($tweets);
        
        // Test if the tweet is returned
        $result = $this->tweetService->getSingleTweet(1);
        $this->assertNotNull($result);
        $this->assertEquals(466, $result['likes']);
    }

    public function testGetSingleTweetReturnsNullIfNotFound()
    {
        // Test if null is returned when tweet is not found
        $result = $this->tweetService->getSingleTweet(3);
        $this->assertNull($result);
    }
}