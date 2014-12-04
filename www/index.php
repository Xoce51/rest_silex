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

$app->get('/users/{id}/', function($id) use ($app) {
	if (!$post || empty($id))
		{
			$error = array('status' => 404, 'message' => 'Not found');
			return $app->json($error, 404);
		}
	else if ($post['role'] == 'admin')
		{
			$error = $error = array('status' => 401, 'message' => 'Not found');
			return $app->json($error, 401);
		}
	$sql = "SELECT id, lastname, firstname, email, role FROM user WHERE id = ?";
	$post = $app['db']->fetchAssoc($sql, array((int)$id));
	return $app->json($post);
});

$app->get('/user/{id}/', function($id) use ($app) {

	if (!$post || empty($id))
		{
			$error = array('status' => 404, 'message' => 'not found');
			return $app->json($error, 404);
		}
	else if ($post['role'] == 'admin')
		{
			$error = $error = array('status' => 401, 'message' => 'Not found');
			return $app->json($error, 401);
		}
	$sql = "SELECT id, lastname, firstname, email, role FROM user WHERE id = ?";
	$post = $app['db']->fetchAssoc($sql, array((int)$id));
	return $app->json($post);
});

$app->get('/', function() use ($app, $request) {
	return $app->json('');
});

$app->error(function (\Exception $e, $code) use ($app) {
	switch ($code) {
	  case 404:
      $error = array('status' => 404, 'message' => 'not found');
      break;
		case 500:
      $error = array('status' => 500, 'message' => 'internal error');
      break;
    default:
      $error = array('message' => 'We are sorry, but something went terribly wrong.');
			break;
  }

    return $app->json($error);
});

$app->run();
?>