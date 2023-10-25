<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\LanguageController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\SongController;
use App\Http\Controllers\api\AdController;
use App\Http\Controllers\api\usersettingsController;
use App\Http\Controllers\api\LibraryController;
use App\Http\Controllers\api\UserplaylistController;
use App\Http\Controllers\api\ArtistController;
use App\Http\Controllers\api\searchdataController;
use App\Http\Controllers\api\featuredPlaylistsController;
use App\Http\Controllers\api\PodcastController;
use App\Http\Controllers\api\FavouriteController;
use App\Http\Controllers\api\HomelistingController;
use App\Http\Controllers\api\EmailController;

use App\Models\User;
use App\Models\Song;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//SELECT tag_1, COUNT(tag_1) FROM bettings GROUP BY tag_1 HAVING COUNT(tag_1) > 1
Route::post('forgot-password', 'App\Http\Controllers\api\AuthController@forgot_password');
Route::post('verify-code', 'App\Http\Controllers\api\AuthController@verifyCode');
Route::post('ResetPassword', 'App\Http\Controllers\api\AuthController@ResetPassword');
Route::get('banner_get', [HomelistingController::class, 'banner_get']);

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {


    /*
    |--------------------------------------------------------------------------
    | Routes for Auth
    |--------------------------------------------------------------------------
    */

    Route::post('login', 'App\Http\Controllers\api\AuthController@login');
    Route::post('logout', 'App\Http\Controllers\api\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\api\AuthController@refresh');
    Route::post('me', 'App\Http\Controllers\api\AuthController@me');
    Route::post('editProfile', 'App\Http\Controllers\api\AuthController@editProfile');
    Route::post('profileUpdate', 'App\Http\Controllers\api\AuthController@profileUpdate');
    Route::post('changePass', 'App\Http\Controllers\api\AuthController@changePass');
    Route::post('register', 'App\Http\Controllers\api\AuthController@Register');
    
    Route::post('change-password', 'App\Http\Controllers\api\AuthController@change_password');
    Route::post('socialLogin', 'App\Http\Controllers\api\AuthController@socialLogin');
    Route::get('sentTestMail', 'App\Http\Controllers\api\AuthController@sentTestMail');
    Route::get('myProfile', 'App\Http\Controllers\api\AuthController@myProfile');
    Route::post('followUser', 'App\Http\Controllers\api\AuthController@followUser');
    Route::post('unfollowUser', 'App\Http\Controllers\api\AuthController@unFollowUser');
    Route::post('changeCoverImage', 'App\Http\Controllers\api\AuthController@changeCoverImage');
    Route::post('changeProfileImage', 'App\Http\Controllers\api\AuthController@changeProfileImage');
    Route::post('blockUser', 'App\Http\Controllers\api\AuthController@blockUser');
    
    
    /*
    |--------------------------------------------------------------------------
    | Routes for Songs
    |--------------------------------------------------------------------------
    */
    
    Route::post('song', [SongController::class, 'index']);
    Route::get('song_by_id/{id}', [SongController::class, 'song_by_id']);
    Route::get('artist_song_list/{id}', [SongController::class, 'artist_song_list']);
    Route::get('language_song_list/{id}', [SongController::class, 'language_song_list']);
    Route::get('category_song_list/{id}', [SongController::class, 'category_song_list']);
    //Route::post('most_played_song_store/{id}', [SongController::class, 'most_played_song_store']);
    Route::get('most_played_song', [SongController::class, 'most_played_song']);
    Route::get('mix_songs', [SongController::class, 'mix_songs']);
    Route::get('autoplay_songs', [SongController::class, 'autoplay_songs']);
    Route::post('song_played_date_store', [SongController::class, 'song_played_date_store']);
    Route::get('song_played_by_date/{id}', [SongController::class, 'song_played_by_date']);
    Route::post('user_song_like', [SongController::class, 'user_song_like']);
    Route::get('user_song_like_get/{id}', [SongController::class, 'user_song_like_get']);
    Route::post('user_song_unlike', [SongController::class, 'user_song_unlike']);
    Route::get('latest_songs', [SongController::class, 'latest_songs']);
    Route::get('artist_played_count/{id}', [SongController::class, 'artist_played_count']);
    Route::get('popular_videos', [SongController::class, 'popular_videos']);
    Route::get('album_song_list/{id}', [SongController::class, 'album_song_list']);
    Route::get('get_trending_songs', [SongController::class, 'get_trending_songs']);

    
	    /*
    |--------------------------------------------------------------------------
    | Routes for Artist
    |--------------------------------------------------------------------------
    */
    Route::get('artist/{skip}', [ArtistController::class, 'index']);
    Route::get('get_artist_form', [ArtistController::class, 'get_artist_form']);
    Route::post('add_artist', [ArtistController::class, 'store']);
    Route::get('edit_artist/{id}', [ArtistController::class, 'edit']);
    Route::post('update_artist/{id}', [ArtistController::class, 'update']);
    Route::get('delete_artist/{id}', [ArtistController::class, 'delete']);
    Route::get('best_artist', [ArtistController::class, 'best_artist']);
    Route::post('follow_artist', [ArtistController::class, 'follow_artist']);
    Route::post('unfollow_artist', [ArtistController::class, 'unfollow_artist']);
    Route::get('get_all_follow_artist/{id}', [ArtistController::class, 'get_all_follow_artist']);
    Route::get('featured_artist', [ArtistController::class, 'featured_artist']);
    
    
    /*
	    /*
    |--------------------------------------------------------------------------
    | Routes for Featured Playlists
    |--------------------------------------------------------------------------
    */
    Route::get('featured', [featuredPlaylistsController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Routes for Language
    |--------------------------------------------------------------------------
    */
    Route::get('language', [LanguageController::class, 'index']);
    Route::post('user_to_language', [LanguageController::class, 'user_to_language']);
    Route::get('get_user_language', [LanguageController::class, 'get_user_language']);

        /*
    |--------------------------------------------------------------------------
    | Routes for Advertisements
    |--------------------------------------------------------------------------
    */
    Route::get('advertisements', [AdController::class, 'index']);
    Route::post('location_ad_display', [AdController::class, 'location_ad_display']);
    Route::post('users_visit_store', [AdController::class, 'users_visit_store']);
    /*
  |--------------------------------------------------------------------------
  | Routes for category
  |--------------------------------------------------------------------------
  */
    Route::get('category', [CategoryController::class, 'index']);
    Route::get('top_genres/{id}', [CategoryController::class, 'top_genres']);

          /*
    |--------------------------------------------------------------------------
    | Routes for Usersettings
    |--------------------------------------------------------------------------
    */
    Route::post('Usersettings_store', [usersettingsController::class, 'store']);
    Route::post('Usersettings_update', [usersettingsController::class, 'update']);
    Route::get('Usersettings_get/{id}', [usersettingsController::class, 'index']);

        /*
  |--------------------------------------------------------------------------
  | Routes for library
  |--------------------------------------------------------------------------
  */
    Route::post('user_add_artist', [LibraryController::class, 'user_add_artist']);
    Route::get('user_add_artist_get/{id}', [LibraryController::class, 'user_add_artist_get']);
    Route::post('user_add_podcast', [LibraryController::class, 'user_add_podcast']);
    Route::get('user_add_podcast_get/{id}', [LibraryController::class, 'user_add_podcast_get']);

        /*
  |--------------------------------------------------------------------------
  | Routes for User playlists
  |--------------------------------------------------------------------------
  */
    Route::post('user_add_playlist', [UserplaylistController::class, 'user_add_playlist']);
    Route::post('user_add_song_playlist', [UserplaylistController::class, 'user_add_song_playlist']);
    Route::get('user_playlist_get/{userID}', [UserplaylistController::class, 'user_playlist_get']);
    Route::get('user_all_playlist_get/{userID}', [UserplaylistController::class, 'user_all_playlist_get']);
    Route::get('user_playlist_delete/{userID}', [UserplaylistController::class, 'user_playlist_delete']);
    Route::post('delete_user_playlist_song', [UserplaylistController::class, 'delete_user_playlist_song']);
    
        /*
    |--------------------------------------------------------------------------
    | Routes for Podcasts
    |--------------------------------------------------------------------------
    */

    Route::get('podcast_list', [PodcastController::class, 'index']);
    Route::post('user_podcast_like', [PodcastController::class, 'user_podcast_like']);
    Route::get('user_podcasts_like_get/{id}', [PodcastController::class, 'user_podcasts_like_get']);
    Route::post('user_podcast_unlike', [PodcastController::class, 'user_podcast_unlike']);
    Route::post('episode_played_date_store', [PodcastController::class, 'episode_played_date_store']);
    
     /*
    |--------------------------------------------------------------------------
    | Routes for Favourites 
    |--------------------------------------------------------------------------
    */
    Route::post('add_favourite_artist', [FavouriteController::class, 'add_favourite_artist']);
    Route::post('add_favourite_playlist', [FavouriteController::class, 'add_favourite_playlist']);
    Route::post('add_favourite_album', [FavouriteController::class, 'add_favourite_album']);
    Route::get('get_favourite_artist/{userid}', [FavouriteController::class, 'get_favourite_artist']);
    Route::get('get_favourite_playlist/{userid}', [FavouriteController::class, 'get_favourite_playlist']);
    Route::get('get_favourite_album/{userid}', [FavouriteController::class, 'get_favourite_album']);
    Route::post('add_unfavourite_playlist', [FavouriteController::class, 'add_unfavourite_playlist']);
    Route::post('add_unfavourite_artist', [FavouriteController::class, 'add_unfavourite_artist']);
    Route::post('add_unfavourite_album', [FavouriteController::class, 'add_unfavourite_album']);
    

    
        /*
    |--------------------------------------------------------------------------
    | Routes for Search
    |--------------------------------------------------------------------------
    */

     Route::get('contentsearch/{search}', [searchdataController::class, 'contentsearch']);
    
            /*
    |--------------------------------------------------------------------------
    | Routes for Albums
    |--------------------------------------------------------------------------
    */

     Route::get('get_albums', [SongController::class, 'get_albums']);
                 /*
    |--------------------------------------------------------------------------
    | Routes for banner
    |--------------------------------------------------------------------------
    */


        /*
    |--------------------------------------------------------------------------
    | Routes for home listing 
    |--------------------------------------------------------------------------
    */
    Route::get('home_listing/{userid}', [HomelistingController::class, 'home_listing']);
    /*
            /*
    |--------------------------------------------------------------------------
    | Routes for Email
    |--------------------------------------------------------------------------
    */
    Route::post('users/verify', [EmailController::class,'resend'])->name('resendEmail');
    Route::post('/send/forgotLink', [EmailController::class, 'mail']);
    /*
    |--------------------------------------------------------------------------
    | Routes for Bets
    |--------------------------------------------------------------------------
    */

    Route::post('joinBet', 'App\Http\Controllers\api\AuthController@joinBet');
    Route::post('addMoney', 'App\Http\Controllers\api\AuthController@addMoney');
    Route::get('timeTimer', 'App\Http\Controllers\api\AuthController@timeTimer');
    Route::get('ResetMoney', 'App\Http\Controllers\api\AuthController@ResetMoney');
    Route::get('bettingReport/{id}{round}', 'App\Http\Controllers\api\AuthController@bettingReport');
    Route::post('myBets', 'App\Http\Controllers\api\AuthController@myBets');
    Route::post('likevideo', 'App\Http\Controllers\api\AuthController@likevideo');
    Route::get('topWinner', 'App\Http\Controllers\api\AuthController@topWinner');
    Route::post('trendingBetsWithPagination', 'App\Http\Controllers\api\AuthController@trendingBetsWithPagination');
    Route::get('trendingBets', 'App\Http\Controllers\api\AuthController@trendingBets');
    Route::get('getUserbyId/{id}', 'App\Http\Controllers\api\AuthController@getUserbyId');
    Route::get('notifyUser/{id}{betid}', 'App\Http\Controllers\api\AuthController@notifyUser');
    Route::get('getComments/{id}', 'App\Http\Controllers\api\AuthController@getComments');
    Route::post('storeComment', 'App\Http\Controllers\api\AuthController@storeComment');
    Route::post('addMoneyApple', 'App\Http\Controllers\api\AuthController@addMoneyByApplePayment');
    Route::post('publishBetting', 'App\Http\Controllers\api\AuthController@publishBetting');
    Route::post('reportBet', 'App\Http\Controllers\api\AuthController@reportBet');
    Route::post('bettingList', 'App\Http\Controllers\api\AuthController@bettingList');
    Route::post('deleteComment', 'App\Http\Controllers\api\AuthController@deleteComment');
    Route::get('viewbet/{id}', 'App\Http\Controllers\api\AuthController@viewBet');
    Route::post('makeTrendingBet', 'App\Http\Controllers\api\AuthController@makeTrendingBet');
    Route::get('videoList/', 'App\Http\Controllers\api\AuthController@videoList');
    Route::get('retriveFcmToken/', 'App\Http\Controllers\api\AuthController@retriveFcmToken');
    Route::get('notificationList/', 'App\Http\Controllers\api\AuthController@notificationList');
    Route::get('readNotification/{id}', 'App\Http\Controllers\api\AuthController@readNotification');
    Route::get('discoverVideos', 'App\Http\Controllers\api\AuthController@discoverVideos');
    Route::get('filterList', 'App\Http\Controllers\api\AuthController@filterList');
    Route::post('buyFilter', 'App\Http\Controllers\api\AuthController@buyFilter');
    Route::get('videosList', 'App\Http\Controllers\api\AuthController@videosList');
    Route::get('videosListbyid/{id}', 'App\Http\Controllers\api\AuthController@videosListbyid');
    

});
