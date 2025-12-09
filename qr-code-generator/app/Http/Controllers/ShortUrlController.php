<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class ShortUrlController extends Controller
{
    /**
     * CREATE NEW QR CODE (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_title'   => 'required|string|max:255',
            'venue'         => 'required|string|max:255',
            'event_date'    => 'required|date',
            'event_time'    => 'required',
            'department'    => 'required|string',
            'description'   => 'nullable|string',
            'link_type'     => 'required|in:external,internal',
            'external_link' => 'required_if:link_type,external|url|nullable',
        ]);

        do {
            $code = strtoupper(Str::random(8));
        } while (ShortUrl::where('short_code', $code)->exists());

        ShortUrl::create([
            'short_code'      => $code,
            'event_title'     => $request->event_title,
            'venue'           => $request->venue,
            'event_date'      => $request->event_date,
            'event_time'      => $request->event_time,
            'department'      => $request->department,
            'description'     => $request->description,
            'destination_url' => $request->link_type === 'external' ? $request->external_link : null,
            'created_by'      => Auth::id(),
            'status'          => 'active',
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'QR Code created successfully!',
            'short_url'  => url("/s/{$code}"),
            'short_code' => $code,
        ]);
    }

    /**
     * DATATABLES - Show all QR codes in admin panel
     */
    public function datatables(Request $request)
    {
        // SIMPLE, FAST, BULLETPROOF — NO RELATIONSHIPS, NO CRASHES
        $urls = ShortUrl::orderBy('created_at', 'desc')->get();

        $data = [];
        foreach ($urls as $url) {
            $fullUrl = url("/s/" . $url->short_code);
            $type    = $url->destination_url ? 'External Link' : 'Internal Form';

            $data[] = [
                'event_title' => "
                    <div data-label='Event'>
                        <div class='fw-bold'>" . htmlspecialchars($url->event_title) . "</div>
                        <small class='text-muted'>" . htmlspecialchars($url->venue ?? 'No venue') . "</small>
                        <div><small class='text-success fw-bold'>{$url->registrations_count} registration(s)</small></div>
                    </div>",
                'event_date' => "
                    <div data-label='Date & Time'>
                        " . ($url->event_date
                            ? \Carbon\Carbon::parse($url->event_date)->format('M d, Y') . "<br><small>{$url->event_time}</small>"
                            : '—') . "
                    </div>",
                'venue' => "
                    <div data-label='Type'>
                        <span class='badge bg-" . ($url->destination_url ? "warning" : "success") . "'>
                            " . ($url->destination_url ? 'External Link' : 'Internal Form') . "
                        </span>
                    </div>",
                'department' => "
                    <div data-label='Department'>
                        <span class='badge bg-primary'>" . htmlspecialchars($url->department ?? 'All') . "</span>
                    </div>",
                'created_by' => "
                    <div data-label='Created By'>
                        " . htmlspecialchars($url->creator?->name ?? 'Admin') . "
                    </div>",
                'action' => "
                    <div data-label='Actions' class='text-center'>
                        <div class='btn-group' role='group'>
                            <button class='btn btn-sm btn-outline-primary copy-link' data-link='{$fullUrl}'>
                                <i class='bi bi-link-45deg'></i>
                            </button>
                            <button class='btn btn-sm btn-outline-info preview-qr' data-id='{$url->id}' data-title='" . htmlspecialchars($url->event_title) . "' data-venue='" . htmlspecialchars($url->venue ?? '') . "'>
                                <i class='bi bi-qr-code-scan'></i>
                            </button>
                            <button class='btn btn-sm btn-outline-success download-qr' data-id='{$url->id}'>
                                <i class='bi bi-download'></i>
                            </button>
                            <button class='btn btn-sm btn-outline-danger delete-qr' data-id='{$url->id}'>
                                <i class='bi bi-trash'></i>
                            </button>
                        </div>
                    </div>"
            ];
        }

        return response()->json([
            'draw'            => (int)($request->input('draw') ?? 1),
            'recordsTotal'    => count($data),
            'recordsFiltered' => count($data),
            'data'            => $data
        ]);
    }

    /**
     * DELETE QR CODE
     */
    public function destroy($id)
    {
        $url = ShortUrl::findOrFail($id);

        // Optional: only allow creator or admin
        if ($url->created_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $url->delete();

        return response()->json(['success' => true, 'message' => 'QR Code deleted successfully']);
    }

    /**
     * PUBLIC: /s/ABC123 → redirect or show form
     */
    public function redirect($code)
    {
        $url = ShortUrl::where('short_code', $code)->firstOrFail();

        if (! $url->isActive()) {
            abort(410, 'This QR code has expired or is no longer active.');
        }

        if ($url->destination_url) {
            return redirect()->away($url->destination_url);
        }

        return view('register.form', compact('url'));
    }

    /**
     * PUBLIC: Handle registration form submission
     */
    public function register(Request $request, $code)
    {
        if (! $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $url = ShortUrl::where('short_code', $code)->firstOrFail();

        if (! $url->isActive()) {
            return response()->json(['success' => false, 'message' => 'Link expired.'], 410);
        }

        $request->validate([
            'firstname'   => 'required|string|max:50',
            'middlename'  => 'nullable|string|max:50',
            'lastname'    => 'required|string|max:50',
            'lgu_company' => 'required|string|max:100',
            'position'    => 'required|string|max:50',
            'contact'     => 'required|string|max:50',
            'purpose'     => 'required|string|max:1000',
        ]);

        Registration::create([
            'short_code'  => $code,
            'firstname'   => $request->firstname,
            'middlename'  => $request->middlename,
            'lastname'    => $request->lastname,
            'lgu_company' => $request->lgu_company,
            'position'    => $request->position,
            'contact'     => $request->contact,
            'purpose'     => $request->purpose,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Thank you.'
        ]);
    }
}
