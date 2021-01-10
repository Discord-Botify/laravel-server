require('./bootstrap');
// const Vue = require("laravel-mix");
// window.Vue = require('vue');
import Vue from 'vue';

// Vue.component('followed-artists', require('./components/followed-artists.vue').default);

import FollowedArtists from './components/followed-artists.vue';

var app = new Vue({
    el: '#app',
    data: {
        message: 'Hello Vue!'
    },
    components: {
        FollowedArtists
    }
})
