<?php
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Models', __DIR__ .'/../src/');
$loader->add('Providers', __DIR__ .'/../src/');
$loader->add('Controllers', __DIR__ .'/../src/');
$loader->add('Services', __DIR__ .'/../src/');
$loader->add('Library', __DIR__ .'/../src/');
require_once __DIR__.'/../src/bootstrap.php';

$app->get('/run', "queue.controller:processJob");
$app->get('/', "queue.controller:showJobs");
$app->get('/pause', "queue.controller:pause");
$app->get('/resume', "queue.controller:resume");
$app->get('/health', "queue.controller:health");

$app->get('/jobs/{jobId}/golive', "jobs.controller:goLive");

// deprecated
$app->get('/jobs/{jobId}/tests/{success}', "jobs.controller:registerTestResult");
$app->get('/environments/{env}/modules/{module}/versions/{version}/push', "queue.controller:queueJob");
$app->get('/status/{jobId}', "jobs.controller:getJobStatus");

// new API
$app->post('/jobs/create', "jobs.controller:createJob");
$app->post('/jobs/{jobId}/register_test_job_result', "jobs.controller:registerTestJobResult");
$app->post('/jobs/{jobId}/register_test_job_url', "jobs.controller:registerTestJobUrl");
$app->get('/jobs/{jobId}/status', "jobs.controller:getJobStatus");
$app->get('/jobs/{jobId}/cancel', "jobs.controller:cancel");

$app->before(function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $token = $app['security']->getToken();
    $app['user'] = null;
    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
        $app['user'] = $token->getUser();
        $app['is_admin_user'] = $app['services.GitHub']->IsUserAdmin($token);
    }
});

$app->get('/login', function () use ($app) {
    $url = "/auth/GitHub?_csrf_token=" . $app['form.csrf_provider']->generateCsrfToken('oauth');

    return $app->redirect($url);
});

$app->run();
