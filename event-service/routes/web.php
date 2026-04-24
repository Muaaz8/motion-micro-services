<?php

/** @var \Laravel\Lumen\Routing\Router $router */

// Health check
$router->get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'service' => 'event-service',
        'version' => '1.0.0',
    ]);
});

// Events
$router->get('/api/events',         ['uses' => 'EventController@index']);
$router->post('/api/events',        ['uses' => 'EventController@store']);
$router->get('/api/events/{id}',    ['uses' => 'EventController@show']);
$router->put('/api/events/{id}',    ['uses' => 'EventController@update']);
$router->delete('/api/events/{id}', ['uses' => 'EventController@destroy']);

// Venue (1:1 inside event)
$router->get('/api/events/{eventId}/venue',    ['uses' => 'VenueController@show']);
$router->post('/api/events/{eventId}/venue',   ['uses' => 'VenueController@store']);
$router->put('/api/events/{eventId}/venue',    ['uses' => 'VenueController@update']);
$router->delete('/api/events/{eventId}/venue', ['uses' => 'VenueController@destroy']);

// Schedules (1:many)
$router->get('/api/events/{eventId}/schedules',                 ['uses' => 'ScheduleController@index']);
$router->post('/api/events/{eventId}/schedules',                ['uses' => 'ScheduleController@store']);
$router->put('/api/events/{eventId}/schedules/{scheduleId}',    ['uses' => 'ScheduleController@update']);
$router->delete('/api/events/{eventId}/schedules/{scheduleId}', ['uses' => 'ScheduleController@destroy']);

// Ticket Types (1:many)
$router->get('/api/events/{eventId}/ticket-types',                   ['uses' => 'TicketTypeController@index']);
$router->post('/api/events/{eventId}/ticket-types',                  ['uses' => 'TicketTypeController@store']);
$router->put('/api/events/{eventId}/ticket-types/{ticketTypeId}',    ['uses' => 'TicketTypeController@update']);
$router->delete('/api/events/{eventId}/ticket-types/{ticketTypeId}', ['uses' => 'TicketTypeController@destroy']);
