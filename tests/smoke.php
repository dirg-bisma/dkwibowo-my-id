<?php

declare(strict_types=1);

use App\Support\Slug;

require dirname(__DIR__) . '/vendor/autoload.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

Slug::assertValid('nora-pos');
check(Slug::isValid('erp-system'), 'valid slug rejected');
check(!Slug::isValid('NoraPOS'), 'uppercase slug accepted');
check(!Slug::isValid('../escape'), 'path traversal slug accepted');
check(!Slug::isValid('admin'), 'reserved slug accepted');

$db = new SQLite3(':memory:');
$db->enableExceptions(true);
$db->exec('PRAGMA foreign_keys = ON');
$db->exec(file_get_contents(dirname(__DIR__) . '/database/migrations/001_initial.sql'));

$db->exec(<<<'SQL'
INSERT INTO content (
    slug, project_type, title_id, title_en, file_path_id, file_path_en,
    status, created_at, updated_at
) VALUES (
    'demo', 'creative_work', 'Demo ID', 'Demo EN',
    'content/id/demo.md', 'content/en/demo.md',
    'draft', 'now', 'now'
)
SQL);

check($db->querySingle("SELECT status FROM content WHERE slug = 'demo'") === 'draft', 'draft default failed');
check($db->querySingle("SELECT project_type FROM content WHERE slug = 'demo'") === 'creative_work', 'project type default failed');

$db->exec("INSERT INTO content_trash (content_id, original_status, original_slug, trashed_at) VALUES (1, 'draft', 'demo', 'now')");
$db->exec("UPDATE content SET status = 'trashed' WHERE id = 1");
check($db->querySingle("SELECT COUNT(*) FROM content WHERE status = 'published'") === 0, 'trashed content is public');

echo "Smoke tests passed.\n";
