<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Mail\ContactSubmissionReceived;
use App\Models\ContactSubmission;
use App\Services\SimpleCaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(Request $request, SimpleCaptcha $captcha): View
    {
        if ($request->boolean('refresh')) {
            $captcha->forget();
        }

        $captchaQuestion = $captcha->question() ?? $captcha->generate();

        return view('contact.create', [
            'captchaQuestion' => $captchaQuestion,
        ]);
    }

    public function store(StoreContactSubmissionRequest $request, SimpleCaptcha $captcha): RedirectResponse
    {
        $submission = ContactSubmission::create([
            'user_id' => auth()->id(),
            'email' => $request->validated('email'),
            'mobile' => $request->validated('mobile'),
            'message' => $request->validated('message'),
            'ip_address' => $request->ip(),
        ]);

        $captcha->forget();

        $adminEmail = config('app.admin_email');

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new ContactSubmissionReceived($submission));
        }

        return redirect()
            ->route('contact.create')
            ->with('success', 'Thank you! Your message has been sent. We will get back to you soon.');
    }
}
