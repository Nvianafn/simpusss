<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SimpusApiClient $client): View
    {
        $bundle = $client->statusBundle($request->query('nim'));

        return view('dashboard', $bundle);
    }
}
