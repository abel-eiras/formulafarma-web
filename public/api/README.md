# Sistema de Comentarios y Valoraciones

## Descripción

Sistema completo de comentarios y valoraciones (0-5 con cápsulas) para el blog, implementado con PHP + SQLite.

## Estructura

- `db.php` - Conexión a SQLite y funciones helper
- `utils.php` - Utilidades compartidas (rate limiting, validación)
- `comments.php` - API endpoint para comentarios (GET, POST)
- `ratings.php` - API endpoint para valoraciones (GET, POST)
- `init-db.php` - Script de inicialización (opcional, se ejecuta automáticamente)
- `schema.sql` - Esquema de base de datos (referencia)
- `comments.db` - Base de datos SQLite (se crea automáticamente)

## Inicialización

La base de datos se inicializa automáticamente la primera vez que se accede a cualquier endpoint.

Si necesitas inicializarla manualmente, puedes acceder a:
```
https://formulafarma.com/api/init-db.php
```

## Permisos del Servidor

Asegúrate de que el directorio `api/` tenga permisos de escritura (755 o 775) para que SQLite pueda crear y escribir en `comments.db`.

## Endpoints

### Comentarios

**GET `/api/comments.php?post=slug-del-post`**
- Retorna lista de comentarios aprobados para un post

**POST `/api/comments.php`**
- Body JSON: `{ post_slug, author_name, author_email, content }`
- Rate limit: 3 comentarios por IP en 1 hora

### Valoraciones

**GET `/api/ratings.php?post=slug-del-post`**
- Retorna estadísticas: `{ average, count, user_rating }`

**POST `/api/ratings.php`**
- Body JSON: `{ post_slug, rating }` (rating: 0-5)
- Una valoración por IP por post (se puede actualizar)

## Seguridad

- Rate limiting implementado
- Sanitización de inputs (XSS protection)
- Validación de datos
- Protección básica contra spam

## Backup

Se recomienda hacer backup periódico del archivo `comments.db`.

