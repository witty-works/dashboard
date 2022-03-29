<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class SubscribeRedirectController extends Controller
{
    public function redirect()
    {
        if (config('spark.enabled')) {
            return redirect()->route('spark.portal');
        }

        return redirect('https://www.witty.works/pricing');
    }
}
