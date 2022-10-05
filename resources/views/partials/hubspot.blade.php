@if (config('hubspot.js_enabled'))
<script type="text/javascript">
    function onConversationsAPIReady() {
      console.log(`HubSpot Conversations API: ${window.HubSpotConversations}`);
    }
    /*
      configure window.hsConversationsSettings if needed.
    */
    window.hsConversationsSettings = {};
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