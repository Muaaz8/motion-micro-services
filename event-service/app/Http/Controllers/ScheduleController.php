<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $schedules = $event->schedules()->orderBy('start_datetime')->get();

        return response()->json(['data' => $schedules]);
    }

    public function store(Request $request, int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $this->validate($request, [
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_datetime' => 'required|date|before:end_datetime',
            'end_datetime'   => 'required|date|after:start_datetime',
        ]);

        $schedule = $event->schedules()->create($request->only([
            'title', 'description', 'start_datetime', 'end_datetime',
        ]));

        return response()->json([
            'message' => 'Schedule created successfully.',
            'data'    => $schedule,
        ], 201);
    }

    public function update(Request $request, int $eventId, int $scheduleId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $schedule = $event->schedules()->find($scheduleId);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        }

        $this->validate($request, [
            'title'          => 'sometimes|string|max:255',
            'description'    => 'nullable|string',
            'start_datetime' => 'sometimes|date',
            'end_datetime'   => 'sometimes|date|after:start_datetime',
        ]);

        $schedule->update($request->only([
            'title', 'description', 'start_datetime', 'end_datetime',
        ]));

        return response()->json([
            'message' => 'Schedule updated successfully.',
            'data'    => $schedule->fresh(),
        ]);
    }

    public function destroy(int $eventId, int $scheduleId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $schedule = $event->schedules()->find($scheduleId);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        }

        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted successfully.']);
    }
}
