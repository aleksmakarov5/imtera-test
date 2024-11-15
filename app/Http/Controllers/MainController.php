<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\budget_item;
use App\Models\deal;
use App\Models\transaction;

class MainController extends Controller
{
    public function index()
    {
        $transactions_date = transaction::all()->sortDesc()->groupBy('Date');


        // dd($transactions);
        return view('dashboard', ['transactions_date' => $transactions_date]);
    }

    public function vypiska()
    {
        return view('vypiska');
    }
    public function file_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|max:2048',
        ]);
        $file = $request['file'];
        $filenameWithExt  = $file->getClientOriginalName(); //
        // Имя и расширение файла
        // Только оригинальное имя файла
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        // Расширение
        $extention = $file->getClientOriginalExtension();
        // Путь для сохранения
        $fileNameToStore = $filename . "_" . date('d-m-y_hms') . "." . $extention;
        $path = $file->storeAs($fileNameToStore);

        $file = file_get_contents('storage/' . $path);
        $file = trim($file);
        $file = mb_convert_encoding($file, 'utf-8', 'windows-1251');
        $file = str_replace('"', "&quot", $file);
        // $file = str_replace('"', ">>", $file);
        $file = str_replace('=', '":"', $file);

        $file = str_replace('ВерсияФормата', 'VersionFormat', $file);

        $file = str_replace('Кодировка', 'Coding', $file);
        $file = str_replace('Отправитель', 'Sender', $file);
        $file = str_replace('ДатаСоздания', 'DateCreate', $file);
        $file = str_replace('ВремяСоздания', 'TimeCreate', $file);
        $file = str_replace('ДатаНачала', 'DateStart', $file);
        $file = str_replace('ДатаКонца', 'DateFinish', $file);
        $file = str_replace('СекцияРасчСчет', ',', $file);
        $file = str_replace('КонецРасчСчет', 'PlatPoruch": [', $file);

        $file = str_replace('РасчСчет', 'RaschSch', $file);
        $file = str_replace('НачальныйОстаток', 'StartBalance', $file);
        $file = str_replace('ВсегоПоступило', 'TotalRecieve', $file);
        $file = str_replace('ВсегоСписано', 'TotalPay', $file);
        $file = str_replace('КонечныйОстаток', 'FinishBalance', $file);
        $file = str_replace('НазначениеПлатежа', 'NazPay', $file);

        $file = str_replace('ПоказательНомера', 'ShowerNumber', $file);
        $file = str_replace('Номер', 'Number', $file);
        $file = str_replace('ДатаПоступило', 'DateRecieve', $file);
        $file = str_replace('ДатаСписано', 'DatePay', $file);
        $file = str_replace('Дата', 'Date', $file);
        $file = str_replace('Сумма', 'Summ', $file);
        $file = str_replace('Плательщик1', 'Payer1', $file);
        $file = str_replace('ПлательщикИНН', 'PayerINN', $file);
        $file = str_replace('ПлательщикСчет', 'PayerSch', $file);
        $file = str_replace('ПлательщикКПП', 'PayerKPP', $file);
        $file = str_replace('ПоказательОснования', 'ShowerOsnov', $file);
        $file = str_replace('ПоказательПериода', 'ShowerPeriod', $file);
        $file = str_replace('ПоказательДаты', 'ShowerDate', $file);
        $file = str_replace('ПоказательТипа', 'ShowerType', $file);
        $file = str_replace('ПоказательКБК', 'ShowerKBK', $file);
        $file = str_replace('ПлательщикБанк1', 'PayerBank1', $file);
        $file = str_replace('ПлательщикБИК', 'PayerBIC', $file);
        $file = str_replace('ПлательщикКорсчет', 'PayerCorSch', $file);
        $file = str_replace('ПоказательНомера', 'ShowerNumber', $file);
        $file = str_replace('ОКАТО', 'OKATO', $file);
        $file = str_replace('КонецФайла', ']', $file);


        $file = str_replace('ПолучательИНН', 'RecieverINN', $file);
        $file = str_replace('ПолучательБанк1', 'RecieverBank1', $file);
        $file = str_replace('ПолучательБИК', 'RecieverBIC', $file);
        $file = str_replace('ПолучательКорсчет', 'RecieverCorSch', $file);
        $file = str_replace('ПолучательИНН', 'RecieverINN', $file);
        $file = str_replace('ПолучательСчет', 'RecieverSch', $file);
        $file = str_replace('ВидОплаты', 'TypePay', $file);
        $file = str_replace('СрокПлатежа', 'SrokPay', $file);
        $file = str_replace('Очередность', 'Ocherednost', $file);
        $file = str_replace('ВидПлатежа', 'TypePay1', $file);
        $file = str_replace('КодНазПлатежа', 'CodeNazPay', $file);
        $file = str_replace('Код', 'Code', $file);
        $file = str_replace('Получатель', 'Reciever', $file);

        $file = str_replace('РежимКонвертации', 'ConversionMode', $file);
        $file = str_replace('СекцияДокумент":"Платежное поручение', '{', $file);
        $file = str_replace('КонецДокумента', '}', $file);
        $file = str_replace('1CClientBankExchange', '{', $file);
        $file = str_replace("\r\n", '","', $file);
        $file = str_replace('{",', '{', $file);
        $file = str_replace(',"}"', '}', $file);
        $file = str_replace(',"]', ']', $file);
        $file = str_replace('"{', '{', $file);
        $file = str_replace(',"",', '', $file);
        $file = str_replace('",",', '', $file);
        $file = str_replace('",{', '{', $file);
        $file =  $file . '}';

        // dd($file);
        // dd(json_decode($file));
        $file = json_decode($file);
        foreach ($file->PlatPoruch as $PlatPoruch) {

            $transaction = new transaction;
            $transaction->Date = date('Y-m-d', strtotime($PlatPoruch->Date));
            $transaction->Summ = $PlatPoruch->Summ;
            if ($PlatPoruch->PayerINN == '4345489100') {
                $transaction->Type = 1;
                $transaction->Kontragent = $PlatPoruch->Reciever1;
            } else {
                $transaction->Type = 0;
                $transaction->Kontragent = $PlatPoruch->Payer1;
            }
            $transaction->NazPay = $PlatPoruch->NazPay;
            $transaction->budget_item_id = (int)$PlatPoruch->TypePay1;
            $transaction->Sch = $file->RaschSch;
            $transaction->deal_id = (int)$PlatPoruch->TypePay;
            $transaction->save();
        }

        // Process the uploaded file here (e.g., import into database)

        return redirect()->route('dashboard');
    }
}