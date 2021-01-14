@extends('layouts/main')

@section('title', 'Botify')

@section('content')
    <div class="row d-flex justify-content-center">
        <followed-artists
            followed-artist-spotify-route="{{route('followed-artist-spotify')}}"
            followed-artist-db-route="{{route('followed-artist-db')}}"
        ></followed-artists>
    </div>
@endsection
