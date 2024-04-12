<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\ParkingSession;
use App\Models\ParkingSlot;
use App\Models\Parkinglot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
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


    public function report(Request $request) {
        $parkingLotId = $request->input('parking_lot_id');
        $parkingLot = ParkingLot::find($parkingLotId);
        $parkingSlots = $parkingLot->parkingSlots()->with('parkingSessions')->get();

        $dailyReport = [];
        $dayReport = [];
        $weeklyReport = array_fill_keys(range(1, 52), 0);
        $monthlyReport = array_fill_keys(range(1, 12), 0);

        foreach ($parkingSlots as $slot) {
            $slotDailyReport = [
                'slot' => $slot->slot_number . '' . $slot->slot_code,
                'total_revenue' => 0,
                'total_sessions' => 0,
            ];

            foreach ($slot->parkingSessions as $session) {
                if ($session->check_out_time) {
                    $sessionFee = $this->calculateParking(
                        $session->check_in_time,
                        $session->check_out_time,
                        $parkingLot->free_time_minutes,
                        $parkingLot->price_per_hour
                    );

                    $slotDailyReport['total_revenue'] += $sessionFee;
                    $slotDailyReport['total_sessions']++;
                    $slotDailyReport['timestart']= $session->check_in_time;
                    $slotDailyReport['timeend']= $session->check_out_time;


                    $today = Carbon::today();

                    // ตรวจสอบว่าเช็คเอาท์ในวันนี้หรือไม่
                    if ($today->format('Y-m-d') === Carbon::parse($session->check_out_time)->format('Y-m-d')) {
                        $weekNumber = $today->weekOfYear;

                        // ตรวจสอบว่ามี index ในอาร์เรย์หรือไม่ ถ้าไม่มีกำหนดค่าเริ่มต้นเป็น 0
                        if (!isset($dayReport[$weekNumber])) {
                            $dayReport[$weekNumber] = 0;
                        }

                        $dayReport[$weekNumber] += $sessionFee;
                    }

                    $weekNumber = Carbon::parse($session->check_out_time)->weekOfYear;
                    $weeklyReport[$weekNumber] += $sessionFee;

                    $monthNumber = Carbon::parse($session->check_out_time)->month;
                    $monthlyReport[$monthNumber] += $sessionFee;
                }
            }

            $dailyReport[] = $slotDailyReport;
        }


        return response()->json([
            'parking_lot' => $parkingLot,
            'dayReport' => $dayReport,
            'weekly_report' => $weeklyReport,
            'monthly_report' => $monthlyReport,
        ]);
    }


}
