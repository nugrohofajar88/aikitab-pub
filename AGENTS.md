# AGENTS.md — KitabAI Public

Context file for AI coding agents working on this repo. Generic, tool-agnostic — update it whenever architecture or known gotchas change.

## What this is

The public-facing half of KitabAI. **This app does no AI processing and dispatches no queue jobs of any kind.** It exists purely to (1) display kitab content that's already been fully processed elsewhere, and (2) let visitors upload a PDF as a "minta kitab" (request a book) submission that someone else picks up and processes.

The actual processing (PDF upload, text extraction, Gemini/OpenRouter harakat+translation+grammar) happens in a **separate sibling Laravel project, the "local" producer app**, normally at `c:\xampp\htdocs\ai-kitab` (own DB `kitabai`, own `AGENTS.md` — read that for the full pipeline). That app pushes finished books here and pulls pending requests from here. This app never talks to Gemini/OpenRouter and never needs `php artisan queue:work` running — that's the entire point of splitting it out (see "Deployment" below).

## Stack

- Laravel 13, PHP 8.4 (Herd)
- MySQL, DB `kitabai_public` on `127.0.0.1:3306`, user `root`, no password (local dev)
- Blade + Tailwind v4 + Alpine.js — same visual stack as the local app, viewer Blade/JS was ported from there and trimmed

## Data model (deliberately thinner than the local app's)

