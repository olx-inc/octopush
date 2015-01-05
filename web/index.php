<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Models', __DIR__ . '/../src/');
$loader->add('Providers', __DIR__ . '/../src/');
$loader->add('Controllers', __DIR__ . '/../src/');
$loader->add('Services', __DIR__ . '/../src/');
$loader->add('Library', __DIR__ . '/../src/');
$loader->add('Helpers', __DIR__ . '/../src/');
require_once __DIR__ . '/../src/bootstrap.php';

if (isset($app['config']['timezone']) && $app['config']['timezone'] != '') {
    date_default_timezone_set($app['config']['timezone']);
}

$app->get('/run', "queue.controller:processJob");
$app->get('/', "queue.controller:index");
$app->get('/versions', "queue.controller:versions");
$app->get('/pause', "queue.controller:pause");
$app->get('/resume', "queue.controller:resume");
$app->get('/health', "queue.controller:health");
$app->get('/status', "queue.controller:status");

$app->get('/deploying', "jobs.controller:deploying");//DEPRECATED
$app->get('/{env}/deployed', "jobs.controller:deployed");
$app->get('/{env}/queued', "jobs.controller:queued");
$app->get('/{env}/inprogress', "jobs.controller:inprogress");
$app->get('/all', "jobs.controller:all");
$app->get('/mycomponents/{state}', "jobs.controller:my_components");
$app->get('/components', "jobs.controller:getComponentList");

$app->get('/jobs/{jobId}/golive', "jobs.controller:goLive");
$app->get('/jobs/{jobId}/rollback', "jobs.controller:rollback");
$app->get('/jobs/{jobId}/cancel', "jobs.controller:cancel");

// deprecated
$app->get('/jobs/{jobId}/tests/{success}', "jobs.controller:registerTestResult");
$app->get('/environments/{env}/modules/{module}/versions/{version}/push', "queue.controller:queueJob");
$app->get('/status/{jobId}', "jobs.controller:getJobStatus");

// new API
$app->post('/jobs/create', "jobs.controller:createJob");
$app->post('/jobs/{jobId}/register_test_job_result', "jobs.controller:registerTestJobResult");
$app->post('/jobs/{jobId}/register_test_job_url', "jobs.controller:registerTestJobUrl");
$app->get('/jobs/{jobId}/status', "jobs.controller:getJobStatus");

$app->get('/versions/all', "version.controller:getAllVersions");
$app->get('/environments/{env}/modules/{module}/versions/{version}/update', "version.controller:update");


$app->before(function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $token = $app['security']->getToken();
    $userDataInSession = $app['session']->get('userData');

    if (is_null($userDataInSession)) {
        
        if ($token && ! $app['security.trust_resolver']->isAnonymous($token)) {
            Helpers\Session::buildSession($app, $token);
        }
    }
});

$app->get('/login', function () use ($app) {
    $url = "/auth/GitHub?_csrf_token=" . $app['form.csrf_provider']->generateCsrfToken('oauth');

    return $app->redirect($url);
});

$app->run();
