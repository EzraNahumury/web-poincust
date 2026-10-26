<?php

namespace App\Http\Controllers;

use App\Domain\Points\PointsConfig;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(PointsConfig $points): View
    {
        return view('landing', [
            'rupiahPerPoint' => $points->rupiahPerPoint(),
            'redeemValue' => $points->redeemValuePerPoint(),
        ]);
    }
}
