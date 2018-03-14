<?php

/**
 * Register affiliate routes
 */
App::before(function ($request) {
    /*
     * Extensibility
     */
    Event::fire('affiliates.beforeRoute');

    Route::any(config('affiliates.uri', '/affiliates'), 'Affiliates\Classes\Controller@run')->middleware('web');

    Route::group([
            'middleware' => ['web'],
            'prefix' => config('affiliates.uri', '/affiliates')
        ], function () {
            Route::any('{noauthslug}', 'Affiliates\Classes\Controller@run')->where('noauthslug', '^(signup|login)(.*)?');
            Route::any('{slug}', 'Affiliates\Classes\Controller@run')->where('slug', '(.*)?')->middleware('Affiliates\Classes\AuthMiddleware');
        });

    /*
     * Entry point
     */
    Route::any(config('affiliates.uri', '/affiliates'), 'Affiliates\Classes\Controller@run')->middleware('web');

    /*
     * Extensibility
     */
    Event::fire('affiliates.route');
});