<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix("home")->group(function(){
    Route::get("/posts", "HomeController@posts");
    Route::get("/coupons", "HomeController@coupons");
});

Route::prefix('users')->group(function(){
    Route::get("/tippers", "UserController@tippers")->middleware("auth:api");
    Route::get("/me","UserController@me")->middleware('auth:api');
    Route::get("/me/statistics","UserController@statistics")->middleware('auth:api');
    Route::get("/activities","ActivityController@getActivities")->middleware("auth:api");
    Route::get("/","UserController@getUsers")->middleware('auth:api');
    Route::get("/search/{username}", "UserController@searchUser");
    Route::get("/{username}","UserController@getUser");
    Route::patch("/","UserController@patch")->middleware('auth:api');
    Route::patch("/image","UserController@image")->middleware('auth:api');
    Route::patch("/reset","UserController@reset")->middleware('auth:api');
    Route::post("/forgot","UserController@forgot")->middleware('auth:api');
    Route::post("/", "UserController@post");
});

Route::prefix("news")->group(function(){
    Route::get("/", "NewsController@getNews");
    Route::get("/side", "NewsController@sideNews");
    Route::get("/{slug}", "NewsController@getSingle");
});

Route::prefix("streams")->group(function(){
    Route::get("/", "StreamController@getStreams");
    Route::get("/{slug}", "StreamController@getStream");
    Route::post("/chat", "StreamController@sendMessage")->middleware("auth:api");
});

Route::prefix("message")->group(function(){
    Route::get("/inbox", "ConversationController@inbox")->middleware("auth:api");
    Route::get("/outbox", "ConversationController@outbox")->middleware("auth:api");
    Route::get("/read/{id}", "ConversationController@read")->middleware("auth:api");
    Route::post("/create", "ConversationController@create")->middleware("auth:api");
    Route::post("/reply", "ConversationController@reply")->middleware("auth:api");
    Route::delete("/delete", "ConversationController@delete")->middleware("auth:api");
});

Route::prefix("posts")->group(function(){
    Route::post("/", "PostsController@posts")->middleware("auth:api");
    Route::get("{post_id}","PostsController@post");
    Route::get("{post_id}/like_count","PostsController@likeCount");
    Route::post("like","PostsController@like")->middleware("auth:api");
    Route::get("{post_id}/liked","PostsController@hasLiked")->middleware("auth:api");
    Route::get("/comment/{comment_id}","PostsController@getByComment");
    Route::get("/feed/{username}","PostsController@getFeedOfUser");
    Route::get("{post_id}/likers","PostsController@likers");
    Route::post("repost","PostsController@rePost")->middleware("auth:api");
    //Route::get("/feed", "PostsController@myFeed")->middleware("auth:api");
    Route::get("/couponDetail/{coupon_id}","PostsController@couponGames")->middleware("auth:api");
    Route::get("/couponStatus/{coupon_id}","PostsController@couponStatus");

});

Route::prefix("comments")->group(function(){
    Route::get("/{post}", "CommentsController@select");
    Route::post("/", "CommentsController@create")->middleware("auth:api");
    Route::patch("/", "CommentsController@update")->middleware("auth:api");
    Route::delete("/{yorum_id}", "CommentsController@delete")->middleware("auth:api");
});

Route::prefix("notifications")->group(function(){
    Route::get("/", "NotificationsController@notifications")->middleware("auth:api");
    Route::patch("/read", "NotificationsController@read")->middleware("auth:api");
});

Route::prefix("sliders")->group(function(){
    Route::get("/nav", "SlidersController@nav");
    Route::get("/video", "SlidersController@video");
    Route::get("/header", "SlidersController@header");
});

Route::prefix("forecast")->group(function(){
   Route::get("/", "ForecastController@forecast");
   Route::get("/{id}", "ForecastController@detail");
   Route::post("/", "ForecastController@create")->middleware("auth:api");
   Route::patch("/", "ForecastController@update")->middleware("auth:api");
   Route::delete("/", "ForecastController@delete")->middleware("auth:api");
});

Route::prefix("points")->group(function(){
    Route::post("/", "PointsController@create")->middleware("auth:api");
    Route::patch("/", "PointsController@update")->middleware("auth:api");
});

Route::prefix("dictionary")->group(function(){
    Route::get("/", "DictionaryController@index");
    Route::get("/{slug}", "DictionaryController@detail");

});


Route::post("/follow", "FollowController@follow")->middleware("auth:api");
Route::post("/unfollow", "FollowController@unfollow")->middleware("auth:api");

