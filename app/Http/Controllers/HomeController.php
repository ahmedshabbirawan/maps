<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('collections.index');
        }

        return view('home');
    }
}
