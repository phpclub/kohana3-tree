<?php defined('SYSPATH') or die('No direct script access.');

//set up tree controller - common route for all operations
Route::set('tree', '<controller>/<action>(/<param1>)(/<param2>)')
	->defaults(array(
		'controller' => 'tree',
		'action'	 => 'index',
	));