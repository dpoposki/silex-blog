<?php

/*
 * (c) Darko Poposki <darko.poposki@sitewards.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Poposki\Blog\Persistence\PdoPostRepository;
use Poposki\Blog\Service\CreatePostRequest;
use Poposki\Blog\Service\CreatePostService;
use Poposki\Blog\Service\ViewPostRequest;
use Poposki\Blog\Service\ViewPostService;
use Poposki\Blog\Service\ViewPostsService;
use Poposki\Blog\Domain\PostDoesNotExistException;

// Get all posts
$app->get('/blog', function () use ($app) {
    $repository = new PdoPostRepository($app['pdo']);

    $service = new ViewPostsService($repository);
    $posts   = $service->execute();

    return $app['twig']->render('blog/collection.html.twig', [
        'posts' => $posts,
    ]);
})->bind('blog');

// View post
$app->get('/blog/view/{id}', function ($id) use ($app) {
    $repository = new PdoPostRepository($app['pdo']);

    $service = new ViewPostService($repository);

    try {
        $post = $service->execute(new ViewPostRequest($id));
    } catch (PostDoesNotExistException $exception) {
        // error 404
        return;
    }

    return $app['twig']->render('blog/view.html.twig', [
        'post' => $post,
    ]);
})->bind('blog.view');

// Display create form
$app->get('/blog/create', function () use ($app) {
    return $app['twig']->render('blog/create.html.twig');
});

// Create post
$app->post('/blog/create', function () use ($app) {
    $requestStack = $app['request_stack']->getCurrentRequest();

    $title   = $requestStack->request->get('title');
    $content = $requestStack->request->get('content');

    $request    = new CreatePostRequest($title, $content);
    $repository = new PdoPostRepository($app['pdo']);

    $service = new CreatePostService($repository);
    $service->execute($request);

    return $app->redirect($app['url_generator']->generate('blog'));
});
