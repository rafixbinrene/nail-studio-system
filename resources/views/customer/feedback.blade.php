@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Feedback View
|
| Purpose:
| - Shows booking information before feedback submission.
| - Allows rating from 1 to 5 stars.
| - Allows customer comment.
| - Prevents editing when feedback already exists.
|
| Defense explanation:
| Feedback is connected to a finished appointment to make the rating valid
| and traceable to an actual customer service transaction.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
.page-header {
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.page-header p {
    color: var(--muted);
    font-size: 14px;
}

.alert {
    padding: 14px 16px;
    border-radius: 18px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 700;
}

.alert-error {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.feedback-card {
    max-width: 760px;
    background: var(--glass);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 800;
}

input,
textarea {
    width: 100%;
    padding: 14px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    outline: none;
    margin-bottom: 18px;
}

input[readonly] {
    background: rgba(255,255,255,.35);
    color: var(--muted);
    cursor: not-allowed;
}

textarea {
    height: 130px;
    resize: none;
}

.rating-row {
    display: flex;
    gap: 10px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.rating {
    padding: 12px 16px;
    border-radius: 999px;
    background: rgba(255,255,255,.5);
    border: 1px solid var(--border);
    cursor: pointer;
    font-weight: 900;
}

.rating.active {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.button-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 6px;
}

.submit-btn {
    padding: 14px 24px;
    border: none;
    border-radius: 999px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
    cursor: pointer;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    padding: 14px 24px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
    font-weight: 900;
}

.readonly-note {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 18px;
}
</style>
@endsection

@section('content')

@php
    $mainService = $appointment->services->first()?->service_name ?? 'Beauty Service';

    $serviceList = $appointment->services->pluck('service_name')->join(', ');

    $selectedRating = old('rating', $existingFeedback?->rating ?? 5);
@endphp

<div class="page-header">
    <h1>Give Feedback</h1>
    <p>Share your experience after your completed appointment.</p>
</div>

@if ($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
    </div>
@endif

<section class="feedback-card">
    @if ($existingFeedback)
        <p class="readonly-note">
            You already submitted feedback for this appointment. Feedback cannot be submitted twice.
        </p>
    @endif

    <form method="POST" action="{{ route('customer.feedback.store', $appointment->id) }}">
        @csrf

        <label>Booking ID</label>
        <input
            type="text"
            value="BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}"
            readonly
        >

        <label>Service</label>
        <input
            type="text"
            value="{{ $serviceList ?: $mainService }}"
            readonly
        >

        <label>Staff</label>
        <input
            type="text"
            value="{{ $appointment->staff?->full_name ?? 'Not assigned' }}"
            readonly
        >

        <label>Rating</label>

        <input type="hidden" name="rating" id="ratingInput" value="{{ $selectedRating }}">

        <div class="rating-row">
            @for ($i = 1; $i <= 5; $i++)
                <button
                    type="button"
                    class="rating {{ (int) $selectedRating === $i ? 'active' : '' }}"
                    onclick="selectRating({{ $i }}, this)"
                    {{ $existingFeedback ? 'disabled' : '' }}
                >
                    {{ str_repeat('⭐', $i) }}
                </button>
            @endfor
        </div>

        <label>Comment</label>
        <textarea
            name="comment"
            maxlength="1000"
            placeholder="Write your feedback here..."
            {{ $existingFeedback ? 'readonly' : '' }}
        >{{ old('comment', $existingFeedback?->comment) }}</textarea>

        <div class="button-row">
            <a href="{{ route('customer.history') }}" class="back-btn">
                Back to Booking History
            </a>

            @if (!$existingFeedback)
                <button type="submit" class="submit-btn">
                    Submit Feedback
                </button>
            @endif
        </div>
    </form>
</section>

@endsection

@section('scripts')
<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Feedback Rating Selector
|--------------------------------------------------------------------------
| Purpose:
| - Updates the hidden rating input based on selected star button.
| - Gives the customer a simple clickable rating experience.
|--------------------------------------------------------------------------
*/
function selectRating(value, button) {
    document.getElementById('ratingInput').value = value;

    document.querySelectorAll('.rating').forEach(btn => {
        btn.classList.remove('active');
    });

    button.classList.add('active');
}
</script>
@endsection