@extends('layouts/main')

@section('title', 'Botify')

@section('content')
    <div class="row d-flex justify-content-center">
        <followed-artists
            followed-artist-spotify-route="{{route('followed-artist-spotify')}}"
        ></followed-artists>
    </div>
@endsection
