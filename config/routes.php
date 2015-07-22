<?php
use Cake\Routing\Router;

Router::plugin('CreatorModifier', function ($routes) {
	$routes->fallbacks('InflectedRoute');
});
