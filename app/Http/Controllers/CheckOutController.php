<?php

namespace App\Http\Controllers;

use App\Models\ParkingSession;
use App\Models\ParkingSlot;
use App\Models\Parkinglot;

use Carbon\Carbon;

class CheckOutController extends Controller
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
    function calculateParking($startTime, $endTime, $freeTimeMinutes, $pricePerHour)
    {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);

        if ($startTime->gt($endTime)) {
            return [
                'error' => 'Invalid entry and exit times',
            ];
        }
        // คำนวณระยะเวลาการจอดรถ (นาที)
        $parkingDurationMinutes = $startTime->diffInMinutes($endTime);

        // หักลด Freetime นาทีแรก
        if ($parkingDurationMinutes > 0) {
            $parkingDurationMinutes -= $freeTimeMinutes;
        }

        // คำนวณจำนวนช่วง
        $numIntervals = ceil($parkingDurationMinutes / 60);

        // คิดเงิน
        $parkingFee = $numIntervals * $pricePerHour;

        return $parkingFee;
    }
    public function checkOut()
    {
        $unavailableCardIds = $this->cardUnavailable();
        if ($unavailableCardIds->isEmpty()) {
            return response()->json(['error' => 'No available cards']);
        }
        $randomCardId = $unavailableCardIds->random();

        if (!is_null($randomCardId)) {
            $parkingSession = ParkingSession::where('card_id', $randomCardId)
                ->whereNull('check_out_time')
                ->first();

            if ($parkingSession) {
                $checkOutTime = Carbon::now();
                $parkingSession->update(['check_out_time' => $checkOutTime]);
                $parkingSlot = ParkingSlot::find($parkingSession->parking_slot_id);
                if ($parkingSlot) {
                    $parkingSlot->update(['is_available' => 0]);
                }
                $parkingSlot = ParkingSlot::with('parkingLot')->find($parkingSlot->id);
                $pricePerHour = $parkingSlot->parkingLot->price_per_hour;
                $freeTimeMinutes = $parkingSlot->parkingLot->free_time_minutes;
                $startTime = $parkingSession->check_in_time;
                $endTime   = $parkingSession->check_out_time;
                $totalCost = $this->calculateParking($startTime, $endTime, $freeTimeMinutes, $pricePerHour);
                $parkingSession->check_out_time = $checkOutTime->format('Y-m-d H:i:s');
                return response()->json(['CheckOut Successful' => $parkingSession, 'Total Cost' => $totalCost]);
            } else {
                return response()->json([
                    'error' => 'ParkingSession not found or already checked out',
                ]);
            }

            return response()->json(['CheckOut Successful : ' => $parkingSession]);
        } else {
            return response()->json([
                'error' => 'not found CardId ',
            ]);
        }
    }

}
