<?php

declare(strict_types=1);

use Zend\Diactoros\ServerRequestFactory;

require_once __DIR__ . '/../vendor/autoload.php';

call_user_func(function (string $uri) {
    $dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
       $r->addRoute('POST', '/api/uniqueEmails', 'countUniqueEmails');
    });

    $request = ServerRequestFactory::fromGlobals();

    $application = new UniqueEmails\Application($dispatcher);

    $application->handleRequest($request, $uri);
}, $_SERVER['REQUEST_URI']);
