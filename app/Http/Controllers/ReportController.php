<?php

namespace App\Http\Controllers;
use App\Models\Parkinglot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    function calculateParking($startTime, $endTime, $freeTimeMinutes, $pricePerHour) {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);
        if ($endTime->lt($startTime)) {
            return ['error' => 'Invalid entry and exit times'];
        }

        // คำนวณระยะเวลาการจอดรถ (นาที)
        $parkingDurationMinutes = $endTime->diffInMinutes($startTime);

        // หักลด Freetime นาทีแรก
        if ($parkingDurationMinutes > $freeTimeMinutes) {
            $parkingDurationMinutes -= $freeTimeMinutes;
        } else {
            $parkingDurationMinutes = 0;
        }

        // คำนวณจำนวนช่วง
        $numIntervals = ceil($parkingDurationMinutes / 60);

        // คิดเงิน
        $parkingFee = $numIntervals * $pricePerHour;
        return $parkingFee;
    }

    public function report(Request $request) {
        $parkingLotId = $request->input('parking_lot_id');
        $parkingLot = Parkinglot::find($parkingLotId);
        $parkingSlots = $parkingLot->parkingSlots()->with('parkingSessions')->get();
        $dailyReport = [];
        $weeklyReport = [];
        $monthlyReport = array_fill(1, 12, 0);

        foreach ($parkingSlots as $slot) {
            foreach ($slot->parkingSessions as $session) {
                if ($session->check_out_time) {
                    $sessionFee = $this->calculateParking(
                        $session->check_in_time,
                        $session->check_out_time,
                        $parkingLot->free_time_minutes,
                        $parkingLot->price_per_hour
                    );

                    $checkoutDate = Carbon::parse($session->check_out_time);
                    $checkoutDateKey = $checkoutDate->format('Y-m-d');

                    if (!isset($dailyReport[$checkoutDateKey])) {
                        $dailyReport[$checkoutDateKey] = [
                            'total_revenue' => $sessionFee,
                            'total_sessions' => 1,
                        ];
                    } else {
                        $dailyReport[$checkoutDateKey]['total_revenue'] += $sessionFee;
                        $dailyReport[$checkoutDateKey]['total_sessions']++;
                    }

                    $weekNumber = $checkoutDate->weekOfYear;
                    $weeklyReport[$weekNumber] = ($weeklyReport[$weekNumber] ?? 0) + $sessionFee;

                    $monthNumber = $checkoutDate->month;
                    $monthlyReport[$monthNumber] += $sessionFee;
                }
            }
        }
        ksort($dailyReport);
        ksort($weeklyReport);

        return response()->json([
            'parking_lot' => $parkingLot,
            'daily_report' => $dailyReport,
            'weekly_report' => $weeklyReport,
            'monthly_report' => $monthlyReport,
        ]);
    }


}
