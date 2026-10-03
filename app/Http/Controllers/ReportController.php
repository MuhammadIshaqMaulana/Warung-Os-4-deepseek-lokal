<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Laporan keuangan (harian & bulanan).
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type') === 'monthly' ? 'monthly' : 'daily';

        [$start, $end, $label] = $this->resolveRange($request, $type);

        $base = fn () => Transaction::where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end]);

        $paidIds = $base()->paid()->pluck('id');

        $revenue = $base()->paid()->sum('total_price');
        $transactionCount = $paidIds->count();
        $pendingAmount = $base()->pending()->sum('total_price');
        $failedCount = $base()->where('status', Transaction::STATUS_FAILED)->count();

        $profit = 0.0;
        $itemsSold = 0;

        if ($paidIds->isNotEmpty()) {
            $aggregates = TransactionDetail::whereIn('transaction_id', $paidIds)
                ->join('products', 'products.id', '=', 'transaction_details.product_id')
                ->selectRaw(
                    'COALESCE(SUM((transaction_details.price - products.buy_price) * transaction_details.quantity), 0) as profit,
                     COALESCE(SUM(transaction_details.quantity), 0) as items'
                )
                ->first();

            $profit = (float) ($aggregates->profit ?? 0);
            $itemsSold = (int) ($aggregates->items ?? 0);
        }

        $byMethod = $base()
            ->paid()
            ->selectRaw('method, COUNT(*) as total, COALESCE(SUM(total_price), 0) as amount')
            ->groupBy('method')
            ->get()
            ->keyBy('method');

        $series = $this->buildSeries(
            $base()->paid()->with('details.product')->orderBy('created_at')->get(),
            $start,
            $type
        );

        $bestSellers = $paidIds->isEmpty() ? collect() : TransactionDetail::whereIn('transaction_id', $paidIds)
            ->join('products', 'products.id', '=', 'transaction_details.product_id')
            ->selectRaw(
                'products.id as product_id,
                 products.name as name,
                 products.category as category,
                 SUM(transaction_details.quantity) as qty,
                 SUM(transaction_details.price * transaction_details.quantity) as revenue,
                 SUM((transaction_details.price - products.buy_price) * transaction_details.quantity) as profit'
            )
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderByDesc('qty')
            ->limit(8)
            ->get();

        $recent = Transaction::where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->with('details.product')
            ->latest()
            ->limit(12)
            ->get();

        $stats = [
            'revenue' => $revenue,
            'profit' => $profit,
            'transactions' => $transactionCount,
            'items' => $itemsSold,
            'avg' => $transactionCount > 0 ? $revenue / $transactionCount : 0,
            'pending' => $pendingAmount,
            'failed' => $failedCount,
            'cash' => $byMethod->get(Transaction::METHOD_CASH),
            'qris' => $byMethod->get(Transaction::METHOD_QRIS),
        ];

        return view('reports.index', compact(
            'type',
            'start',
            'end',
            'label',
            'stats',
            'series',
            'bestSellers',
            'recent'
        ));
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function resolveRange(Request $request, string $type): array
    {
        if ($type === 'monthly') {
            $month = $request->query('month', now()->format('Y-m'));

            if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
                $month = now()->format('Y-m');
            }

            $start = Carbon::parse($month.'-01')->startOfDay();
            $end = (clone $start)->endOfMonth();

            return [$start, $end, $start->translatedFormat('F Y')];
        }

        $date = $request->query('date', now()->format('Y-m-d'));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = now()->format('Y-m-d');
        }

        $start = Carbon::parse($date)->startOfDay();
        $end = (clone $start)->endOfDay();

        return [$start, $end, $start->translatedFormat('d F Y')];
    }

    /**
     * Rekap per jam (harian) atau per hari (bulanan).
     */
    protected function buildSeries($transactions, Carbon $start, string $type): Collection
    {
        $buckets = collect();

        if ($type === 'monthly') {
            $days = $start->daysInMonth();

            for ($day = 1; $day <= $days; $day++) {
                $key = $start->copy()->day($day)->format('Y-m-d');
                $buckets->put($key, [
                    'label' => (string) $day,
                    'revenue' => 0.0,
                    'profit' => 0.0,
                    'count' => 0,
                ]);
            }

            foreach ($transactions as $transaction) {
                $key = $transaction->created_at->format('Y-m-d');
                if (! $buckets->has($key)) {
                    continue;
                }

                $bucket = $buckets->get($key);
                $bucket['revenue'] += (float) $transaction->total_price;
                $bucket['profit'] += $transaction->profit();
                $bucket['count']++;
                $buckets->put($key, $bucket);
            }

            return $buckets->values();
        }

        for ($hour = 0; $hour < 24; $hour++) {
            $buckets->put($hour, [
                'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).'.00',
                'revenue' => 0.0,
                'profit' => 0.0,
                'count' => 0,
            ]);
        }

        foreach ($transactions as $transaction) {
            $hour = (int) $transaction->created_at->format('G');
            $bucket = $buckets->get($hour);
            $bucket['revenue'] += (float) $transaction->total_price;
            $bucket['profit'] += $transaction->profit();
            $bucket['count']++;
            $buckets->put($hour, $bucket);
        }

        return $buckets->values();
    }
}
