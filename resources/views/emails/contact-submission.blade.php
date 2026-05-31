New contact or suggestion received

@if($submission->email)
Email: {{ $submission->email }}
@endif
@if($submission->mobile)
Mobile: {{ $submission->mobile }}
@endif

Message:
{{ $submission->message }}

---
Submitted: {{ $submission->created_at->toDateTimeString() }}
@if($submission->user)
From user: {{ $submission->user->name }} ({{ $submission->user->email }})
@endif
@if($submission->ip_address)
IP: {{ $submission->ip_address }}
@endif
