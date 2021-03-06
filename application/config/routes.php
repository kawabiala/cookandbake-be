<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
//$route['default_controller'] = 'welcome';
$route['default_controller'] = 'pages';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
| -------------------------------------------------------------------------
| MY ROUTES
| -------------------------------------------------------------------------
*/
$route['api/recipe/put'] = 'recipe/insert';
$route['api/recipe']['PUT'] = 'recipe/insert';
$route['api/recipe']['POST'] = 'recipe/update';
$route['api/recipe/(:num)']['DELETE'] = 'recipe/delete/$1';
$route['api/recipe/(:num)']['GET'] = 'recipe/get/$1';
$route['api/recipe']['GET'] = 'recipe/get_all';

$route['api/ingredient']['PUT'] = 'ingredient/insert';
$route['api/ingredient']['POST'] = 'ingredient/update';
$route['api/ingredient/(:num)']['DELETE'] = 'ingredient/delete/$1';
$route['api/ingredient/(:num)']['GET'] = 'ingredient/get/$1';
$route['api/recipe/(:num)/ingredient']['GET'] = 'ingredient/get_all/$1';
$route['api/ingredient']['GET'] = 'ingredient/get_all';

$route['api/files']['PUT'] = 'files/insert';
$route['api/files']['POST'] = 'files/update';
$route['api/files/(:num)']|'DELETE'] = 'files/delete/$1';
$route['api/files/(:any)']['GET'] = 'files/get/$1';
$route['api/recipe/(:num)/files']['GET'] = 'files/get_all/$1';
$route['api/files']['GET'] = 'file/get_all';

$route['api/files/save']['POST'] = 'file/save';
$route['api/files/change']['POST'] = 'file/change';

$route['api/(:any)'] = 'api/view/$1';

$route['auth/login'] = 'auth/loginWithPassword';
$route['auth/logout'] = 'auth/logout';
$route['auth/refresh'] = 'auth/loginWithRefreshToken';
$route['auth/register'] = 'auth/register';
$route['auth/change_password'] = 'auth/changePassword';
$route['auth/lost_password'] = 'auth/lostPassword';
$route['auth/new_password'] = 'auth/newPassword';
$route['auth/confirm_registration'] = 'auth/confirmRegistration';

$route['(:any)'] = 'pages/view/$1';
$route['([a-zA-Z]{2,8})/(:any)'] = 'pages/view/$2/$1';
$route['([a-zA-Z]{2,8})[_|-]([a-zA-Z]{2}|[0-9]{3})/(:any)'] = 'pages/view/$3/$1/$2';
//$route['([a-zA-Z]{2,8}\_[a-zA-Z]{2})/(:any)'] = 'pages/view/$2/$1';
//$route['([a-zA-Z]{2,8}_[0-9]{3})/(:any)'] = 'pages/view/$2/$1';