<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Http\Request;
use App\Models\transaction;

class MainController extends Controller
{
    public function delete(Request $request)
    {
        foreach ($request->redactArray as $redactArray) {
            $transaction = transaction::find($redactArray);
            $transaction->delete();
        }
        $transactions_date = transaction::all()->sortBy([['Date', 'desc'], ['id', 'asc']])->groupBy('Date');
        return $transactions_date;
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'Date' => 'required',
            'Summ' => 'required',
            'Type' => 'required',
            'Kontragent' => 'required',
            'NazPay' => 'required',
            'budget_item_id' => 'nullable',
            'Sch' => 'nullable',
            'deal_id' => 'nullable',
        ]);
        $transaction = new transaction;
        $transaction->Date = $request->Date;
        $transaction->Summ = $request->Summ;
        $transaction->Type = $request->Type;
        $transaction->Kontragent = $request->Kontragent;
        $transaction->NazPay = $request->NazPay;
        $transaction->budget_item_id = $request->budget_item_id;
        $transaction->Sch = $request->Sch;
        $transaction->deal_id = $request->deal_id;
        $transaction->status_id = $request->status_id;
        $transaction->save();
        $transactions_date = transaction::all()->sortBy([['Date', 'desc'], ['id', 'asc']])->groupBy('Date');
        return $transactions_date;
    }
    public function copy(Request $request)
    {
        $i = 0;
        foreach ($request->redactArray as $redactArray) {
            $transaction_last = transaction::where('id', '=', $redactArray)->first();
            $transaction = new transaction;
            $transaction->Date = $transaction_last->Date;
            $transaction->Summ = $transaction_last->Summ;
            $transaction->Type = $transaction_last->Type;
            $transaction->Kontragent = $transaction_last->Kontragent;
            $transaction->NazPay = $transaction_last->NazPay;
            $transaction->budget_item_id = $transaction_last->budget_item_id;
            $transaction->Sch = $transaction_last->Sch;
            $transaction->deal_id = $transaction_last->deal_id;
            $transaction->status_id = $transaction_last->status_id;

            $file = 'storage/test' . $i++ . '.txt';
            $file = file_put_contents($file, $transaction);
            $transaction->save();
        }
        $transactions_date = transaction::all()->sortBy([['Date', 'desc'], ['id', 'asc']])->groupBy('Date');
        return $transactions_date;
    }
}