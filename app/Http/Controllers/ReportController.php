<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\LogServiceTitanJob;
use App\Models\Market;
use App\Models\EventName;

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
            if ($user->market_ownership === 'all') {
                $allowed = null;
            } else {
                $allowed = array_filter(array_map('trim', explode(',', $user->market_ownership)));
            }
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

    public function conversionFunnel(Request $request)
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

        $steps = [
            'Job Type & Zip Completed',
            'Appointment Date/Time Selected',
            'New/Repeat Customer Selection',
            'Terms of Service Loaded',
            'Appointment Confirmed',
        ];

        $eventNames = EventName::whereIn('name', $steps)->get()->keyBy('name');
        $stepIds = [];
        foreach ($steps as $s) {
            if (isset($eventNames[$s])) {
                $stepIds[] = (int) $eventNames[$s]->id;
            } else {
                $stepIds[] = null;
            }
        }

        $query = \DB::table('log_events')
            ->select(['market_id', 'session_id', 'event_name_id'])
            ->whereBetween('created_at', [$fromDate->toDateTimeString(), $toDate->toDateTimeString()])
            ->whereNotNull('session_id')
            ->whereIn('event_name_id', array_filter($stepIds));

        if (!empty($marketIds)) {
            $query->whereIn('market_id', $marketIds);
        }

        $rows = $query->get();

        $groups = [];
        foreach ($rows as $r) {
            $m = (string) $r->market_id;
            $s = (string) $r->session_id;
            $eid = (int) $r->event_name_id;
            $groups[$m][$s][$eid] = true;
        }

        $marketIdsFound = !empty($marketIds) ? $marketIds : array_keys($groups);
        $markets = Market::whereIn('id', $marketIdsFound)->pluck('name', 'id')->toArray();

        $marketsData = [];
        $combined = array_fill(0, count($steps), 0);
        $combinedPercentages = array_fill(0, count($steps), 0);

        foreach ($marketIdsFound as $mId) {
            $mKey = (string) $mId;
            $sessions = $groups[$mKey] ?? [];
            $counts = [];
            foreach ($steps as $i => $step) {
                $requiredIds = array_filter(array_slice($stepIds, 0, $i + 1));
                $cnt = 0;
                foreach ($sessions as $sessionEvents) {
                    $has = true;
                    foreach ($requiredIds as $rid) {
                        if (!isset($sessionEvents[$rid])) {
                            $has = false;
                            break;
                        }
                    }
                    if ($has)
                        $cnt++;
                }
                $counts[] = $cnt;
                $combined[$i] += $cnt;
            }

            $percentages = [];
            foreach ($counts as $i => $cnt) {
                if ($i === 0) {
                    $percentages[] = ($cnt > 0) ? 100.0 : 0.0;
                } else {
                    $prev = $counts[$i - 1] ?? 0;
                    $pct = ($prev > 0) ? round(($cnt / $prev) * 100, 2) : 0.0;
                    $percentages[] = $pct;
                }
            }

            $marketsData[] = [
                'id' => $mId,
                'name' => $markets[$mId] ?? ('Market ' . $mId),
                'counts' => $counts,
                'percentages' => $percentages,
            ];
        }

        foreach ($combined as $i => $total) {
            if ($i === 0) {
                $combinedPercentages[$i] = ($total > 0) ? 100.0 : 0.0;
            } else {
                $prev = $combined[$i - 1] ?? 0;
                $combinedPercentages[$i] = ($prev > 0) ? round(($total / $prev) * 100, 2) : 0.0;
            }
        }

        if ($request->query('export') === 'csv') {
            $filename = 'conversion_funnel_' . now()->format('Ymd_His') . '.csv';
            $response = new StreamedResponse(function () use ($marketsData, $steps, $combined) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['market', 'event', 'conversions_total', 'conversions_percentage']);

                foreach ($marketsData as $m) {
                    foreach ($steps as $i => $step) {
                        $total = $m['counts'][$i] ?? 0;
                        if ($i === 0) {
                            $pct = $total > 0 ? 100.0 : 0.0;
                        } else {
                            $prev = $m['counts'][$i - 1] ?? 0;
                            $pct = ($prev > 0) ? round(($total / $prev) * 100, 2) : 0.0;
                        }
                        fputcsv($handle, [$m['name'], $step, $total, $pct]);
                    }
                }

                foreach ($steps as $i => $step) {
                    $total = $combined[$i] ?? 0;
                    if ($i === 0) {
                        $pct = $total > 0 ? 100.0 : 0.0;
                    } else {
                        $prev = $combined[$i - 1] ?? 0;
                        $pct = ($prev > 0) ? round(($total / $prev) * 100, 2) : 0.0;
                    }
                    fputcsv($handle, ['ALL', $step, $total, $pct]);
                }

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            return $response;
        }

        return response()->json([
            'steps' => $steps,
            'markets' => $marketsData,
            'combined' => $combined,
            'combined_percentages' => $combinedPercentages,
        ]);
    }
}
