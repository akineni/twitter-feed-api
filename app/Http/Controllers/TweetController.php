<?php

namespace App\Http\Controllers;

use App\Services\TweetService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TweetController extends Controller
{
    private TweetService $tweetService;

    public function __construct(TweetService $tweetService)
    {
        $this->tweetService = $tweetService;
    }

    public function getSingleTweet($tweetId): JsonResponse
    {
        $tweet = $this->tweetService->getSingleTweet($tweetId);
        return Response::success($tweet); // Macro defined in ResponseMacroServiceProvider
    }

    public function getTweets(Request $request): JsonResponse
    {
        try {
            $maxFetchedTweets = env('MAX_FETCHED_TWEETS', 20);
            $perPage = $request->get('per_page', $maxFetchedTweets); // Default to 20 tweets per page
            $page = $request->get('page', 1); // Default to page 1

            $tweets = $this->tweetService->getAllTweets((int) $page, (int) $perPage);
            return Response::success($tweets);
        } catch (Exception $e) {
            return Response::error("An error occurred while fetching the tweets", 500);
        }    
    }

    public function getMostLikedTweet(): JsonResponse
    {
        try {
            $tweet = $this->tweetService->getMostLikedTweet();
            return Response::success($tweet);
        } catch (Exception $e) {
            return Response::error("An error occurred while fetching the most liked tweet", 500);
        }
    }

    public function getMostCommentedTweet(): JsonResponse
    {
        try {
            $tweet = $this->tweetService->getMostCommentedTweet();
            return Response::success($tweet);
        } catch (Exception $e) {
            return Response::error("An error occurred while fetching the most commented tweet", 500);
        }
    }
}

?>