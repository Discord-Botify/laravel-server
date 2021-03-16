require('./bootstrap');
// const Vue = require("laravel-mix");
// window.Vue = require('vue');
import Vue from 'vue';

import Notifications from 'vue-notification';
Vue.use(Notifications)

// Vue.component('followed-artists', require('./components/followed-artists.vue').default);

import FollowedArtists from './components/followed-artists.vue';
import NotificationDropdown from './components/notification-dropdown.vue';

let app = new Vue({
    el: '#app',
    data: {
        message: 'Hello Vue!',
        user: window.user
    },
    components: {
        FollowedArtists,
        NotificationDropdown
    },
})
