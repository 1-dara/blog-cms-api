![Tests](https://github.com/1-dara/blog-cms-api/actions/workflows/tests.yml/badge.svg)

# Blog/CMS API (Laravel)

A RESTful blog/content management API built with Laravel and Eloquent ORM, featuring categories, tags (many-to-many), comments, pagination, and ownership-based authorization.

- **API URL:** - https://blog-cms-api-j8mx.onrender.com 

## Features

- Full CRUD for posts, categories, tags, and comments
- Many-to-many relationship between posts and tags
- Nested comments under posts (`/posts/{id}/comments`)
- Public read access, authenticated write access
- Ownership-based authorization — users can only edit/delete their own posts and comments
- Auto-generated URL-friendly slugs for posts, categories, and tags
- Paginated post listings
- SQLite for local development, PostgreSQL in production

## Tech Stack

- **PHP** 8.2
- **Laravel** 12
- **Eloquent ORM**
- **PostgreSQL** (production) / **SQLite** (local)
- **Laravel Sanctum** (API authentication)

## Database Schema

**User** — id, name, email, password
**Category** — id, name, slug
**Tag** — id, name, slug
**Post** — id, title, slug, body, published, user_id (FK), category_id (FK, nullable)
**Comment** — id, body, user_id (FK), post_id (FK)
**post_tag** (pivot) — post_id, tag_id

Relationships:
- A `User` has many `Post`s and `Comment`s
- A `Post` belongs to a `User` and a `Category`, has many `Comment`s, and belongs to many `Tag`s
- A `Tag` belongs to many `Post`s

## API Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | — | Register a new user |
| POST | `/api/login` | — | Log in |
| POST | `/api/logout` | Required | Log out |
| GET | `/api/categories` | — | List categories |
| GET | `/api/categories/{id}` | — | Show category with posts |
| POST | `/api/categories` | Required | Create category |
| PUT | `/api/categories/{id}` | Required | Update category |
| DELETE | `/api/categories/{id}` | Required | Delete category |
| GET | `/api/tags` | — | List tags |
| GET | `/api/tags/{id}` | — | Show tag with posts |
| POST | `/api/tags` | Required | Create tag |
| PUT | `/api/tags/{id}` | Required | Update tag |
| DELETE | `/api/tags/{id}` | Required | Delete tag |
| GET | `/api/posts` | — | List published posts (paginated) |
| GET | `/api/posts/{id}` | — | Show post with author, category, tags, comments |
| POST | `/api/posts` | Required | Create post (author auto-set) |
| PUT | `/api/posts/{id}` | Required + Owner | Update own post |
| DELETE | `/api/posts/{id}` | Required + Owner | Delete own post |
| POST | `/api/posts/{postId}/comments` | Required | Add comment to a post |
| DELETE | `/api/comments/{id}` | Required + Owner | Delete own comment |

### Example: Create a post with tags

```bash
curl -X POST https://blog-cms-api-j8mx.onrender.com/api/posts \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"title": "My Post", "body": "Content here", "published": true, "category_id": 1, "tags": [1, 2]}'
```

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer

### Installation

```bash
git clone https://github.com/1-dara/blog-cms-api.git
cd blog-cms-api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`.

## Live Demo

- **API URL:** https://blog-cms-api-j8mx.onrender.com

Note: hosted on Render's free tier — the first request after inactivity may take 30-60 seconds while the server spins up.

## Roadmap

- [ ] Swagger/OpenAPI interactive documentation
- [ ] Search/filter posts by category or tag
- [ ] Rich text or Markdown support for post bodies

## About

Built as a second Laravel project, extending on an earlier task manager API by introducing many-to-many relationships, nested resources, and pagination.