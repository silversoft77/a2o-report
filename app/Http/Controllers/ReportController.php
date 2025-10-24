<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\LogServiceTitanJob;
use App\Models\Market;

class ReportController extends Controller
{
    public function jobBookings(Request $request)
    {
        $request->validate([
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date|after_or_equal:fromDate',
            'markets' => 'nullable|string',
            'export' => 'nullable|string',
        ]);

        $toDate = $request->input('toDate') ? \Carbon\Carbon::parse($request->input('toDate'))->endOfDay() : now();
        $fromDate = $request->input('fromDate') ? \Carbon\Carbon::parse($request->input('fromDate'))->startOfDay() : now()->subDays(29)->startOfDay();

        $requested = $request->filled('markets')
            ? array_filter(array_map('trim', explode(',', $request->input('markets'))))
            : null;

        $user = $request->user();

        $allowed = null;
        if ($user && !empty($user->market_ownership)) {
            $allowed = array_filter(array_map('trim', explode(',', $user->market_ownership)));
        } elseif ($user) {
            $allowed = $user->markets()->pluck('id')->map(fn($v) => (string) $v)->toArray();
        }

        if ($requested !== null) {
            if ($allowed !== null) {
                $marketIds = array_values(array_intersect($requested, $allowed));
            } else {
                $marketIds = $requested;
            }
        } else {
            if ($allowed !== null) {
                $marketIds = $allowed;
            } else {
                $marketIds = [];
            }
        }

        $query = LogServiceTitanJob::query()
            ->select([
                'market_id',
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as bookings')
            ])
            ->whereBetween('created_at', [$fromDate->toDateTimeString(), $toDate->toDateTimeString()])
            ->groupBy('market_id', DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc');

        if (!empty($marketIds)) {
            $query->whereIn('market_id', $marketIds);
        }

        $rows = $query->get()->toArray();

        $markets = Market::whereIn('id', array_unique(array_column($rows, 'market_id')))->pluck('name', 'id')->toArray();

        $period = new \DatePeriod(
            new \DateTime($fromDate->toDateString()),
            new \DateInterval('P1D'),
            (new \DateTime($toDate->toDateString()))->modify('+1 day')
        );

        $categories = [];
        foreach ($period as $dt) {
            $categories[] = $dt->format('Y-m-d');
        }

        $dataMap = [];
        foreach ($rows as $r) {
            $m = (string) $r['market_id'];
            $d = $r['date'];
            $dataMap[$m][$d] = (int) $r['bookings'];
        }

        $series = [];
        foreach ($marketIds as $mId) {
            $name = $markets[$mId] ?? ('Market ' . $mId);
            $data = [];
            foreach ($categories as $cat) {
                $data[] = $dataMap[$mId][$cat] ?? 0;
            }
            $series[] = ['name' => $name, 'data' => $data];
        }

        if ($request->query('export') === 'csv') {
            $filename = 'job_bookings_' . now()->format('Ymd_His') . '.csv';
            $response = new StreamedResponse(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['market', 'date', 'bookings']);
                foreach ($rows as $r) {
                    fputcsv($handle, [$r['market_id'], $r['date'], $r['bookings']]);
                }
                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            return $response;
        }

        return response()->json([
            'categories' => $categories,
            'series' => $series,
        ]);
    }
}
