<?php

use App\Http\Controllers\TweetController;
use Illuminate\Support\Facades\Route;

Route::controller(TweetController::class)->group(function() { // Route group by controller
    Route::get('/tweet/{id}', 'getSingleTweet')->where('id', '[0-9]+')->name('tweet.show');  // Get single tweet

    Route::prefix('tweets')->group(function () { // Route group by prefix
        Route::get('/', 'getTweets')->name('tweets.index');  // Get all tweets

        Route::get('/most-liked', 'getMostLikedTweet')->name('tweets.mostLiked');  // Get the most liked tweet

        Route::get('/most-commented', 'getMostCommentedTweet')->name('tweets.mostCommented'); // Get the most commented tweet
    });
});

// Route::fallback(function () {
//     abort(404);
// });

?>