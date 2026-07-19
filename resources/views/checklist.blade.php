@extends('layouts.app')

@section('title', 'Checklist '.$child->name)
@section('page-title', $child->emoji.' '.$child->name)
@section('back', route('home'))

@section('content')
    <div class="date-nav">
        <a class="iconbtn" href="{{ route('checklist', [$child, $prevDate]) }}" aria-label="Hari sebelumnya">‹</a>
        <div class="date-nav-label">
            <b>{{ $day->isToday() ? 'Hari Ini' : $day->translatedFormat('l') }}</b>
            <small class="muted">{{ $day->translatedFormat('j F Y') }}</small>
        </div>
        @if ($nextDate)
            <a class="iconbtn" href="{{ route('checklist', [$child, $nextDate]) }}" aria-label="Hari berikutnya">›</a>
        @else
            <span class="iconbtn disabled" aria-hidden="true">›</span>
        @endif
    </div>

    @include('partials.pet-card')

    @include('partials.mood-card', ['moodUrl' => $day->isToday() ? route('checklist.mood', $child) : null])

    @include('partials.checklist-board', ['mode' => 'admin'])
@endsection
