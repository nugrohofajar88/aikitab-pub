<?php

namespace App\Console\Commands;

use App\Models\BookImport;
use App\Services\BookImportProcessor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('books:process-imports')]
#[Description('Process pending book imports pushed to storage/app/private/book-imports over FTPS')]
class ProcessBookImports extends Command
{
    /**
     * Meant to run every minute via cron (`* * * * * php artisan schedule:run`
     * on the actual hosted cron, see routes/console.php for the schedule
     * registration). No queue worker involved — this app deliberately stays
     * request/response + cron only, see AGENTS.md.
     */
    public function handle(BookImportProcessor $processor): int
    {
        $imports = BookImport::where('status', 'pending')->orderBy('id')->get();

        foreach ($imports as $import) {
            $import->update(['status' => 'processing']);

            try {
                $path = 'book-imports/'.$import->filename;

                if (! Storage::disk('local')->exists($path)) {
                    throw new \RuntimeException("File tidak ditemukan: {$path}");
                }

                $payload = json_decode(Storage::disk('local')->get($path), true, flags: JSON_THROW_ON_ERROR);

                // request_uuid isn't part of the producer's export payload
                // itself (it's metadata about the request that prompted this
                // book), so it needs merging in from the import signal.
                $payload['request_uuid'] ??= $import->request_uuid;

                $validated = $processor->validate($payload);
                $processor->upsert($validated);

                Storage::disk('local')->delete($path);

                $import->update(['status' => 'done', 'processed_at' => now(), 'error_message' => null]);

                $this->info("Imported book (source_local_id={$import->source_local_id})");
            } catch (Throwable $e) {
                $import->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $this->error("Failed import id={$import->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
