<template>
    <div class="dropdown">
        <button
            class="btn btn-primary dropdown-toggle" id="notificationDropdownButton"
            data-toggle="dropdown" @click="grabNotifications">
            <i class="fas fa-bell text-primary"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right scrollable-menu" id="notificationDropdown" aria-labelledby="notificationDropdownButton">
            <h5 class="dropdown-header"><button class="btn btn-danger btn-sm" @click="clearAllNotifications" :disabled="notifications === {}">Clear all notifications</button></h5>
            <div v-for="notification in notifications">
                <div class="dropdown-divider"></div>
                <form class="mx-2">
                    <div class="form-group">
                        <label>New {{notification.album_type}} from {{notification.artist_name}}: {{notification.album_name}}</label>
                    </div>
                    <div class="form-group">
                        <a :href="notification.album_uri" class="btn btn-spotify btn-sm">Open on Spotify <i class="fab fa-spotify"></i></a>
                        <button class="btn btn-danger" type="button" @click="clearNotification(notification.notification_id)"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import $ from 'jquery';

    export default {
        name: "notification-dropdown",
        mounted() {
            this.grabNotifications();

            // Prevents menu from closing when clicked inside
            document.getElementById("notificationDropdown").addEventListener('click', function (event) {
                event.stopPropagation();
            });

            // Closes the menu in the event of outside click
            window.onclick = function(event) {
                if (!event.target.matches('.dropbutton')) {

                    let dropdowns =
                        document.getElementsByClassName("dropdownmenu-content");

                    let i;
                    for (i = 0; i < dropdowns.length; i++) {
                        let openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }
        },
        methods: {
            grabNotifications: function () {
                let data = axios.get(this.notificationUnDismissedRoute)
                .then(response => {
                    this.notifications = response.data;
                })
                .catch(error => {
                    console.log(error);
                });

            },
            clearAllNotifications: function () {
                axios.put(this.dismissAllNotificationsRoute)
                .then(response => {
                    this.grabNotifications();
                    this.$notify({
                        group: 'foo',
                        title: '',
                        text: response.data
                    });
                    this.notifications = {};
                })
                .catch(error => {
                    console.log(error);
                });
            },
            clearNotification: function (notificationId) {
                axios.put(this.dismissNotificationRoute + '/' + notificationId)
                .then(response => {
                    this.$delete(this.notifications, notificationId);

                    this.$notify({
                        group: 'foo',
                        title: '',
                        text: response.data
                    });
                })
                .catch(error => {
                    console.log(error);
                });
            }
        },
        props: {
            notificationUnDismissedRoute: {type: String, required: true},
            dismissAllNotificationsRoute: {type: String, required: true},
            dismissNotificationRoute: {type: String, required: true}
        },
        data: function () {
            return {
                notifications: {}
            }
        }
    }
</script>

<style scoped>

</style>
