backend productboard1 {
    .host = "44.206.109.238";
    .port = "80";
    .connect_timeout = 5s;
    .first_byte_timeout = 5s;
    .between_bytes_timeout = 5s;
}

backend productboard2 {
    .host = "34.192.241.137";
    .port = "80";
    .connect_timeout = 5s;
    .first_byte_timeout = 5s;
    .between_bytes_timeout = 5s;
}

backend productboard3 {
    .host = "52.23.40.179";
    .port = "80";
    .connect_timeout = 5s;
    .first_byte_timeout = 5s;
    .between_bytes_timeout = 5s;
}

sub vcl_init {
    new productboard = directors.round_robin();
    productboard.add_backend(productboard1);
    productboard.add_backend(productboard2);
    productboard.add_backend(productboard3);
}

sub vcl_recv {
    set req.backend_hint = productboard.backend();
    set req.http.host = "roadmap.witty.works";
}

sub vcl_backend_response {
    #Fix a strange problem: HTTP 301 redirects to the same page sometimes go in$
    if (beresp.http.Location == "http://" + bereq.http.host + bereq.url
        || beresp.http.Location == "https://" + bereq.http.host + bereq.url
    ) {
        if (bereq.retries > 2) {
            unset beresp.http.Location;
            #set beresp.http.X-Restarts = bereq.retries;
        } else {
            return (retry);
        }
    }
}