<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class TweetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetSingleTweetReturnsTweetIfExists()
    {
        // Simulate a tweet in storage
        Storage::fake('local');
        $mockData = json_encode(['data' => [['id' => 1, 'content' => 'Tweet 1']]]);
        Storage::put('mock_tweets.json', $mockData);

        // Perform a request to the controller's route
        $response = $this->getJson('/api/tweet/1');

        // Assert the response contains the correct data
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'id' => 1,
                         'content' => 'Tweet 1'
                     ]
                 ]);
    }

    public function testGetSingleTweetReturnsNotFoundIfDoesNotExist()
    {
        // Simulate a missing tweet
        Storage::fake('local');
        $mockData = json_encode(['data' => [['id' => 1, 'content' => 'Tweet 1']]]);
        Storage::put('mock_tweets.json', $mockData);

        // Perform a request to a non-existing tweet
        $response = $this->getJson('/api/tweet/999');

        // Assert the response is not found
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => "Operation successful",
                     'data' => null
                 ]);
    }
}
