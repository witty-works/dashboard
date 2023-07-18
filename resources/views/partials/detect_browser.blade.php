<script>
    function detectBrowser() {
        if (window.navigator.userAgent.toLowerCase().indexOf("edg") > -1) {
            return 'edge';
        }

        if (window.navigator.userAgent.toLowerCase().indexOf("firefox") > -1) {
            return 'firefox';
        }

        if (!!navigator.userAgentData && navigator.userAgentData.brands.some(data => data.brand == 'Chromium')) {
            return 'chrome';
        }
    }
</script>