<?php

declare(strict_types=1);

use App\Support\ErrorHandler;
use App\Support\Response;
use App\Controllers\ContentController;
use App\Controllers\AdminController;
use App\Controllers\DiscoveryController;
use App\Controllers\PublicController;
use App\Controllers\SearchController;
use App\Controllers\TagController;
use App\Database\Connection;
use App\Repositories\RedirectRepository;
use App\Repositories\ContentRepository;
use App\Repositories\TagRepository;
use App\Repositories\TrashRepository;
use App\Security\Auth;
use App\Security\Csrf;
use App\Services\ContentService;
use App\Services\MediaService;
use App\Services\MarkdownService;
use App\Services\SearchService;
use App\Services\SeoService;
use App\Services\TagService;
use App\Services\TrashService;
use App\Support\View;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (is_file($autoload)) {
    require $autoload;
}

if (class_exists(Dotenv\Dotenv::class) && is_file($root . '/.env')) {
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
}

require_once $root . '/app/Support/Response.php';
require_once $root . '/app/Support/ErrorHandler.php';

$config = require $root . '/config/app.php';
date_default_timezone_set($config['timezone']);
ErrorHandler::register($config['env']);

session_set_cookie_params([
    'httponly' => true,
    'secure' => $config['env'] === 'production',
    'samesite' => 'Lax',
]);
session_start();

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($path, '/assets/')) {
    $relative = substr($path, strlen('/'));
    if (preg_match('/^[A-Za-z0-9._\/-]+\.(?:css|js|svg|webp|png|jpg|jpeg|gif)$/', $relative) !== 1
        || str_contains($relative, '..')) {
        Response::text('Not found.', 404);
    }
    $asset = $root . '/' . $relative;
    if (!is_file($asset)) {
        Response::text('Not found.', 404);
    }
    $types = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'text/javascript; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
    ];
    $extension = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
    header('Content-Type: ' . $types[$extension]);
    header('Cache-Control: public, max-age=86400');
    readfile($asset);
    exit;
}

if (str_starts_with($path, '/media/')) {
    $relative = substr($path, strlen('/media/'));
    if (preg_match('/^(?:cover|inline)\/[A-Za-z0-9._-]+\.(?:webp|png|jpg|jpeg|gif)$/', $relative) !== 1) {
        Response::text('Not found.', 404);
    }
    $media = $root . '/storage/media/' . $relative;
    if (!is_file($media)) {
        Response::text('Not found.', 404);
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($media);
    if (!is_string($mime) || !str_starts_with($mime, 'image/')) {
        Response::text('Not found.', 404);
    }
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($media);
    exit;
}

if ($path === '/health') {
    Response::text('ok');
}

$dbConfig = require $root . '/config/database.php';
$db = Connection::open($dbConfig['path']);
$content = new ContentRepository($db);
$redirectRepository = new RedirectRepository($db);
$redirect = $redirectRepository->findActive($path);
if ($redirect !== null) {
    Response::redirect($redirect['new_path'], (int) $redirect['status_code']);
}
$view = new View($root);
$markdown = new MarkdownService($root);
$seo = new SeoService($config['url'], $markdown);
$tagRepository = new TagRepository($db);
$public = new PublicController($content, $view, $config['default_per_page'], $config['max_per_page'], $seo);
$contentController = new ContentController($content, $markdown, $seo, $view);
$tag = new TagController($content, $tagRepository, $view, $config['default_per_page'], $config['max_per_page'], $seo);
$search = new SearchController(new SearchService($content), $view, $config['default_per_page'], $config['max_per_page']);
$tagService = new TagService($tagRepository);
$media = new MediaService($root, $config['upload_max_size']);
$trashRepository = new TrashRepository($db);
$trashService = new TrashService($db, $root, $content, $trashRepository, $redirectRepository);
$admin = new AdminController(
    new Auth($config),
    new Csrf(),
    $content,
    $tagRepository,
    new ContentService($db, $root, $content, $redirectRepository, $tagService),
    $markdown,
    $media,
    $tagService,
    $trashRepository,
    $trashService,
    $view,
);
$discovery = new DiscoveryController($config['url'], $content, $tagRepository);

$router = new Bramus\Router\Router();
$router->get('/', static fn (): never => Response::redirect('/id'));
$router->get('/sitemap.xml', static fn (): never => $discovery->sitemap());
$router->get('/robots.txt', static fn (): never => $discovery->robots());
$router->get('/(id|en)', static function (string $language) use ($public, $search): never {
    $query = trim((string) ($_GET['q'] ?? ''));
    $tag = trim((string) ($_GET['tag'] ?? '')) ?: null;
    $page = max(1, (int) ($_GET['page'] ?? 1));
    if ($query !== '') {
        $search->show($language, $query, $tag, $page);
    }
    $public->index($language, $tag, $page);
});
$router->get('/(id|en)/project', static fn (string $language): never => $public->projectArchive($language, max(1, (int) ($_GET['page'] ?? 1))));
$router->get('/(id|en)/content/([^/]+)', static fn (string $language, string $slug): never => $contentController->show($language, $slug));
$router->get('/(id|en)/tag/([^/]+)', static fn (string $language, string $slug): never => $tag->show($language, $slug, max(1, (int) ($_GET['page'] ?? 1))));
$router->get('/admin/login', static fn (): never => $admin->login());
$router->get('/admin/oauth/callback', static fn (): never => $admin->callback());
$router->post('/admin/logout', static fn (): never => $admin->logout());
$router->get('/admin', static fn (): never => $admin->dashboard());
$router->get('/admin/trash', static fn (): never => $admin->trashList());
$router->get('/admin/content/create', static fn (): never => $admin->createForm());
$router->post('/admin/content/create', static fn (): never => $admin->create());
$router->get('/admin/content/(\d+)/edit', static fn (string $id): never => $admin->editForm((int) $id));
$router->post('/admin/content/(\d+)/edit', static fn (string $id): never => $admin->edit((int) $id));
$router->post('/admin/content/(\d+)/publish', static fn (string $id): never => $admin->publish((int) $id));
$router->post('/admin/content/(\d+)/archive', static fn (string $id): never => $admin->archive((int) $id));
$router->post('/admin/content/(\d+)/restore-draft', static fn (string $id): never => $admin->restoreDraft((int) $id));
$router->post('/admin/content/(\d+)/restore-published', static fn (string $id): never => $admin->restorePublished((int) $id));
$router->post('/admin/content/(\d+)/trash', static fn (string $id): never => $admin->moveToTrash((int) $id));
$router->post('/admin/trash/(\d+)/restore', static fn (string $id): never => $admin->restoreTrash((int) $id));
$router->post('/admin/trash/(\d+)/delete', static fn (string $id): never => $admin->permanentDelete((int) $id));
$router->post('/admin/tags/create', static fn (): never => $admin->createTag());
$router->post('/admin/tags/(\d+)/edit', static fn (string $id): never => $admin->updateTag((int) $id));
$router->post('/admin/tags/(\d+)/delete', static fn (string $id): never => $admin->deleteTag((int) $id));
$router->post('/admin/upload/cover', static fn (): never => $admin->uploadCover());
$router->post('/admin/upload/inline', static fn (): never => $admin->uploadInline());
$router->set404(static function () use ($view): never {
    Response::html($view->page('errors/404', ['title' => '404']), 404);
});
$router->run();
