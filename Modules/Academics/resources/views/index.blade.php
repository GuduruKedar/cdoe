@extends('layouts.app')

@section('page_title', 'Academics')

@section('content')
    <div class="card">
        <h3>Hello, {{ $user->usercode }}! 👋</h3>
        <p style="color:#64748b; font-size:14px;">This is the <b>Academics</b> module.</p>
    </div>
@endsection
