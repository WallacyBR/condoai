<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessControlDashboardController extends Controller
{
    public function getDashboardData(Request $request)
    {
        // For now, return a simple success message.
        // You'll need to add the actual dashboard logic here.
        return response()->json([
            'message' => 'Access Control Dashboard data will be here.',
            'data' => [],
        ]);
    }
}
