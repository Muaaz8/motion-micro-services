<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    public function index(int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $ticketTypes = $event->ticketTypes()
            ->where('status', 'active')
            ->get()
            ->map(function ($ticket) {
                $ticket->available_quantity = $ticket->available_quantity;
                return $ticket;
            });

        return response()->json(['data' => $ticketTypes]);
    }

    public function store(Request $request, int $eventId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $this->validate($request, [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:1',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $ticketType = $event->ticketTypes()->create([
            'name'          => $request->name,
            'description'   => $request->description,
            'price'         => $request->price,
            'quantity'      => $request->quantity,
            'quantity_sold' => 0,
            'status'        => $request->status ?? 'active',
        ]);

        return response()->json([
            'message' => 'Ticket type created successfully.',
            'data'    => $ticketType,
        ], 201);
    }

    public function update(Request $request, int $eventId, int $ticketTypeId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $ticketType = $event->ticketTypes()->find($ticketTypeId);

        if (!$ticketType) {
            return response()->json(['message' => 'Ticket type not found.'], 404);
        }

        $this->validate($request, [
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'quantity'    => 'sometimes|integer|min:' . $ticketType->quantity_sold,
            'status'      => 'sometimes|in:active,inactive',
        ]);

        $ticketType->update($request->only([
            'name', 'description', 'price', 'quantity', 'status',
        ]));

        return response()->json([
            'message' => 'Ticket type updated successfully.',
            'data'    => $ticketType->fresh(),
        ]);
    }

    public function destroy(int $eventId, int $ticketTypeId): JsonResponse
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $ticketType = $event->ticketTypes()->find($ticketTypeId);

        if (!$ticketType) {
            return response()->json(['message' => 'Ticket type not found.'], 404);
        }

        if ($ticketType->quantity_sold > 0) {
            return response()->json([
                'message' => 'Cannot delete a ticket type that has already been sold. Deactivate it instead.',
            ], 422);
        }

        $ticketType->delete();

        return response()->json(['message' => 'Ticket type deleted successfully.']);
    }
}
