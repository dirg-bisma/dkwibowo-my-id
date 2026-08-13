<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ContentRepository;
use App\Repositories\TagRepository;
use App\Repositories\TrashRepository;
use App\Security\Auth;
use App\Security\Csrf;
use App\Services\ContentService;
use App\Services\MarkdownService;
use App\Services\MediaService;
use App\Services\TagService;
use App\Services\TrashService;
use App\Support\ConflictException;
use App\Support\Response;
use App\Support\ValidationException;
use App\Support\View;
use Throwable;

final class AdminController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly ContentRepository $content,
        private readonly TagRepository $tags,
        private readonly ContentService $contentService,
        private readonly MarkdownService $markdown,
        private readonly MediaService $media,
        private readonly TagService $tagService,
        private readonly TrashRepository $trash,
        private readonly TrashService $trashService,
        private readonly View $view,
    ) {
    }

    public function login(): never
    {
        if ($this->auth->isAuthenticated()) {
            Response::redirect('/admin');
        }
        Response::html($this->view->page('admin/login', [
            'title' => 'Admin Login',
            'loginUrl' => $this->auth->authorizationUrl(),
        ]));
    }

    public function callback(): never
    {
        try {
            $this->auth->authenticate((string) ($_GET['code'] ?? ''), $_GET['state'] ?? null);
            Response::redirect('/admin');
        } catch (Throwable) {
            Response::text('Authentication failed.', 401);
        }
    }

    public function logout(): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        $this->auth->logout();
        Response::redirect('/admin/login');
    }

    public function dashboard(): never
    {
        $this->auth->requireAdmin();
        Response::html($this->view->page('admin/dashboard', [
            'title' => 'Admin',
            'items' => $this->content->listAdmin(),
            'tags' => $this->tags->listAll(),
            'csrf' => $this->csrf->token(),
            'metaRobots' => 'noindex,nofollow',
        ]));
    }

    public function createForm(): never
    {
        $this->auth->requireAdmin();
        $this->form(null);
    }

    public function create(): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $id = $this->contentService->createDraft($_POST);
            Response::redirect('/admin/content/' . $id . '/edit');
        } catch (ConflictException|ValidationException $exception) {
            Response::text($exception->getMessage(), $exception instanceof ConflictException ? 409 : 422);
        }
    }

    public function editForm(int $id): never
    {
        $this->auth->requireAdmin();
        $content = $this->content->findById($id);
        if ($content === null) {
            $this->notFound();
        }
        $content['tags'] = implode(', ', $this->content->tagNames($id));
        try {
            $content['content_id'] = $this->markdown->read('id', $content['slug']);
            $content['content_en'] = $this->markdown->read('en', $content['slug']);
        } catch (Throwable) {
            $content['content_id'] = '';
            $content['content_en'] = '';
        }
        $this->form($content);
    }

    public function edit(int $id): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->contentService->update($id, $_POST);
            Response::redirect('/admin/content/' . $id . '/edit');
        } catch (ConflictException|ValidationException $exception) {
            Response::text($exception->getMessage(), $exception instanceof ConflictException ? 409 : 422);
        }
    }

    public function publish(int $id): never
    {
        $this->action($id, 'publish');
    }

    public function archive(int $id): never
    {
        $this->action($id, 'archive');
    }

    public function restoreDraft(int $id): never
    {
        $this->action($id, 'restoreAsDraft');
    }

    public function restorePublished(int $id): never
    {
        $this->action($id, 'restoreAsPublished');
    }

    public function createTag(): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->tagService->create((string) ($_POST['name'] ?? ''));
            Response::redirect('/admin');
        } catch (ConflictException|ValidationException $exception) {
            Response::text($exception->getMessage(), $exception instanceof ConflictException ? 409 : 422);
        }
    }

    public function deleteTag(int $id): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->tagService->delete($id);
            Response::redirect('/admin');
        } catch (ConflictException $exception) {
            Response::text($exception->getMessage(), 409);
        }
    }

    public function updateTag(int $id): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->tagService->update($id, (string) ($_POST['name'] ?? ''));
            Response::redirect('/admin');
        } catch (ConflictException|ValidationException $exception) {
            Response::text($exception->getMessage(), $exception instanceof ConflictException ? 409 : 422);
        }
    }

    public function uploadCover(): never
    {
        $this->upload('cover');
    }

    public function uploadInline(): never
    {
        $this->upload('inline');
    }

    public function previewMarkdown(): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            Response::json(['html' => $this->markdown->render((string) ($_POST['markdown'] ?? ''))]);
        } catch (Throwable) {
            Response::json(['error' => 'Unable to render preview.'], 422);
        }
    }

    public function trashList(): never
    {
        $this->auth->requireAdmin();
        Response::html($this->view->page('admin/trash', [
            'title' => 'Trash',
            'items' => $this->trash->listAll(),
            'csrf' => $this->csrf->token(),
            'metaRobots' => 'noindex,nofollow',
        ]));
    }

    public function moveToTrash(int $id): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->trashService->moveToTrash($id);
            Response::redirect('/admin');
        } catch (ConflictException|ValidationException $exception) {
            Response::text($exception->getMessage(), $exception instanceof ConflictException ? 409 : 422);
        }
    }

    public function restoreTrash(int $id): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->trashService->restore($id);
            Response::redirect('/admin');
        } catch (ConflictException|ValidationException $exception) {
            Response::text($exception->getMessage(), $exception instanceof ConflictException ? 409 : 422);
        }
    }

    public function permanentDelete(int $id): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->trashService->permanentDelete($id);
            Response::redirect('/admin/trash');
        } catch (ValidationException $exception) {
            Response::text($exception->getMessage(), 422);
        }
    }

    private function action(int $id, string $method): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $this->contentService->{$method}($id);
            Response::redirect('/admin/content/' . $id . '/edit');
        } catch (ValidationException $exception) {
            Response::text($exception->getMessage(), 422);
        }
    }

    private function upload(string $kind): never
    {
        $this->auth->requireAdmin();
        $this->csrf->assertValid($_POST['_csrf'] ?? null);
        try {
            $path = $kind === 'cover'
                ? $this->media->uploadCover($_FILES['file'] ?? [])
                : $this->media->uploadInline($_FILES['file'] ?? []);
            Response::json(['path' => $path]);
        } catch (ValidationException $exception) {
            Response::json(['error' => $exception->getMessage()], 422);
        }
    }

    private function form(?array $content): never
    {
        Response::html($this->view->page('admin/content-form', [
            'title' => $content === null ? 'Create Content' : 'Edit Content',
            'content' => $content ?? [
                'slug' => '',
                'project_type' => 'creative_work',
                'title_id' => '',
                'title_en' => '',
                'cover_image' => '',
                'tags' => '',
                'content_id' => '',
                'content_en' => '',
                'status' => 'draft',
            ],
            'csrf' => $this->csrf->token(),
            'metaRobots' => 'noindex,nofollow',
        ]));
    }

    private function notFound(): never
    {
        Response::text('Not found.', 404);
    }
}
