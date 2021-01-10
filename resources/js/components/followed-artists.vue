<template>
    <div class="col-12">
        <div v-if="needsArtists" class="text-primary row d-flex justify-content-center mt-4">Looks like you haven't added any artists to the app! Click the button below to start receiving notifications for Artists you follow on Spotify</div>
        <div class="row d-flex justify-content-center mt-4">
            <button @click="syncSpotifyArtists()">Sync Followed Artists with Spotify</button>
        </div>
        <div class="row mt-2">
            <div class="col-xs-1 col-md-2 col-lg-3"></div>
            <div class="col-xs-10 col-md-8 col-lg-6">
                <div class="row">
                    <div v-for="artist in followedArtists">
                        <div class="">
                            <div class="card card-artist m-2">
                                <div class="card-body">
                                    {{artist.artist_name}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-1 col-md-2 col-lg-3"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: "followed-artists",
    data: function() {
        return {
            followedArtists: {},
            needsArtists: true,
            isLoading: false,
        }
    },
    props: {
        followedArtistSpotifyRoute: {type: String, required: true}
    },
    beforeMount() {
        this.grabDbArtists();
    },
    methods: {
        syncSpotifyArtists: function () {
            console.log('sync artists');
            console.log(this.followedArtistSpotifyRoute);
            this.isLoading = true;
            axios.get(this.followedArtistSpotifyRoute)
            .then((response) => {
                console.log(response);
                this.followedArtists = response.data;
            })
        },
        grabDbArtists: function() {
            console.log('DB artists');
        }
    }
}
</script>
