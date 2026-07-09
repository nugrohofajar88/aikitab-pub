<?php

namespace App\Http\Controllers;

use App\Models\BookImport;
use App\Models\BookRequest;
use Illuminate\View\View;

class StatusController extends Controller
{
    /**
     * Public activity log: "minta kitab" requests and their processing
     * status, plus the file-based sync log (see AGENTS.md "File-based book
     * import") showing what the producer app has pushed here and whether it
     * landed successfully. No auth on this app at all, so this is
     * intentionally public — treated like a changelog/status page, not an
     * admin panel. Deliberately doesn't show requester_name/requester_note
     * (kept private even though status itself is public), matching what the
     * per-request status page already does.
     */
    public function index(): View
    {
        $requests = BookRequest::latest()->take(50)->get();
        $imports = BookImport::latest()->take(50)->get();

        return view('status.index', compact('requests', 'imports'));
    }
}
