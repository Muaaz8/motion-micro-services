<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['venue', 'ticketTypes']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->orderBy('start_date', 'asc')->paginate(15);

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:tournament,match,league,friendly,other',
            'status'      => 'nullable|in:draft,published,cancelled,completed',
            'start_date'  => 'required|date|before:end_date',
            'end_date'    => 'required|date|after:start_date',
        ]);

        $event = Event::create([
            'name'        => $request->name,
            'description' => $request->description,
            'type'        => $request->type,
            'status'      => $request->status ?? 'draft',
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'created_by'  => $request->header('X-Auth-User-Id'),
        ]);

        return response()->json([
            'message' => 'Event created successfully.',
            'data'    => $event->load(['venue', 'ticketTypes']),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $event = Event::with(['venue', 'schedules', 'ticketTypes'])->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        return response()->json(['data' => $event]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $this->validate($request, [
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'sometimes|in:tournament,match,league,friendly,other',
            'status'      => 'sometimes|in:draft,published,cancelled,completed',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'sometimes|date|after:start_date',
        ]);

        $event->update($request->only([
            'name', 'description', 'type', 'status', 'start_date', 'end_date',
        ]));

        return response()->json([
            'message' => 'Event updated successfully.',
            'data'    => $event->fresh(['venue', 'schedules', 'ticketTypes']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }
}
