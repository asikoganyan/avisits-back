<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class WidgetServiceController extends Controller
{
    public function __construct(Request $request)
    {
        $this->chain = $request->route('chain');
    }

    public function services(Request $request)
    {
        $filter = $request->post();
        $services = Service::getServices($this->chain, $filter);
        return response()->json(['data' => $services], 200);
    }
}