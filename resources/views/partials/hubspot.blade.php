@if (config('hubspot.js_enabled'))
<script type="text/javascript">
    function onConversationsAPIReady() {
        window.HubSpotConversations.widget.load();
    }

    // Configure window.hsConversationsSettings if needed.
    window.hsConversationsSettings = {
        @if(!empty($user) && Session::has('hubspot_identification_token'))
        identificationEmail: @json($user->email),
        identificationToken: "{{ Session::get('hubspot_identification_token') }}",
        @endif
        loadImmediately: false
    };

    // If external API methods are already available, use them.
    if (window.HubSpotConversations) {
        onConversationsAPIReady();
    } else {
        /*
            Otherwise, callbacks can be added to the hsConversationsOnReady on the window object.
            These callbacks will be called once the external API has been initialized.
        */
        window.hsConversationsOnReady = [onConversationsAPIReady];
    }

    @if(!empty($user))
    var _hsq = window._hsq = window._hsq || [];
    _hsq.push(["identify",{
        email: @json($user->email),
        id: @json($user->posthogId()),
    }]);
    @endif
</script>

@if(!empty($user))
<script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/{{ config('hubspot.hub_id') }}.js"></script>
@endif
@endif
