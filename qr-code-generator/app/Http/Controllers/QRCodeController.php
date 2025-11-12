<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QRCodeController extends Controller
{
    /**
     * Display a listing of QR codes
     */
    public function index(Request $request)
    {
        $query = QRCode::with('creator');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('event_title', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $qrCodes = $query->orderBy('created_at', 'desc')->get();

        // Format the data
        $data = $qrCodes->map(function($qr) {
            return [
                'id' => $qr->id,
                'event_title' => $qr->event_title,
                'description' => $qr->description,
                'venue' => $qr->venue,
                'event_date' => $qr->event_date,
                'event_time' => $qr->event_time,
                'department' => $qr->department,
                'created_by' => $qr->creator->name ?? 'Unknown',
                'created_at' => $qr->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store a newly created QR code
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_title' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'department' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $qrCode = QRCode::create([
            'event_title' => $request->event_title,
            'venue' => $request->venue,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'department' => $request->department,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'qr_code' => $this->generateQRCode(), // You can implement actual QR generation
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QR Code created successfully',
            'data' => $qrCode
        ], 201);
    }

    /**
     * Display the specified QR code
     */
    public function show($id)
    {
        $qrCode = QRCode::with('creator')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $qrCode
        ]);
    }

    /**
     * Update the specified QR code
     */
    public function update(Request $request, $id)
    {
        $qrCode = QRCode::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'event_title' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'department' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $qrCode->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'QR Code updated successfully',
            'data' => $qrCode
        ]);
    }

    /**
     * Remove the specified QR code
     */
    public function destroy($id)
    {
        $qrCode = QRCode::findOrFail($id);
        $qrCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'QR Code deleted successfully'
        ]);
    }

    /**
     * Generate a unique QR code string
     */
    private function generateQRCode()
    {
        // You can implement actual QR code generation here
        // For now, just return a unique string
        return 'QR-' . strtoupper(uniqid());
    }
}
