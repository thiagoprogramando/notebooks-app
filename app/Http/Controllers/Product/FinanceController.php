<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Simulated;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller {
    
    public function index (Request $request) {

        $query = Invoice::query();
        $query->when($request->filled('product_id'), function ($q) use ($request) {
            $q->where('product_id', $request->product_id);
        });
        $query->when($request->filled('simulated_id'), function ($q) use ($request) {
            $q->where('simulated_id', $request->simulated_id);
        });
        $query->when($request->filled('payment_status'), function ($q) use ($request) {
            $q->where('payment_status', $request->payment_status);
        });
        if ($request->filled('date_start') || $request->filled('date_end')) {
            $query->whereBetween('created_at', [
                $request->date_start ? now()->parse($request->date_start)->startOfDay() : now()->subYears(10),
                $request->date_end   ? now()->parse($request->date_end)->endOfDay()   : now(),
            ]);
        }
        if ($request->filled('filter')) {
            $ids = $this->getInvoiceIdsByStatus($request->filter);
            if ($ids !== null) {
                $query->whereIn('id', $ids);
            }
        }

        return view('app.Product.Finance.index', [
            'users'         => User::select('id', 'name')->get(),
            'products'      => Product::select('id', 'name')->get(),
            'simulateds'    => Simulated::select('id', 'title')->get(),
            'invoices'      => $query->paginate(30)->withQueryString(),
            'stats'         => $this->getStats(),
        ]);
    }

    public function update (Request $request, $uuid) {

        $invoice = Invoice::where('uuid', $uuid)->first();
        if (!$invoice) {
            return redirect()->back()->with('infor', 'Fatura não localizada, verifique os dados e tente novamente!');
        }

        if (!empty($request->product_id)) {
            $invoice->product_id = $request->product_id;
        }
        if (!empty($request->simulated_id)) {
            $invoice->simulated_id = $request->simulated_id;
        }
        if (!empty($request->value)) {
            $invoice->value = $this->formatValue($request->value);
        }
        if (!empty($request->payment_status)) {
            $invoice->payment_status = $request->payment_status;
        }

        if ($invoice->save()) {
            return redirect()->back()->with('success', 'Fatura atualizada!');
        }

        return redirect()->back()->with('infor', 'Falha ao atualizar a Fatura, verifique os dados e tente novamente!');
    }

    public function destroy ($uuid) {

        $invoice = Invoice::where('uuid', $uuid)->first();
        if ($invoice && $invoice->delete()) {
            return redirect()->back()->with('success', 'Fatura excluída com sucesso!');
        }

        return redirect()->back()->with('infor', 'Fatura não localizada, verifique os dados e tente novamente!');
    }

    private function formatValue ($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }

    private function productDurationInDays($time) {
        return match ($time) {
            'monthly'      => 30,
            'semi-annual'  => 180,
            'yearly'       => 365,
            'lifetime'     => null,
            default        => 0,
        };
    }

    private function getStats () {
        
        $now = now();

        $latestInvoices = Invoice::query()->select('invoices.*')
            ->join(DB::raw('(
                SELECT MAX(id) as id
                FROM invoices
                GROUP BY user_id, product_id
            ) as latest'), 'latest.id', '=', 'invoices.id')->with('product')->get();

        $stats = [
            'actives'   => 0,
            'inactives' => 0,
            'canceleds' => 0,
        ];

        foreach ($latestInvoices as $invoice) {

            $product = $invoice->product;
            if (!$product) {
                continue;
            }

            if ($product->time === 'lifetime') {
                $stats['actives']++;
                continue;
            }

            $days = match ($product->time) {
                'monthly'     => 30,
                'semi-annual' => 180,
                'yearly'      => 365,
                default       => 0,
            };

            $expiresAt = $invoice->created_at->copy()->addDays($days);
            if ($expiresAt->isFuture()) {
                $stats['actives']++;
                continue;
            }

            $daysExpired = $expiresAt->diffInDays($now);
            if ($daysExpired <= 50) {
                $stats['inactives']++;
            } else {
                $stats['canceleds']++;
            }
        }

        return $stats;
    }

    private function getInvoiceIdsByStatus(?string $filter = null)
{
    if (!$filter) {
        return null;
    }

    $now = now();

    $latestInvoices = Invoice::query()
        ->select('invoices.*')
        ->join(DB::raw('(
            SELECT MAX(id) as id
            FROM invoices
            GROUP BY user_id, product_id
        ) as latest'), 'latest.id', '=', 'invoices.id')
        ->with('product')
        ->get();

    $ids = [];

    foreach ($latestInvoices as $invoice) {

        $product = $invoice->product;

        if (!$product) {
            continue;
        }

        // lifetime = sempre ativo
        if ($product->time === 'lifetime') {
            if ($filter === 'actives') {
                $ids[] = $invoice->id;
            }
            continue;
        }

        $days = match ($product->time) {
            'monthly'     => 30,
            'semi-annual' => 180,
            'yearly'      => 365,
            default       => 0,
        };

        $expiresAt = $invoice->created_at->copy()->addDays($days);

        if ($expiresAt->isFuture()) {
            if ($filter === 'actives') {
                $ids[] = $invoice->id;
            }
            continue;
        }

        $daysExpired = $expiresAt->diffInDays($now);

        if ($daysExpired <= 50 && $filter === 'inactives') {
            $ids[] = $invoice->id;
        }

        if ($daysExpired > 50 && $filter === 'canceleds') {
            $ids[] = $invoice->id;
        }
    }

    return $ids;
}
}
