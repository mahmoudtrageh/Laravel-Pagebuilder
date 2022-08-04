<?php

use Illuminate\Support\Facades\Route;
use HansSchouten\LaravelPageBuilder\LaravelPageBuilder;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\VerifyCsrfToken;


if(env('INSTALLATION', false) == true){


// handle pagebuilder asset requests
Route::any( config('pagebuilder.general.assets_url') . '{any}', function() {

    $builder = new LaravelPageBuilder(config('pagebuilder'));
    $builder->handlePageBuilderAssetRequest();

})->where('any', '.*');


// handle requests to retrieve uploaded file
Route::any( config('pagebuilder.general.uploads_url') . '{any}', function() {

    $builder = new LaravelPageBuilder(config('pagebuilder'));
    $builder->handleUploadedFileRequest();

})->where('any', '.*');


if (config('pagebuilder.website_manager.use_website_manager')) {
    
    // handle all website manager requests
    Route::any( config('pagebuilder.website_manager.url') . '{any}', function() {
        
        $builder = new LaravelPageBuilder(config('pagebuilder'));
        $builder->handleRequest();

    })->where('any', '.*');

} 

Route::group(['middleware' => ['web', 'auth']], function(){

if ( config('pagebuilder.pagebuilder.use_pagebuilder') ) {
    // handle all website manager requests
    Route::any( config('pagebuilder.pagebuilder.url') . '{any}', function() {
       
       $builder = new LaravelPageBuilder(config('pagebuilder'));
      
       $builder->handleRequest();

   })->where('any', '.*')->withoutMiddleware([VerifyCsrfToken::class]);
}

});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
->middleware('guest')
->name('login');

if (config('pagebuilder.router.use_router')) {

    $pages = DB::table('pagebuilder__pages')->pluck('name')->toArray();
    if(isset(explode('/', URL::current(), PHP_URL_PATH)[3]) && explode('/', URL::current(), PHP_URL_PATH)[3] != '') {
    if(in_array(explode('/', URL::current(), PHP_URL_PATH)[3], $pages)) {
    // pass all remaining requests to the LaravelPageBuilder router
    Route::any( '/{any}', function() {
        
        $builder = new LaravelPageBuilder(config('pagebuilder'));
        $hasPageReturned = $builder->handlePublicRequest();

    })->where('any', '.*');

    }
}
}

} else {
            Route::namespace('Admin')->group(function () {
                // Global routes
                Route::get('database/step0', 'HomeController@step0');
                Route::get('database/step1', 'HomeController@step1')->name('step1');
                Route::get('database/step2', 'HomeController@step2')->name('step2');
                Route::get('database/step3/{error?}', 'HomeController@step3')->name('step3');
                Route::get('database/step4', 'HomeController@step4')->name('step4');
                Route::get('database/step5', 'HomeController@step5')->name('step5');

                Route::post('database/database_installation', 'HomeController@database_installation')->name('install.db');
                Route::get('import_sql', 'HomeController@import_sql')->name('import_sql');
                Route::post('system_settings', 'HomeController@system_settings')->name('system_settings');
                Route::post('purchase_code', 'HomeController@purchase_code')->name('purchase.code');
            });
}