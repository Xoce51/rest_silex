<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\Common\Cache\ApcCache;
use \Doctrine\Common\Cache\ArrayCache;

$app = new Silex\Application();
$app['debug'] = true;

// Register Doctrine DBAL
$app->register(new Silex\Provider\DoctrineServiceProvider(),
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
// use session
$app->register(new Silex\Provider\SessionServiceProvider());

// authentification
$before = function(Request $request, Silex\Application $app)
{
	if (!isset($_SERVER['PHP_AUTH_USER']))
		{
			$response = new Response();
			$response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', 'site_login'));
			$response->setStatusCode(401, 'Please sign in.');
			return $response;
		}
	$username = $app->escape($app['request']->server->get('PHP_AUTH_USER', false));
	$password = $app->escape($app['request']->server->get('PHP_AUTH_PW'));
	$pwd = $app['db']->fetchAssoc('SELECT password, role FROM user WHERE email = :email', array(
		'email' => $username,
	));
	if (!empty($pwd) && sha1($password) === $pwd["password"])
		$app['session']->set('user', array('username' => $username, 'role' => $pwd['role']));
	else
		{
			$response = new Response();
			$response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', 'site_login'));
			$response->setStatusCode(401, 'Please sign in.');
			return $response;
		}
};
// get delete url
$app->delete('/users/{id}/', function ($id) use ($app) {
	$sql = "SELECT role FROM user WHERE id = :id";
	$role = $app['db']->fetchAssoc($sql, array('id' =>  $id));
	if ($role['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
		return $app->json(array('status' => 403, 'message' => '403 Forbidden'), 403);
	
	$message = $app['db']->delete('user', array(
		'id' => $id,
	));
	$error =  array('status' => 500, 'message' => 'Something went wrong');
	if ($message)
		return $app->json($message, 200);
	else
		return $app->json($error, 500);
})
->before($before);

$app->delete('/users/{id}', function ($id) use ($app) {
	$sql = "SELECT role FROM user WHERE id = :id";
	$role = $app['db']->fetchAssoc($sql, array('id' =>  $id));
	if ($role['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
		return $app->json(array('status' => 403, 'message' => '403 Forbidden'), 403);
	
	$message = $app['db']->delete('user', array(
		'id' => $id,
	));
	$error =  array('status' => 500, 'message' => 'Something went wrong');
	if ($message)
		return $app->json($message, 200);
	else
		return $app->json($error, 500);
})
->before($before);

# update url
$app->put('/users/{id}/', function ($id) use ($app) {
	$sql = "SELECT role FROM user WHERE id = :id";
	$role = $app['db']->fetchAssoc($sql, array('id' =>  $id));
	if ($role['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
		return $app->json(array('status' => 403, 'message' => '403 Forbidden'), 403);
	
	$values = $app['request']->request->all();

	$message = $app['db']->update('user',
		$values
		, array(
			'id'   => $id,
	));
	$error =  array('status' => 500, 'message' => 'Something went wrong');
	if ($message)
		return $app->json( array('status' => 200, 'message' => 'Update done'), 200);
	else
		return $app->json($error, 500);
})
->before($before);

# update url
$app->put('/users/{id}', function ($id) use ($app) {
	$sql = "SELECT role FROM user WHERE id = :id";
	$role = $app['db']->fetchAssoc($sql, array('id' =>  $id));
	if ($role['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
		return $app->json(array('status' => 403, 'message' => '403 Forbidden'), 403);
	
	$values = $app['request']->request->all();

	$message = $app['db']->update('user',
	$values
	, array(
	'id'   => $id,
	));
	$error =  array('status' => 500, 'message' => 'Something went wrong');
	if ($message)
	return $app->json( array('status' => 200, 'message' => 'Update done'), 200);
	else
	return $app->json($error, 500);
})
->before($before);

// get post url
$app->post('/users/', function (Request $request) use ($app) {
	$sql = "SELECT role FROM user WHERE id = :id";
	$role = $app['db']->fetchAssoc($sql, array('id' =>  $id));
	if ($role['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
		return $app->json(array('status' => 403, 'message' => '403 Forbidden'), 403);
	
	$data = array("lastname", "firstname", "email", "password", "role");
	$post = array();
	# populate data
	foreach($data as $d)
	$post[$d] = $request->get($d);

	# check if user exist
	$sql = "SELECT id FROM user WHERE lastname = ? AND firstname = ?";
	$save = $app['db']->fetchAssoc($sql, array($post['lastname'], $post['firstname']));
	if ($save)
	{
		$message =  array('status' => 401, 'message' => 'User already exist');
		return $app->json($message, 401);
	}

	# insert user
	$sql = "INSERT INTO user (lastname, firstname, email, password, role) VALUES (?, ?, ?, ?, ?)";
	$insert = $app['db']->executeQuery($sql, array($post['lastname'], $post['firstname'], $post['email'], sha1($post['password']),  $post['role']));

	$message =  array('status' => 200, 'message' => 'Create new user');
	//$message =  json_encode($insert);
	return $app->json($message, 200);
})
->before($before);

$app->post('/users', function (Request $request) use ($app) {
	$sql = "SELECT role FROM user WHERE id = :id";
	$role = $app['db']->fetchAssoc($sql, array('id' =>  $id));
	if ($role['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
		return $app->json(array('status' => 403, 'message' => '403 Forbidden'), 403);
	
	$data = array("lastname", "firstname", "email", "password", "role");
	$post = array();
	# populate data
	foreach($data as $d)
	$post[$d] = $request->get($d);

	# check if user exist
	$sql = "SELECT id FROM user WHERE lastname = ? AND firstname = ?";
	$save = $app['db']->fetchAssoc($sql, array($post['lastname'], $post['firstname']));
	if ($save)
	{
		$message =  array('status' => 401, 'message' => 'User already exist');
		return $app->json($message, 401);
	}

	# insert user
	$sql = "INSERT INTO user (lastname, firstname, email, password, role) VALUES (?, ?, ?, ?, ?)";
	$insert = $app['db']->executeQuery($sql, array($post['lastname'], $post['firstname'], $post['email'], sha1($post['password']),  $post['role']));

	$message =  array('status' => 200, 'message' => 'Create new user');
	//$message =  json_encode($insert);
	return $app->json($message, 200);
})
->before($before);

// get route
$app->get('/users/{id}/', function($id) use ($app) {
	$sql = "SELECT id, lastname, firstname, email, role FROM user WHERE id = ?";
	$post = $app['db']->fetchAssoc($sql, array((int)$id));

	if (!$post || empty($id))
	{
		$error = array('status' => 404, 'message' => 'Not found');
		return $app->json($error, 404);
	}
	else if ($post['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
	{
		$error = $error = array('status' => 401, 'message' => 'Not found');
		return $app->json($error, 401);
	}
	$post['id'] = (int) $post['id'];
	return $app->json($post);
})
->before($before);

$app->get('/user/{id}/', function($id) use ($app)
{
	$sql = "SELECT id, lastname, firstname, email, role FROM user WHERE id = ?";
	$post = $app['db']->fetchAssoc($sql, array((int)$id));

	if (!$post || empty($id))
	{
		$error = array('status' => 404, 'message' => 'not found');
		return $app->json($error, 404);
	}
	else if ($post['role'] == 'admin' && $app['session']->get('user')["role"] != 'admin')
	{
		$error = $error = array('status' => 401, 'message' => 'Not found');
		return $app->json($error, 401);
	}
	$post['id'] = (int) $post['id'];
	return $app->json($post);
})
->before($before);


// general route & error handler
$app->get('/', function() use ($app) {
	return $app->json('');
})
->before($before);

$app->error(function (\Exception $e, $code) use ($app) {
	switch ($code) {
		case 404:
		$error = array('status' => 404, 'message' => 'not found');
		break;
		case 500:
		$error = array('status' => 500, 'message' => 'internal error');
		break;
		default:
		$error = array('message' => $code.' '.$e->getMessage());
		break;
	}

	return $app->json($error);
});
$app->run();
?>
