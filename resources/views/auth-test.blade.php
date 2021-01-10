@extends('layouts.auth')
@section('title', 'Welcome to Botify')

@section('content')
    <a href="{{$auth_link}}">Click here to sign in with Spotify</a>
@endsection
