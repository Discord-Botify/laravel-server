<template>
    <div class="col-12" :class="listClass">
        <div v-if="needsArtists" class="text-primary row d-flex justify-content-center mt-4 mx-1 text-center">Looks like you haven't added any artists to the app! Click the button below to start receiving notifications for Artists you follow on Spotify</div>
        <div class="row d-flex justify-content-center mt-4">
            <button class="btn btn-success btn-spotify" @click="syncSpotifyArtists()" :class="{disabled: isLoading}" :disabled="isLoading">Sync Followed Artists with Spotify</button>
        </div>
        <div v-if="isLoadingFromSpotify" class="text-primary row d-flex justify-content-center mt-4 mx-1 text-center">Grabbing your followed artists from Spotify, this may take a while...</div>
        <div class="row mt-2">
            <div class="col-xs-1 col-md-2"></div>
            <div class="col-xs-10 col-md-8">
                <div class="row justify-content-center">
                    <div v-for="artist in followedArtists">
                        <div class="">
                            <div class="card card-artist m-2">
                                <div class="card-header text-truncate">
                                    {{artist.artist_name}}
                                </div>
                                <div class="card-body">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-1 col-md-2"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: "followed-artists",
    data: function() {
        return {
            followedArtists: {},
            isLoadingFromSpotify: false,
            isLoadingFromDatabase: false,
        }
    },
    props: {
        followedArtistSpotifyRoute: {type: String, required: true},
        followedArtistDbRoute: {type: String, required: true}
    },
    beforeMount() {
        this.grabDbArtists();
    },
    methods: {
        syncSpotifyArtists: function () {
            this.isLoadingFromSpotify = true;
            axios.get(this.followedArtistSpotifyRoute)
                .then((response) => {
                    this.followedArtists = response.data;
                    this.isLoadingFromSpotify = false;
                });
        },
        grabDbArtists: function() {
            this.isLoadingFromDatabase = true;
            axios.get(this.followedArtistDbRoute)
                .then((response) => {
                    this.followedArtists = response.data;
                    this.isLoadingFromDatabase = false;
                });
        }
    },
    computed: {
        listClass: function () {
            return this.isLoading ? 'list-loading' : 'list-loaded'
        },
        needsArtists: function () {
            return !this.isLoading && Object.keys(this.followedArtists).length === 0;
        },
        isLoading: function() {
            return this.isLoadingFromSpotify || this.isLoadingFromDatabase;
        }
    }
}
</script>
