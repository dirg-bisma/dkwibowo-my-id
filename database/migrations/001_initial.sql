PRAGMA foreign_keys = ON;

CREATE TABLE content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    project_type TEXT NOT NULL DEFAULT 'creative_work',
    title_id TEXT NOT NULL,
    title_en TEXT NOT NULL,
    file_path_id TEXT NOT NULL,
    file_path_en TEXT NOT NULL,
    cover_image TEXT NULL,
    status TEXT NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CHECK (project_type IN ('creative_work', 'software_application')),
    CHECK (status IN ('draft', 'published', 'archived', 'trashed'))
);

CREATE TABLE tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE content_tags (
    content_id INTEGER NOT NULL,
    tag_id INTEGER NOT NULL,
    PRIMARY KEY (content_id, tag_id),
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE redirects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content_id INTEGER NULL,
    old_path TEXT NOT NULL UNIQUE,
    new_path TEXT NOT NULL,
    status_code INTEGER NOT NULL DEFAULT 301,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    CHECK (status_code = 301),
    CHECK (is_active IN (0, 1)),
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE SET NULL
);

CREATE TABLE content_trash (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content_id INTEGER NOT NULL UNIQUE,
    original_status TEXT NOT NULL,
    original_slug TEXT NOT NULL,
    trashed_at DATETIME NOT NULL,
    CHECK (original_status IN ('draft', 'published', 'archived')),
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

CREATE INDEX idx_content_status ON content(status);
CREATE INDEX idx_content_published_at ON content(published_at);
CREATE INDEX idx_content_updated_at ON content(updated_at);
CREATE INDEX idx_content_tags_tag ON content_tags(tag_id);
CREATE INDEX idx_content_tags_content ON content_tags(content_id);
CREATE INDEX idx_redirects_active ON redirects(is_active);

CREATE VIRTUAL TABLE content_search USING fts5(
    title_id,
    title_en,
    tags,
    content_id UNINDEXED
);
