<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\budget_item;
use App\Models\deal;
use App\Models\transaction;
use App\classes\ComplexNumber;

class MainController extends Controller
{
    public function index()
    {
        $transactions_date = transaction::all()->sortBy([['Date', 'desc'], ['id', 'asc']])->groupBy('Date');


        // dd($transactions);
        return view('dashboard', ['transactions_date' => $transactions_date]);
    }

    public function coube()
    {
        return view('coube');
    }
    public function shear()
    {
        return view('shear');
    }
    public function step2(Request $request)
    {
        $a = $request->a ? $request->a : 0;
        $b = $request->b ? $request->b : 0;
        $c = $request->c ? $request->c : 0;
        $x1 = 0;
        $x2 = 0;
        $d = 0;
        if ($a != 0) {
            $d = $b * $b - 4 * $a * $c;
            if ($d > 0) {
                $x1 = (-$b + sqrt($d)) / (2 * $a);
                $x2 = (-$b - sqrt($d)) / (2 * $a);
            }
        } else
        if ($b != 0) {
            $x1 = -$c / $b;
            $x2 = $x1;
            $d = 0;
        }

        return view('coube', ['a' => $a, 'b' => $b, 'c' => $c, 'x1' => $x1, 'x2' => $x2, 'n' => 2, 'd' => $d]);
    }

