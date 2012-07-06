<?php

/*
|--------------------------------------------------------------------------
| Installer
|--------------------------------------------------------------------------
|
| Run installation route when Orchestra is not installed yet.
 */
Route::any('(:bundle)/installer/?(:any)?/?(:num)?', function ($action = 'index', $steps = 0) 
{
	// we should disable this routing when the system 
	// detect it's already running/installed.
	if (Orchestra\Installer::installed() and (!($action === 'steps' && intval($steps) === 2))) return Response::error('404');

	// Otherwise, install it right away.
	return Controller::call("orchestra::installer@{$action}", array($steps));
});

/*
|--------------------------------------------------------------------------
| Default Routing
|--------------------------------------------------------------------------
 */
Route::any('(:bundle)', array('before' => 'orchestra::installed|orchestra::auth', function ()
{
	// we should run installer when the system 
	// detect it's already running/installed.
	if ( ! Orchestra\Installer::installed()) return Redirect::to_action("orchestra::installer@index");

	// Display the dashboard
	return Controller::call('orchestra::dashboard@index');
}));

/*
|--------------------------------------------------------------------------
| Credential Routing
|--------------------------------------------------------------------------
 */
Route::any('(:bundle)/(login|register|logout)', function ($action)
{
	return Controller::call("orchestra::credential@{$action}");
});

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
|
| Detects all controller under Orchestra bundle and register it to routing
 */
Route::controller(array('orchestra::account', 'orchestra::dashboard', 'orchestra::credential', 'orchestra::users'));

/*
|--------------------------------------------------------------------------
| Route Filtering
|--------------------------------------------------------------------------
|
 */
Route::filter('orchestra::auth', function ()
{
	// Redirect the user to login page if he/she is not logged in.
	if (Auth::guest()) return Redirect::to('orchestra/login');
});

Route::filter('orchestra::installed', function ()
{
	// we should run installer when the system 
	// detect it's already running/installed.
	if ( ! Orchestra\Installer::installed()) return Redirect::to_action("orchestra::installer@index");
});