- `books`: `source_local_id` (unique — the producer app's own book ID, used to upsert instead of duplicating on re-publish), `title`, `author`, `total_pages`, `published_at`. No `status`/`error_message`/`extraction_method` — only ever receives finished content.
- `pages`: just `book_id` + `page_number`.
- `paragraphs`: `harakat_text`, `content_json` (same `{arabic, translation, words[{arabic,translation,grammar}]}` per-sentence shape the producer app uses) — no `raw_text`/`status`, every row here is by definition "done."
- `book_requests`: the visitor-submitted "minta kitab" queue. `uuid` (public tracking token, visitor's status-page URL is `/request/{uuid}`), `title`/`author`/`requester_name`/`requester_note`, `file_path` (the uploaded PDF, stored on the `local` disk same as producer app), `status` (`pending` → `claimed` → `completed`, or `rejected`), `book_id` (nullable, filled in once the producer app publishes the fulfilling book).
- `book_imports`: the async publish queue (see "File-based book import" gotcha below). `source_local_id`, `filename` (relative to `storage/app/private/book-imports/`), `request_uuid` (nullable, carried over from the publish call), `status` (`pending` → `processing` → `done`/`failed`), `error_message`, `processed_at`.

## Routes

Public (`routes/web.php`):
- `GET /books`, `GET /books/{book}` — the viewer. No upload, no processing controls, no delete.
- `GET /books/by-source/{sourceLocalId}` — redirects to the real book page, looked up by the producer app's own book id (`source_local_id`) instead of this app's `id`. Exists because publishing is now async (file-based import, see below) — the producer app has no way to know this app's `id` for a book at the moment it finishes publishing, only its own id. 404s if the import hasn't landed yet.
- `GET /request`, `POST /request` — the "minta kitab" upload form.
- `GET /request/{uuid}` — visitor status page (pending/claimed→"sedang diproses"/completed→link to the book/rejected).

Sync API (`routes/api.php`, prefix `/api/sync/*`, **all behind `sync.token` middleware** — see below), called only by the producer app's `HostedSyncService`:
- `GET /requests/pending` — list of `pending` `BookRequest`s.
- `POST /requests/{bookRequest}/claim` — marks one `claimed` so it drops off the pending list (409 if already claimed/completed).
- `GET /requests/{bookRequest}/download` — streams the request's uploaded PDF.
- `POST /books` (`BookSyncController::store`) — full-replace upsert of one book's complete content sent directly as a JSON body, matched by `source_local_id`. Still here for small/medium payloads, but the producer app's actual publish flow no longer calls this — see file-based import below.
- `POST /books/import-signal` (`BookImportSignalController::store`) — the producer app's real publish path. Only logs a `pending` `BookImport` row (`source_local_id`, `filename`, `request_uuid`); does **not** touch `Book`/`Page`/`Paragraph` itself. The actual file (pushed here separately over FTPS, not through this endpoint) is picked up by the `books:process-imports` cron command.

## Auth: `VerifySyncToken` middleware (alias `sync.token`)

Not a real auth system — a single shared secret. Checks `Authorization: Bearer {token}` against `config('services.sync.token')` (env `SYNC_TOKEN`) with `hash_equals()`. **This value must match `HOSTED_SYNC_TOKEN` in the producer app's `.env` exactly** — different env var names on each side by design (one is "this is who I am" on this app, the other is "this is who I'm calling" on the producer app), don't let the name mismatch confuse a future search for it.

## Known gotchas (verified while wiring up the producer↔hosted sync)

- **A Laravel API endpoint without the caller sending `Accept: application/json` will render validation failures as an HTML redirect, not a JSON error.** This isn't specific to this app, but it's exactly what happened here: `BookSyncController::store`'s validation failure came back as a 302 to `/` because the caller (producer app's `HostedSyncService`) didn't declare `acceptJson()`. If you're debugging a sync call that "succeeds" with obviously wrong data (e.g. a `book_id` of `0`), check whether the caller actually got JSON back or silently followed/ignored a redirect — `Illuminate\Http\Client\Response::failed()` only flags 4xx/5xx, so a 3xx sails right through unnoticed.
- **`'pages' => ['required', 'array']` rejects a legitimately empty array.** A producer-side book where every paragraph failed AI processing has zero pages worth syncing — `[]` is valid input here, but Laravel's `required` rule fails on empty arrays (it means "present AND non-empty," not just "key exists"). Use `'present'` instead of `'required'` for array fields that are allowed to be empty.
- **Full-replace sync, not incremental.** `BookSyncController::store` deletes and recreates a book's entire `pages`/`paragraphs` tree on every call (matched by `source_local_id`). This is intentional — the producer app always sends the complete current state, so diffing would just be extra complexity for no benefit. Don't try to "optimize" this into a partial update without checking the producer side sends partial data (it doesn't, and shouldn't).
- **No queue, no jobs, no `QUEUE_CONNECTION` dependency here** — if you're tempted to add a background job for anything on this app, reconsider; the entire design premise is that this app stays request/response-only so it can run on the cheapest possible hosting. If something feels like it needs a job, it probably belongs in the producer app instead, synced over via the API. **The one exception is `books:process-imports`** (see below) — a cron-triggered command, not a persistent worker, which is a different cost/complexity tradeoff than a queue and was judged acceptable.
- **File-based book import, added after direct JSON publishing started intermittently failing on large books** (`fwrite(): Unable to create temporary file` on the producer side — eventually root-caused there to Flysystem's FTP adapter buffering writes through an in-memory stream; the producer switched to raw `ftp_*()` functions writing from a real local file instead, see that app's `AGENTS.md`). The producer app pushes the export JSON to this server directly over **FTPS** into `storage/app/private/book-imports/` (same disk/path convention as everything else here — outside this app's own control, since it's a raw file drop, not an upload through Laravel), then calls `POST /api/sync/books/import-signal` with just `{source_local_id, filename, request_uuid}`. That endpoint only creates a `pending` `BookImport` row — it's intentionally kept tiny and fast regardless of file size. The actual heavy lifting (read file, validate, upsert `Book`/`Page`/`Paragraph` via `BookImportProcessor`, delete the file) happens in `books:process-imports`. `BookImportProcessor` holds the upsert logic shared with `BookSyncController::store()` (the older direct-JSON path, still used for small payloads) — if you change the sync payload shape, update validation/upsert in one place (`BookImportProcessor`), not both controllers.
- **`Schedule::command()` in `routes/console.php` does not work on the real hosting account — `proc_open()` is disabled there** (a common shared-hosting security restriction), and `Schedule::command()`/`schedule:run` needs it to spawn the command as a separate process via Symfony Process. This surfaced as `Symfony\Component\Process\Exception\LogicException: The Process class relies on proc_open` in the cron's error log the first time this was tried. The registration is left in `routes/console.php` for local dev/documentation only — **the real cPanel cron entry calls the artisan command directly, bypassing `schedule:run` entirely**: `* * * * * /usr/local/bin/ea-php84 /home/fajh6696/public_html/aikitab.fajarnugroho.info/artisan books:process-imports >> /dev/null 2>&1`. If you add more scheduled commands later, they need the same direct-cron-entry treatment, not `Schedule::command()`, on this specific host.

## Local dev loop

```
php artisan serve --port=8001   # no queue worker needed — nothing here ever dispatches a job
npm run build                    # after any Blade class-name changes
```

To test the full producer↔hosted loop, also run the producer app (`ai-kitab`) side by side with its `HOSTED_API_URL` pointed at `http://127.0.0.1:8001` and both apps' sync tokens matching.

## Deployment

This is the piece meant to actually go on public/shared hosting. No Ghostscript/Tesseract/Imagick dependency (nothing here even touches PDF content beyond storing/serving the raw bytes visitors upload), and **no persistent queue worker** — the only background-ish requirement is a lightweight per-minute cron entry for `books:process-imports` (see the `proc_open`/`Schedule::command()` gotcha above for why it calls the artisan command directly instead of the usual `schedule:run` pattern), well within what ordinary shared PHP hosting supports.
