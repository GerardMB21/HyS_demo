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

// Route::get('/', 'Backend\BillingController@test')->name('home');

Route::get('/iniciar-sesion', 'Backend\UserController@index')->name('login');
Route::post('/iniciar-sesion/auth', 'Backend\UserController@login')->name('login.auth');
Route::get('/cerrar-sesion', 'Backend\UserController@logout')->name('logout');

Route::middleware(['auth'])->namespace('Backend')->group(function() {

	/** Article */
	
	Route::get('/', 'ArticleController@index')->name('dashboard.article');
	Route::post('/list-articles', 'ArticleController@list')->name('dashboard.article.list');
	Route::post('/update-article', 'ArticleController@updateArticle')->name('dashboard.article.update_article');
	Route::post('/delete-article', 'ArticleController@deleteArticle')->name('dashboard.article.delete_article');

	Route::get('/graphics', 'GraphicsController@index')->name('dashboard.graphics');
	Route::post('/graphics/get-articles', 'GraphicsController@getArticles')->name('dashboard.graphics.get_articles');
});
