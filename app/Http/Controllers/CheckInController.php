<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Card;
use App\Models\ParkingSession;
use App\Models\ParkingSlot;

class CheckInController extends Controller
{

    public function cardUnavailable()
    {
        //หา Cards ID ที่ถูกใช้อยู่
        $parkingSlotIds = ParkingSlot::where('is_available', 1)->pluck('id');
        $cardIds = ParkingSession::whereIn('parking_slot_id', $parkingSlotIds)
            ->pluck('card_id')
            ->unique();
        return $cardIds;
    }
    public function cardAvailable()
    {
        //หา Cards ID ที่สามารถใช้งานได้
        $usedCardIds = $this->cardUnavailable();
        $availableCardIds = Card::whereNotIn('id', $usedCardIds)->pluck('id')->toArray();
        return $availableCardIds;
    }

    public function checkIn()
    {   //สุ่ม CardId ที่ยังไม่ถูกใช้
        $availableCardIds = $this->cardAvailable();
        $randomCardId = $availableCardIds[array_rand($availableCardIds)];

        //สุ่มที่จอดรถ Parking Slot ที่ยังไม่ถูกใช้
        $parkingSlotIds = ParkingSlot::where('is_available', 0)->pluck('id');
        if ($parkingSlotIds->isEmpty()) {
            return response()->json(['error' => 'No available parking slots']);
        } else {
            $randomParkingSlotId = $parkingSlotIds->random();
            if (!empty($randomCardId) && !empty($randomParkingSlotId)) {
                $checkInTime = Carbon::now();
                ParkingSession::create(['check_in_time' => $checkInTime, 'card_id' => $randomCardId, 'parking_slot_id' => $randomParkingSlotId]);
                $updateStatus = ParkingSlot::where('id', $randomParkingSlotId)->first();
                $updateStatus->update(['is_available' => 1]);
                $updateStatus->save();

                $checkIn = ParkingSession::where('card_id', $randomCardId)->where('parking_slot_id', $randomParkingSlotId)->first();

                return response()->json(['CheckIn Successful : ' => $checkIn]);
            } else {
                return response()->json([
                    'error' => 'not found CardId or ParkingSlotId',
                ]);
            }
        }
    }
}
