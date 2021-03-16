<nav class="navbar navbar-expand-md navbar-dark">
    <a class="navbar-brand" href="{{route('home')}}">Botify</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="nav-link" href="{{route('home')}}">Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="{{route('settings')}}">Settings <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="#">About</a>
            </li>
        </ul>
        <notification-dropdown
            notification-un-dismissed-route="{{route('notification-un-dismissed')}}"
            dismiss-all-notifications-route="{{route('notification-dismiss-all')}}"
            dismiss-notification-route="{{route('notification-dismiss', '')}}"
        ></notification-dropdown>
    </div>
</nav>
<script>
    import NotificationDropdown from "../../js/components/notification-dropdown";
    export default {
        components: {NotificationDropdown}
    }
</script>
