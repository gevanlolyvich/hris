<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // Check duplicate subscription data
        $duplicate              = PushSubscription::where('user_id', \Auth::user()->id)->where('data', $request->getContent())->first();

        if (!$duplicate) {
            $new_subs           = new PushSubscription();
            $new_subs->user_id  = \Auth::user()->id;
            $new_subs->data     = $request->getContent();
            $new_subs->save();
        }
    }

    public function show(PushSubscription $pushSubscription)
    {
        //
    }

    public function edit(PushSubscription $pushSubscription)
    {
        //
    }

    public function update(Request $request, PushSubscription $pushSubscription)
    {
        //
    }

    public function destroy(PushSubscription $pushSubscription)
    {
        //
    }
}
