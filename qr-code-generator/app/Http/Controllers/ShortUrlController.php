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
                // These EXACT keys must match your DataTables columns
                'event_title' => "
                    <div>
                        <strong>" . htmlspecialchars($url->event_title ?? 'Untitled Event') . "</strong>
                        <br><small class='text-muted'>" . htmlspecialchars($type) . "</small>
                    </div>",
                'event_date'  => $url->event_date
                    ? date('M d, Y', strtotime($url->event_date)) . "<br><small>{$url->event_time}</small>"
                    : '—',
                'venue'       => $url->venue,
                'department'  => "<span class='badge bg-primary'>" . htmlspecialchars($url->department ?? 'All') . "</span>",
                'created_by'  => 'Admin', // You can later replace with real user
                'action'      => "
                    <div class='btn-group' role='group'>
                        <button class='btn btn-sm btn-outline-primary copy-link' data-link='{$fullUrl}' title='Copy Link'>
                            <i class='bi bi-link-45deg'></i>
                        </button>
                        <button class='btn btn-sm btn-outline-success download-qr' data-code='{$url->short_code}' title='Download QR'>
                            <i class='bi bi-download'></i>
                        </button>
                        <button class='btn btn-sm btn-outline-danger delete-qr' data-id='{$url->id}' title='Delete'>
                            <i class='bi bi-trash'></i>
                        </button>
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
