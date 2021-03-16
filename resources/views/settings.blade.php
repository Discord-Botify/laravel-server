@extends('layouts/main')

@section('title', 'Settings')

@section('content')
    <div class="row d-flex justify-content-center">
        <a href="{{$discord_oauth_url}}">Sign in with Discord</a>
    </div>
@endsection
