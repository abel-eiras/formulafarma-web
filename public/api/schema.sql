-- Esquema de base de datos para sistema de comentarios y valoraciones
-- Este archivo es solo de referencia. La base de datos se crea automáticamente mediante init-db.php

-- Tabla de comentarios
CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_slug TEXT NOT NULL,
    author_name TEXT NOT NULL,
    author_email TEXT,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address TEXT,
    approved INTEGER DEFAULT 1
);

-- Tabla de valoraciones de posts
CREATE TABLE IF NOT EXISTS ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_slug TEXT NOT NULL,
    rating INTEGER NOT NULL CHECK(rating >= 0 AND rating <= 5),
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(post_slug, ip_address)
);

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_comments_post ON comments(post_slug);
CREATE INDEX IF NOT EXISTS idx_ratings_post ON ratings(post_slug);
CREATE INDEX IF NOT EXISTS idx_comments_created ON comments(created_at DESC);

