<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PointLedger;
use App\Models\Transaction;
use App\Support\AuditLogger;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $completed = Transaction::completed()->whereBetween('transaction_date', [$from, $to]);
        $ledger = PointLedger::whereBetween('created_at', [$from, $to]);

        return view('admin.reports.index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'transactionCount' => (clone $completed)->count(),
            'grossSum' => (int) (clone $completed)->sum('gross_amount'),
            'finalSum' => (int) (clone $completed)->sum('final_amount'),
            'pointsEarned' => (int) (clone $ledger)->whereIn('type', [PointLedger::TYPE_EARN, PointLedger::TYPE_ADJUSTMENT_CREDIT])->where('points_delta', '>', 0)->sum('points_delta'),
            'pointsRedeemed' => (int) abs((clone $ledger)->where('type', PointLedger::TYPE_REDEEM)->sum('points_delta')),
            'reversalCount' => Transaction::where('status', Transaction::STATUS_REVERSED)->whereBetween('reversed_at', [$from, $to])->count(),
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $stamp = now()->format('Ymd_His');

        AuditLogger::log('report.export', ['after' => ['type' => $type, 'from' => $from->toDateString(), 'to' => $to->toDateString()]]);

        return match ($type) {
            'customers' => $this->exportCustomers($stamp),
            'transactions' => $this->exportTransactions($from, $to, $stamp),
            'points' => $this->exportLedger($from, $to, $stamp),
            default => abort(404),
        };
    }

    private function exportCustomers(string $stamp): StreamedResponse
    {
        // NOTE: never export password/pin hashes (README §14).
        $headers = ['Member Number', 'Nama', 'Username', 'WhatsApp', 'Status', 'Saldo Poin', 'Tgl Daftar'];

        $rows = function () {
            foreach (Customer::orderBy('name')->cursor() as $c) {
                yield [$c->member_number, $c->name, $c->username, $c->whatsapp_number, $c->status, $c->points_balance, $c->created_at?->toDateString()];
            }
        };

        return CsvExporter::download("customers_{$stamp}.csv", $headers, $rows());
    }

    private function exportTransactions(Carbon $from, Carbon $to, string $stamp): StreamedResponse
    {
        $headers = ['No Transaksi', 'Tanggal', 'Member', 'Nama', 'Gross', 'Diskon', 'Eligible', 'Poin Tukar', 'Diskon Poin', 'Poin Didapat', 'Total Bayar', 'Status'];

        $rows = function () use ($from, $to) {
            $q = Transaction::with('customer')->whereBetween('transaction_date', [$from, $to])->orderBy('transaction_date');
            foreach ($q->cursor() as $t) {
                yield [
                    $t->transaction_number, $t->transaction_date?->toDateTimeString(),
                    $t->customer?->member_number, $t->customer?->name,
                    $t->gross_amount, $t->transaction_discount, $t->eligible_amount,
                    $t->points_redeemed, $t->redemption_discount, $t->points_earned,
                    $t->final_amount, $t->status,
                ];
            }
        };

        return CsvExporter::download("transactions_{$stamp}.csv", $headers, $rows());
    }

    private function exportLedger(Carbon $from, Carbon $to, string $stamp): StreamedResponse
    {
        $headers = ['Tanggal', 'Member', 'Nama', 'Tipe', 'Perubahan', 'Saldo Setelah', 'Keterangan', 'No Transaksi'];

        $rows = function () use ($from, $to) {
            $q = PointLedger::with(['customer', 'transaction'])->whereBetween('created_at', [$from, $to])->orderBy('id');
            foreach ($q->cursor() as $l) {
                yield [
                    $l->created_at?->toDateTimeString(),
                    $l->customer?->member_number, $l->customer?->name,
                    $l->type, $l->points_delta, $l->balance_after,
                    $l->description, $l->transaction?->transaction_number,
                ];
            }
        };

        return CsvExporter::download("point_ledger_{$stamp}.csv", $headers, $rows());
    }

    /** @return array{0:Carbon,1:Carbon} */
    private function range(Request $request): array
    {
        $tz = 'Asia/Jakarta';
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfDay();

        return [$from, $to];
    }
}
