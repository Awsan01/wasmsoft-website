<?php

return [

    /*
    | Proxies (load balancers, CDNs) allowed to set X-Forwarded-* headers.
    | Comma-separated IPs/CIDR ranges, or "*" to trust the calling proxy.
    | Leave empty when the application is reached directly; trusting
    | arbitrary proxies would let clients spoof their IP address, which
    | would weaken rate limiting and audit logs.
    */
    'proxies' => env('TRUSTED_PROXIES'),

];
