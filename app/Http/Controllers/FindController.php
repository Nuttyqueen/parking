<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\ParkingSession;
use App\Models\ParkingSlot;
use App\Models\Parkinglot;
use Illuminate\Http\Request;

class FindController extends Controller
{
    public function getAllCards()
    {
        $cards = ParkingSession::all();
        return response()->json($cards);
    }

    public function getDetailParkingLot(Request $request)
    {
        $parkingLotId = $request->input('parking_lot_id');
        $parkingLot = ParkingLot::with('parkingSlots')->find($parkingLotId);
        return response()->json($parkingLot);
    }

    public function getFindParking(Request $request)
    {
        $cardId = $request->input('card_id');
        $sessions = ParkingSession::where('card_id', $cardId)->get();
        $parkingSlots = [];

        foreach ($sessions as $session) {
            $parkingSlot = ParkingSlot::where('id', $session->parking_slot_id)
                ->where('is_available', 1)
                ->first();

            if ($parkingSlot) {
                $currentSlot = [
                    'slot_number' => $parkingSlot->slot_number,
                    'slot_code' => $parkingSlot->slot_code,
                ];

                $neighborSlots = ParkingSlot::whereIn('slot_number', [
                    $currentSlot['slot_number'] - 1,
                    $currentSlot['slot_number'],
                    $currentSlot['slot_number'] + 1,
                ])
                    ->whereIn('slot_code', [
                        $currentSlot['slot_code'],
                        chr(ord($currentSlot['slot_code']) - 1),
                        chr(ord($currentSlot['slot_code']) + 1),
                    ])
                    ->where('id', '!=', $parkingSlot->id)
                    ->get(['slot_number', 'slot_code'])
                    ->toArray();

                $neighborSlots = array_unique($neighborSlots, SORT_REGULAR);
                $neighborSlots = array_filter($neighborSlots, function ($slot) use ($currentSlot) {
                    return $slot['slot_number'] !== $currentSlot['slot_number'] ||
                        $slot['slot_code'] !== $currentSlot['slot_code'];
                });

                $parkingSlots[] = [
                    'current_slot' => $currentSlot,
                    'neighbor_slots' => array_values($neighborSlots),
                ];

                /*                 $currentSlot = $parkingSlots['current_slot'];
                $neighborSlots = array_filter($parkingSlots['neighbor_slots'], function ($slot) use ($currentSlot) {
                    return $slot['slot_number'] !== $currentSlot['slot_number'] || $slot['slot_code'] !== $currentSlot['slot_code'];
                }); */
            } else {
                return response()->json([
                    'error' => 'No available location found for the specified card',
                ], 404);
            }
        }
        return response()->json($parkingSlots);
    }
}
