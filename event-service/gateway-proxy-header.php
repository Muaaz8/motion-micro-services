<?php

// In your ProxyController@forward method, after JWT validation passes,
// add the user ID as a header before forwarding to the upstream service.
// Your AuthenticateRequest gateway middleware already decodes the JWT —
// it should set the user on the request so ProxyController can read it.

// Example — inside your forward() method when building the upstream request:

$headers = [
    'Accept'          => 'application/json',
    'Content-Type'    => $request->header('Content-Type', 'application/json'),
    'X-Auth-User-Id'  => $request->user()?->id ?? $request->attributes->get('auth_user_id'),
    'X-Forwarded-For' => $request->ip(),
];

// This way every downstream service (event, kiosk, tournament) knows
// who made the request without re-validating the JWT themselves.
