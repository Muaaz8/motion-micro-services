<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function show(int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $venue = $event->venue;

        if (!$venue) {
            return response()->json(['message' => 'No venue set for this event.'], 404);
        }

        return response()->json(['data' => $venue]);
    }

    public function store(Request $request, int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        if ($event->venue) {
            return response()->json([
                'message' => 'This event already has a venue. Use PUT to update it.',
            ], 422);
        }

        $this->validate($request, [
            'name'     => 'required|string|max:255',
            'address'  => 'required|string|max:255',
            'city'     => 'required|string|max:100',
            'country'  => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $venue = $event->venue()->create($request->only([
            'name', 'address', 'city', 'country', 'capacity',
        ]));

        return response()->json([
            'message' => 'Venue created successfully.',
            'data'    => $venue,
        ], 201);
    }

    public function update(Request $request, int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $venue = $event->venue;

        if (!$venue) {
            return response()->json(['message' => 'No venue found for this event.'], 404);
        }

        $this->validate($request, [
            'name'     => 'sometimes|string|max:255',
            'address'  => 'sometimes|string|max:255',
            'city'     => 'sometimes|string|max:100',
            'country'  => 'sometimes|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $venue->update($request->only([
            'name', 'address', 'city', 'country', 'capacity',
        ]));

        return response()->json([
            'message' => 'Venue updated successfully.',
            'data'    => $venue->fresh(),
        ]);
    }

    public function destroy(int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $venue = $event->venue;

        if (!$venue) {
            return response()->json(['message' => 'No venue found for this event.'], 404);
        }

        $venue->delete();

        return response()->json(['message' => 'Venue removed successfully.']);
    }
}
