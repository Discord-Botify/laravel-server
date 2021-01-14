<template>
    <div class="col-12" :class="listClass">
        <div v-if="needsArtists" class="text-primary row d-flex justify-content-center mt-4 mx-1 text-center">Looks like you haven't added any artists to the app! Click the button below to start receiving notifications for Artists you follow on Spotify</div>
        <div class="row d-flex justify-content-center mt-4">
            <button @click="syncSpotifyArtists()" :class="{disabled: isLoading}" :disabled="isLoading">Sync Followed Artists with Spotify</button>
        </div>
        <div class="row mt-2">
            <div class="col-xs-1 col-md-2"></div>
            <div class="col-xs-10 col-md-8">
                <div class="row justify-content-center">
                    <div v-for="artist in followedArtists">
                        <div class="">
                            <div class="card card-artist m-2">
                                <div class="card-header">
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
            isLoading: false,
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
            this.isLoading = true;
            axios.get(this.followedArtistSpotifyRoute)
                .then((response) => {
                    this.followedArtists = response.data;
                    this.isLoading = false;
                });
        },
        grabDbArtists: function() {
            console.log('DB artists');
            this.isLoading = true;
            axios.get(this.followedArtistDbRoute)
                .then((response) => {
                    this.followedArtists = response.data;
                    this.isLoading = false;
                });
        }
    },
    computed: {
        listClass: function () {
            return this.isLoading ? 'list-loading' : 'list-loaded'
        },
        needsArtists: function () {
            return !this.isLoading && Object.keys(this.followedArtists).length === 0;
        }
    }
}
</script>
