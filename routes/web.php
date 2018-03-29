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

Route::prefix('users')->group(function(){
    Route::get("/","UserController@getUsers");
    Route::get("/search/{username}", "UserController@searchUser");
    Route::get("/{username}","UserController@getUser");
    Route::patch("/","UserController@patch")->middleware('auth:api');
    Route::post("/", "UserController@post")->middleware('auth:api');
});

Route::prefix("news")->group(function(){
    Route::get("/", "NewsController@getNews");
    Route::get("/{slug}", "NewsController@getSingle");
});

Route::prefix("streams")->group(function(){
    Route::get("/", "StreamController@getStreams");
    Route::get("/{slug}", "StreamController@getStream");
    Route::post("/chat", "StreamController@sendMessage")->middleware("auth:api");
});

Route::prefix("posts")->group(function(){
    Route::post("/", "PostsController@posts")->middleware("auth:api");
});