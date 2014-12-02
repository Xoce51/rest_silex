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
			'user' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'dbbame' => 'tcm_rest'
		)
	)
);

$app -> get('/user/{id}', function($id) use ($app) {
	$sql = "SELECT * FROM user WHERE id = ?";
	$post = $app['db']->fetchAssoc($sql, array((int)$id));

	return print_r($sql);
	//"<h1>{$post['title']}</h1>".
	//"<p>{$post['body']}</p>";
});

$app -> get('/', function() use ($app, $request) {

	//$article = new Entities\User();
	//$app['db.orm.em']->persist($article);
	//$app['db.orm.em']->flush();

	return '';
});

$app -> run();
?>