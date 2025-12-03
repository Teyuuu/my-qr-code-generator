<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QrCode;

class QRCodeController extends Controller
{
    // MAIN DATATABLES SERVER-SIDE ENDPOINT
    public function datatables(Request $request)
    {
        $columns = [
            'event_title',
            'event_date',
            'venue',
            'department',
            'created_by'
        ];

        $totalData = QrCode::count();
        $totalFiltered = $totalData;

        $limit  = $request->input('length');
        $start  = $request->input('start');
        $order  = $columns[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $query = QrCode::query();

        // SEARCH
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('event_title', 'LIKE', "%{$search}%")
                  ->orWhere('venue', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%")
                  ->orWhere('created_by', 'LIKE', "%{$search}%");
            });

            $totalFiltered = $query->count();
        }

        // PAGINATION + ORDER
        $qrCodes = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        // FORMAT DATA
        $data = [];
        foreach ($qrCodes as $qr) {
            $data[] = [
                'event_title' => $qr->event_title,
                'event_date'  => $qr->event_date,
                'venue'       => $qr->venue,
                'department'  => $qr->department,
                'created_by'  => $qr->created_by,
                'action'      =>
                    '<div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary view-qr" data-id="'.$qr->id.'"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-secondary edit-qr" data-id="'.$qr->id.'"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-qr" data-id="'.$qr->id.'"><i class="bi bi-trash"></i></button>
                    </div>'
            ];
        }

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data
        ]);
    }

    // SHOW
    public function show($id)
    {
        $qr = QrCode::find($id);

        if (!$qr) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        return response()->json($qr);
    }
}