    public function step3(Request $request)
    {
        $a = $request->a ? $request->a : 0;
        $b = $request->b ? $request->b : 0;
        $c = $request->c ? $request->c : 0;
        // $x1 = new ComplexNumber(0, 0);
        // $x2 = new ComplexNumber(0, 0);
        // $x3 = new ComplexNumber(0, 0);
        // $x4 = new ComplexNumber(0, 0);
        $P = -$a * $a / 3 + $b;
        $Q = -$a / 3 * ($b - 2 * $a * $a / 9) + $c;
        $M = $Q * $Q / 4 + $P * $P * $P / 27;
        $T = sqrt(abs($P * $P * $P / 27));
        $N = sqrt(abs($P / 3));
        if ($M < 0) {
            $Fi = atan(-2 * sqrt(-$M) / $Q);
            if ($Q > 0) {
                $Fi += pi();
            }
            // $x1->real = 2 * $N * cos($Fi / 3);
            // $x1->imaginary = 0;
            // $x2->real = 2 * $N * cos(($Fi + 2 * pi()) / 3);
            // $x2->imaginary = 0;
            // $x3->real = 2 * $N * cos(($Fi + 4 * pi()) / 3);
            // $x3->imaginary = 0;
        }
        if ($M == 0) {
            if ($Q > 0) {
                //     $x1->real = $N;
                //     $x1->imaginary = 0;
                //     $x2 = $x1;
                //     $x3->real = -2 * $N;
                //     $x3->imaginary = 0;
            }
        }
        if ($M > 0) {
            $alf = -$Q / 2 + sqrt($M);
            $bet = -$Q / 2 - sqrt($M);
            if ($alf >= 0)
                $alf = pow($alf, 1 / 3);
            else   $alf = -pow(-$alf, 1 / 3);
            if ($bet >= 0)
                $bet = pow($bet, 1 / 3);
            else   $bet = -pow(-$bet, 1 / 3);
            // $x1->real = $alf + $bet;
            // $x1->imaginary = 0;
            // $x2->real = -$x1->real / 2;
            // $x2->imaginary = sqrt(3) * ($alf - $bet) / 2;
            // $x3->real = -$x1->real / 2;
            // $x3->imaginary = -$x2->imaginary / 2;
        }
        // $x1->real -= $a / 3;
        // $x2->real -= $a / 3;
        // $x3->real -= $a / 3;
        $x1 = 1;
        $x2 = 1;
        $x3 = 1;


        return view('coube', ['a' => $a, 'b' => $b, 'c' => $c, 'x1' => $x1, 'x2' => $x2, 'x3' => $x3, 'n' => 3]);
    }
    public function step4(Request $request)
    {
        $a = $request->a ? $request->a : 0;
        $b = $request->b ? $request->b : 0;
        $c = $request->c ? $request->c : 0;
        $d = $request->d ? $request->d : 0;
        $x1 = new ComplexNumber(0, 0);
        $x2 = new ComplexNumber(0, 0);
        $x3 = new ComplexNumber(0, 0);
        $x4 = new ComplexNumber(0, 0);
        $A0 = -$a / 4;
        $P = -3 * $a * $a * 8 + $b;
        $Q = $a * ($a * $a / 4 - $b) / 2 + $c;
        $R = $a * $a * ($b - 3 * $a * $a / 16) / 16 - $a * $c / 4 + $d;
        $A1 = $P;
        $B1 = $P * $P / 4 - $R;
        $C1 = -$Q * $Q / 8;
        $a = $A1;
        $b = $B1;
        $c = $C1;
        $x1 = new ComplexNumber(0, 0);
        $x2 = new ComplexNumber(0, 0);
        $x3 = new ComplexNumber(0, 0);
        $x4 = new ComplexNumber(0, 0);
        $P = -$a * $a / 3 + $b;
        $Q = -$a / 3 * ($b - 2 * $a * $a / 9) + $c;
        $M = $Q * $Q / 4 + $P * $P * $P / 27;
        $T = sqrt(abs($P * $P * $P / 27));
        $N = sqrt(abs($P / 3));
        if ($M < 0) {
            $Fi = atan(-2 * sqrt(-$M) / $Q);
            if ($Q > 0) {
                $Fi += pi();
            }
            $x1->real = 2 * $N * cos($Fi / 3);
            $x1->imaginary = 0;
            $x2->real = 2 * $N * cos(($Fi + 2 * pi()) / 3);
            $x2->imaginary = 0;
            $x3->real = 2 * $N * cos(($Fi + 4 * pi()) / 3);
            $x3->imaginary = 0;
        }
        if ($M == 0) {
            if ($Q > 0) {
                $x1->real = $N;
                $x1->imaginary = 0;
                $x2 = $x1;
                $x3->real = -2 * $N;
                $x3->imaginary = 0;
            }
        }
        if ($M > 0) {
            $alf = -$Q / 2 + sqrt($M);
            $bet = -$Q / 2 - sqrt($M);
            if ($alf >= 0)
                $alf = pow($alf, 1 / 3);
            else   $alf = -pow(-$alf, 1 / 3);
            if ($bet >= 0)
                $bet = pow($bet, 1 / 3);
            else   $bet = -pow(-$bet, 1 / 3);
            $x1->real = $alf + $bet;
            $x1->imaginary = 0;
            $x2->real = -$x1->real / 2;
            $x2->imaginary = sqrt(3) * ($alf - $bet) / 2;
            $x3->real = -$x1->real / 2;
            $x3->imaginary = -$x2->imaginary / 2;
        }
        $x1->real -= $a / 3;
        $x2->real -= $a / 3;
        $x3->real -= $a / 3;
        $X = $x1->real / 2;
        $M1 = -$X - $P / 2 + $Q / sqrt(16 + $X);
        $M2 = -$X - $P / 2 - $Q / sqrt(16 + $X);
        $x1->real = -sqrt($X) + $A0;
        $x1->imaginary = sqrt(abs($M1));
        $x2->real = $x1->real;
        $x2->imaginary = -sqrt(abs($M1));
        $x3->real = sqrt($X) + $A0;
        $x3->imaginary = sqrt(abs($M2));
        $x4->real = $x3->real;
        $x4->imaginary = -sqrt(abs($M2));
        if ($M1 >= 0) {
            $x1->real += sqrt($M1);
            $x1->imaginary = 0;
            $x2->real -= sqrt($M1);
            $x2->imaginary = 0;
        }
        if ($M2 >= 0) {
            $x3->real += sqrt($M2);
            $x3->imaginary = 0;
            $x4->real -= sqrt($M2);
            $x4->imaginary = 0;
        }
        return view('coube', ['a' => $request->a, 'b' => $request->b, 'c' => $request->c, 'd' => $request->d, 'x1' => $x1, 'x2' => $x2, 'x3' => $x3, 'x4' => $x4, 'n' => 4]);
    }
    public function print(request $request)
    {
        return view('print', [
            'a' => $request->a,
            'b' => $request->b,
            'c' => $request->c,
            'd' => $request->d,
            'x1' => $request->x1,
            'x2' => $request->x2,
            'x3' => $request->x3,
            'x4' => $request->x4,
            'n' => $request->n
        ]);
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
