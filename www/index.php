<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Response;
use \Doctrine\Common\Cache\ApcCache;
use \Doctrine\Common\Cache\ArrayCache;

$app = new Silex\Application();
$app['debug'] = true;

// Register Doctrine DBAL
$app -> register(new Silex\Provider\DoctrineServiceProvider(),
	array(
		'db.options' => array(
			'driver' => 'pdo_mysql',
			'host' => '127.0.0.1',
			'port' => null,
			'user' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'dbname' => 'tcm_rest'
		)
	)
);

$app -> get('/user/{id}', function($id) use ($app) {
	$sql = "SELECT id, lastname, firstname, email, role FROM user WHERE id = ?";
	$post = $app['db']->fetchAssoc($sql, array((int)$id));
	if (!$post)
		{
			$error = array('message' => 'The user was not found.');
			return $app->json($error, 404);
		}
	return $app->json($post);
});

$app -> get('/', function() use ($app, $request) {

	return '';
});

$app -> run();
?>