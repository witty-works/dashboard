@if (config('hubspot.js_enabled'))
<script type="text/javascript">
    function onConversationsAPIReady() {
      window.HubSpotConversations.widget.load();
    }
    /*
      configure window.hsConversationsSettings if needed.
    */
    window.hsConversationsSettings = {
      @if($user && Session::has('hubspot_identification_token'))
      identificationEmail: "{{ $user->email }}",
      identificationToken: "{{ Session::get('hubspot_identification_token') }}",
      @endif
      loadImmediately: false
    };

    /*
     If external API methods are already available, use them.
    */
    if (window.HubSpotConversations) {
      onConversationsAPIReady();
    } else {
      /*
        Otherwise, callbacks can be added to the hsConversationsOnReady on the window object.
        These callbacks will be called once the external API has been initialized.
      */
      window.hsConversationsOnReady = [onConversationsAPIReady];
    }
</script>
@endif