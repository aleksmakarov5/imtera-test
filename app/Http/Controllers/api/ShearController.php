<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\shear;
use App\Models\vertex;
use App\Models\edge;
use App\Models\matrix;
use PhpOffice\PhpWord\TemplateProcessor;

define("Mat", 85); // Максимальное количество рёбер


class ShearController extends Controller
{


    // Global Variables



    // In Pascal, St, St1, St2 are arrays of strings. We simulate as 1-indexed arrays.


    public function save(Request $request)
    {
        $shears = shear::all();
        foreach ($shears as $shear) {
            $shear->delete();
            $vertex = vertex::where('shear_id', $shear->id)->delete();
            $edge = edge::where('shear_id', $shear->id)->delete();
        }
        $matrix = matrix::all();
        foreach ($matrix as $matrix) {
            $matrix->delete();
        }
        $data = $request->validate([
            'M' => 'required|numeric',
            'K' => 'required|numeric',
            'z' => 'required|array',
            'y' => 'required|array',
            'i1' => 'required|array',
            'i2' => 'required|array',
            'h' => 'required|array',
            'k' => 'required|array',
            'c' => 'required|array',
            'Msw' => 'nullable|numeric',
            'Nsw' => 'nullable|numeric',

        ]);
        $shear = new shear;
        $shear->M = $data['M'];
        $shear->K = $data['K'];
        $shear->Msw = $data['Msw'] ? $data['Msw'] : 0;
        $shear->Nsw = $data['Nsw'] ? $data['Nsw'] : 0;
        $shear->save();
        $i = 0;

        foreach ($data['z'] as $key => $value) {
            $vertex = new vertex;
            $vertex->z = $data['z'][$i];
            $vertex->y = $data['y'][$i];
            $vertex->npp = $i;
            $vertex->shear_id = $shear->id;
            $vertex->save();
            $i++;
        }

        for ($i = 0; $i < count($data['i1']); $i++) {
            $edge = new edge;
            $edge->i1 = $data['i1'][$i];
            $edge->i2 = $data['i2'][$i];
            $edge->h = $data['h'][$i];
            $edge->k = $data['k'][$i];
            $edge->npp = $i;
            $edge->shear_id = $shear->id;
            $edge->save();
            for ($j = 0; $j < count($data['c'][$i]); $j++) {
                $matrix = new matrix;
                $matrix->c = $data['c'][$i][$j];
                $matrix->edge_id = $edge->id;
                $matrix->save();
            }
        }
        $vertex = vertex::where('shear_id', $shear->id)->get();
        $edge = edge::where('shear_id', $shear->id)->get();
        foreach ($edge as $ed) {
            $c = matrix::where('edge_id', $ed->id)->get();
            $ed->c = $c;
        }
        return [$data];
    }
    public function load()
    {
        $shear = shear::first();
        $vertex = vertex::where('shear_id', $shear->id)->get();
        $edge = edge::where('shear_id', $shear->id)->get();
        foreach ($edge as $ed) {
            $c = matrix::where('edge_id', $ed->id)->get();
            $ed->c = $c;
        }
        return [$shear, $vertex, $edge];
    }
    public function calculate(Request $request)
    {
        $data = $request->validate([
            'M' => 'required|numeric',
            'K' => 'required|numeric',
            'z' => 'required|array',
            'y' => 'required|array',
            'i1' => 'required|array',
            'i2' => 'required|array',
            'c' => 'required|array',
            'h' => 'required|array',
            'k' => 'required|array',
            'Msw' => 'nullable|numeric',
            'Nsw' => 'nullable|numeric',
        ]);

        $M = 0; // количество ребер (number of edges)
        $K = 0; // количество контуров (number of contours)
        $N = 0; // количество вершин (number of vertices)

        $Bn = $this->new_array(Mat); // Ордината вершины
        $Yn = $this->new_array(Mat); // Абсцисса вершины
        $Tm = $this->new_array(Mat); // Приведенная толщина ребра
        $ks = $this->new_array(Mat); // Коэффициент приведения толщины

        $C = $this->new_matrix($M, $M, 0.0); // Матрица индексов

        $Bm = $this->new_array(Mat); //Инициализационная точка ординаты ребра
        $Ym = $this->new_array(Mat); //Инициализационная точка абсциссы ребра
        $Q1 = $this->new_array(Mat); //Ориентация ребра
        $QL = $this->new_array(Mat); //Длина ребра
        for ($i = 0; $i < count($data['i1']); $i++) {
            $C[$i][0] = $data['i1'][$i];
            $C[$i][1] = $data['i2'][$i];
            for ($j = 0; $j < count($data['c'][$i]); $j++) {
                $C[$i][$j + 2] = $data['c'][$i][$j];
            }
            $Tm[$i] = $data['h'][$i];
            $ks[$i] = $data['k'][$i];
        }
        for ($i = 0; $i < count($data['z']); $i++) {
            $Bn[$i] = $data['z'][$i];
            $Yn[$i] = $data['y'][$i];
        }
        $M = $data['M'];
        $K = $data['K'];
        $N = $M - $K + 1;

        // In Pascal, C is declared as pointer to MatrMas; here we simulate it as a 2D array.
        $X1 = $this->new_array(Mat); // will be allocated as an array of double (1D array)
        $X2 = $this->new_array(Mat); // same as above

        $B0 = 0.0; //отстояние н.о. Y  от о.с. , СМ
        $Y0 = 0.0; //отстояние н.о. Z  от о.с., СМ
        $F = 0.0; //Площадь поперечного сечения, см2
        $Iy0 = 0.0; //момент инерции относительно оси Y, СМ4
        $Msw = 0.0; //изгибающий момент, кгс*см
        $Msw = $data['Msw'] ? $data['Msw'] : 0;

        $Nsw = 0.0; //перерезывающую силу, кгс
        $Nsw = $data['Nsw'] ? $data['Nsw'] : 0;

        $Iz0 = 0.0; //момент инерции относительно оси Z, СМ4
        $OM = 0.0; //Приведенная площадь по сдвигу, см2
        $G = $this->new_matrix($M, $M, 0.0);
        $Nx = 0;
        $a = 0.0;
        $b = 0.0;
        $Fi = $this->new_array($M, 0.0);
        // Формирование матрицы инцидентности
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $N; $J++) {
                if ($J == ($C[$I][0] - 1)) {
                    $G[$J][$I] = 1;
                }
                if ($J == ($C[$I][1] - 1)) {
                    $G[$J][$I] = -1;
                }
            }
        }
        // Ориентация ребер
        for ($I = 0; $I < $N; $I++) {
            for ($J = 0; $J < $M; $J++) {
                if ($G[$I][$J] > 0) {
                    $Bm[$J] = $Bn[$I];
                    $Ym[$J] = $Yn[$I];  // Note: In the Pascal code, Yn is used here. Using Bn for simulation.
                    $Nx = round($C[$J][1] - 1);
                    $a = $Yn[$Nx] - $Ym[$J];
                    $b = $Bn[$Nx] - $Bm[$J];
                }
                if ($G[$I][$J] < 0) {
                    $Nx = round($C[$J][0] - 1);
                    $Bm[$J] = $Bn[$Nx];
                    $Ym[$J] = $Yn[$Nx];  // Using Bn in place of Yn as per provided code structure.
                    $a = $Yn[$I] - $Ym[$J];
                    $b = $Bn[$I] - $Bm[$J];
                }
                if ($G[$I][$J] == 0)
                    continue;
                $QL[$J] = sqrt($a * $a + $b * $b);
                if ($b == 0) {
                    if ($a > 0) {
                        $Fi[$J] = M_PI / 2;
                    } else {
                        $Fi[$J] = -M_PI / 2;
                    }
                }
                if ($b < 0) {
                    if ($a >= 0) {
                        $Fi[$J] = M_PI + atan($a / $b);
                    } else {
                        $Fi[$J] = -M_PI + atan($a / $b);
                    }
                }
                if ($b > 0) {
                    $Fi[$J] = atan($a / $b);
                }
                $Q1[$J] = M_PI - $Fi[$J];
            }
        }
        $G = $this->new_matrix($M, $M, 0.0);
        $As = $this->new_matrix($M, $M, 0.0);
        $Au = $this->new_matrix($M, $M, 0.0);
        $AA = $this->new_matrix($M, $M, 0.0);
        $Nt = $this->new_matrix($M, $M, 0.0);

        // One dimensional arrays of length Mat (simulate MD)
        $X = $this->new_array($M, 0.0);
        $FI = $this->new_array($M, 0.0);
        $S = $this->new_array($M, 0.0);
        $Um = $this->new_array($M, 0.0);
        $T0m = $this->new_array($M, 0.0);
        $Bs = $this->new_array($M, 0.0);
        $L = $this->new_array($M, 0.0);
        $BB = $this->new_array($M, 0.0);
        $Bu = $this->new_array($M, 0.0);
        $Hm = $this->new_array($M, 0.0);
        $A2k = $this->new_array($M, 0.0);
        $Fm = $this->new_array($M, 0.0);
        $Sym = $this->new_array($M, 0.0);
        $Szm = $this->new_array($M, 0.0);
        $Iym = $this->new_array($M, 0.0);
        $Izm = $this->new_array($M, 0.0);
        $Iyzm = $this->new_array($M, 0.0);
        $Psim = $this->new_array($M, 0.0);
        $Un = $this->new_array($M, 0.0);
        $DU = $this->new_array($M, 0.0);

        // X1 and X2 passed as MD are assumed to be allocated already by caller.
        // For memory check simulation we ignore MaxAvail conditions.

        // Display dynamic memory (simulated)
        // GotoXY(15, 7);
        // echo "Остаток динамической памяти : " . PHP_INT_MAX . "\n\n";

        // Zero out matrices AA, Nt, G, As, Au
        $this->ZerMatr($AA, $M);
        $this->ZerMatr($Nt, $M);
        $this->ZerMatr($G, $M);
        $this->ZerMatr($As, $M);
        $this->ZerMatr($Au, $M);

        $F = 0;
        $Sy = 0;
        $Sz = 0;
        $Iy = 0;
        $Iz = 0;
        $Iyz = 0;

        // Global variables used: $M, $N, $Tm, $ks, $C, $Q1, $QL, $Bm, $Ym, $Bn
        // global $M, $N, $Tm, $ks, $C, $Q1, $QL, $Bm, $Ym, $Bn;

        // Формирование матрицы инцидентности с учетом фактической толщины
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $N; $J++) {
                if ($J == $C[$I][0] - 1) {
                    $G[$J][$I] = 1;
                }
                if ($J == $C[$I][1] - 1) {
                    $G[$J][$I] = -1;
                }
                $As[$J][$I] = $G[$J][$I] * $Tm[$I] / $ks[$I];
            }
        }
        // Nt^(K, M) - транспонированная матрица контуров
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $K; $J++) {
                $Nt[$J][$I] = $C[$I][$J + 2];
            }
        }

        for ($I = 0; $I < $M; $I++) {
            $FI[$I] = M_PI - $Q1[$I]; // Note: In Pascal, FI:=Pi-Q1
            $C1 = cos($FI[$I]);
            $S1 = sin($FI[$I]);
            $Fm[$I] = $Tm[$I] * $QL[$I];
            $Bm0 = $Bm[$I] + $QL[$I] * $C1 / 2;
            $Ym0 = $Ym[$I] + $QL[$I] * $S1 / 2;
            $Sym[$I] = $Fm[$I] * $Bm0;
            $Szm[$I] = $Fm[$I] * $Ym0;
            $Iym[$I] = $Tm[$I] * $this->Step($QL[$I], 3) * $C1 * $C1 / 12;
            $Iym[$I] = $Iym[$I] + $this->Step($Tm[$I], 3) * $QL[$I] * $S1 * $S1 / 12;
            $Iym[$I] = $Iym[$I] + $Fm[$I] * $Bm0 * $Bm0;
            $Izm[$I] = $Tm[$I] * $this->Step($QL[$I], 3) * $S1 * $S1 / 12;
            $Izm[$I] = $Izm[$I] + $this->Step($Tm[$I], 3) * $QL[$I] * $C1 * $C1 / 12;
            $Izm[$I] = $Izm[$I] + $Fm[$I] * $Ym0 * $Ym0;
            $Iyzm[$I] = ($this->Step($QL[$I], 2) / 12 - $this->Step($Tm[$I], 2) / 12) * $S1 * $C1;
            $Iyzm[$I] = $Fm[$I] * ($Iyzm[$I] + $Bm0 * $Ym0);
            $F += $Fm[$I];
            $Sy += $Sym[$I];
            $Sz += $Szm[$I];
            $Iy += $Iym[$I];
            $Iz += $Izm[$I];
            $Iyz += $Iyzm[$I];
        }
        $B0 = $Sy / $F;
        $Y0 = $Sz / $F;
        $Iy0 = $Iy - $B0 * $Sy;
        $Iz0 = $Iz - $Y0 * $Sz;

        for ($I = 0; $I < $M; $I++) {
            $B0m = $Bm[$I] - $B0;
            $C1 = cos(($Q1[$I] + $QL[$I]) / 2);
            $S1 = sin(($QL[$I] - $Q1[$I]) / 2);
            $S2 = sin(($QL[$I] + $Q1[$I]) / 2);
            $S[$I] = $QL[$I] * ($B0m + $QL[$I] * cos($FI[$I]) / 2);
            $Um[$I] = $QL[$I] * $QL[$I] * ($B0m / 2 + $QL[$I] * cos($FI[$I]) / 6);
            $T0m[$I] = $B0m * $B0m * $this->Step($QL[$I], 3) / 3;
            $T0m[$I] = $T0m[$I] + $B0m * $this->Step($QL[$I], 4) * cos($FI[$I]) / 4;
            $T0m[$I] = $T0m[$I] + $this->Step($QL[$I], 5) * cos($FI[$I]) * cos($FI[$I]) / 20;
        }
        for ($I = 0; $I < ($M - $K); $I++) {
            $A = 0;
            for ($J = 0; $J < $M; $J++) {
                $A += $G[$I][$J] * $S[$J] * $Tm[$J] * (1 - $G[$I][$J]) / 2;
            }
            $Bs[$I] = $A;
        }
        for ($I = 0; $I < $K; $I++) {
            for ($J = 0; $J < $M; $J++) {
                $L[$J] = $QL[$J];
                $Au[$I][$J] = $Nt[$I][$J] * $L[$J];
            }
        }

        for ($I = 0; $I < $M; $I++) {
            $Um[$I] = $Um[$I] * $ks[$I];
        }
        for ($I = 0; $I < $M; $I++) {
            $Bu[$I] = 0;
            for ($J1 = 0; $J1 < $M; $J1++) {
                $Bu[$I] += $Nt[$I][$J1] * $Um[$J1];
            }
        }

        // Формирование разрешающей матрицы
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $M; $J++) {
                if ($I < ($M - $K)) {
                    $AA[$I][$J] = $As[$I][$J];
                } else {
                    $AA[$I][$J] = $Au[$I - $M + $K][$J];
                }
            }
            if ($I < ($M - $K)) {
                $BB[$I] = $Bs[$I];
            } else {
                $BB[$I] = $Bu[$I - $M + $K];
            }
        }
        // Solve system using Gauss elimination.

        $this->Gauss($M, $AA, $BB, $X1);
        $T = 0;
        for ($I = 0; $I < $M; $I++) {
            $T += $Tm[$I] * ($X1[$I] * $X1[$I] * $L[$I] / $ks[$I]
                - 2 * $X1[$I] * $Um[$I] / $ks[$I] + $ks[$I] * $T0m[$I]);
        }

        for ($I = 0; $I < $M; $I++) {
            $X2[$I] = $X1[$I] - $S[$I] * $ks[$I];
        }


        $OM = ($Iy0 * $Iy0) / $T;
        $Q1_rez = [];
        $Tm_rez = [];
        $Xt = [];
        $Xt2 = [];

        $T1 = [];
        $T2 = [];
        $Sigbn1 = [];
        $Sigbn2 = [];
        for ($I = 0; $I < $M; $I++) {
            $Sigbn1[$I] = $Msw / $Iy0 * ($Bm[$I] - $B0);
            $Sigbn2[$I] = $Msw / $Iy0 * (($Bm[$I] + $QL[$I] * cos(M_PI - $Q1[$I])) - $B0);
            $Xt[$I] = $Nsw * $X1[$I] / $Iy0;
            $Xt2[$I] = $Nsw * $X2[$I] / $Iy0;
            $T1[$I] = $Xt[$I] * $Tm[$I] / $ks[$I];
            $T2[$I] = $Xt2[$I] * $Tm[$I] / $ks[$I];
            $Q1_rez[$I] = 180 - $Q1[$I] * 180 / M_PI;
            $Tm_rez[$I] = $Tm[$I] / $ks[$I];
        }

        // Dispose dynamically allocated arrays by unsetting them.
        return [
            $F,
            $Q1_rez,
            $Tm_rez,
            $OM,
            $Iy0,
            $Iz0,
            $B0,
            $Y0,
            $Bn,
            $Bm,
            $Yn,
            $Ym,
            $QL,
            $Tm,
            $ks,
            $C,
            $Xt,
            $Xt2,
            $T1,
            $T2,
            $Sigbn1,
            $Sigbn2,

        ];
    }
    private function str2number($str)
    {
        $str = str_replace(',', '.', preg_replace('/[^,.0-9]/', '', $str));
        if (!$str)
            return '0.0';
        else
            return round((float)($str), 2);
    }
    public function save_file(request $request)
    {
        $res = $this->calculate($request);
        // dd($res[15][0]);
        $location = "storage/file.docx";
        $templateProcessor = new TemplateProcessor('storage/doc_template/template.docx');
        $templateProcessor->setValue('F', $this->str2number($res[0]));
        $templateProcessor->setValue('OM', $this->str2number($res[3]));
        $templateProcessor->setValue('Iy0', $this->str2number($res[4]));
        $templateProcessor->setValue('Iz0', $this->str2number($res[5]));
        $templateProcessor->setValue('B0', $this->str2number($res[6]));
        $templateProcessor->setValue('Y0', $this->str2number($res[7]));
        $array = [];
        foreach ($res[15] as $key => $value) {
            $array[] = array(
                'i' => $key + 1,
                'i1' => $res[15][$key][0],
                'i2' => $res[15][$key][1],
                'Zm' => $this->str2number($res[9][$key] ? $res[9][$key] : 0),
                'Ym' => $this->str2number($res[11][$key] ? $res[11][$key] : 0),
                'Fi' => $this->str2number($res[1][$key] ? $res[1][$key] : 0),
                'Tm' => $this->str2number($res[2][$key] ? $res[2][$key] : 0),
                'Hm' => $this->str2number($res[13][$key] ? $res[13][$key] : 0),
                'Km' => $this->str2number($res[14][$key] ? $res[14][$key] : 0),
                'Lm' => $this->str2number($res[12][$key] ? $res[12][$key] : 0)
            );
        }
        $templateProcessor->cloneRowAndSetValues('i', $array);

        if (file_exists($location)) {
            unlink($location);
        }
        $templateProcessor->saveAs($location);
        return response()->download($location);
    }
    public function calculate_test()
    {
        $res = $this->load();

        $M = 0; // количество ребер (number of edges)
        $K = 0; // количество контуров (number of contours)
        $N = 0; // количество вершин (number of vertices)

        $Bn = $this->new_array(Mat); // Ордината вершины
        $Yn = $this->new_array(Mat); // Абсцисса вершины
        $Tm = $this->new_array(Mat); // Приведенная толщина ребра
        $ks = $this->new_array(Mat); // Коэффициент приведения толщины

        $C = $this->new_matrix($M, $M, 0.0); // Матрица индексов

        $Bm = $this->new_array(Mat); //Инициализационная точка ординаты ребра
        $Ym = $this->new_array(Mat); //Инициализационная точка абсциссы ребра
        $Q1 = $this->new_array(Mat); //Ориентация ребра
        $QL = $this->new_array(Mat); //Длина ребра
        for ($i = 0; $i < count($res[2]); $i++) {
            $C[$i][0] = $res[2][$i]->i1;
            $C[$i][1] = $res[2][$i]->i2;
            for ($j = 0; $j < count($res[2][$i]->c); $j++) {
                $C[$i][$j + 2] = $res[2][$i]->c[$j]->c;
            }
            $Tm[$i] = $res[2][$i]->h;
            $ks[$i] = $res[2][$i]->k;
        }
        for ($i = 0; $i < count($res[1]); $i++) {
            $Bn[$i] = $res[1][$i]->z;
            $Yn[$i] = $res[1][$i]->y;
        }
        $M = $res[0]->M;
        $K = $res[0]->K;
        $N = $M - $K + 1;

        // In Pascal, C is declared as pointer to MatrMas; here we simulate it as a 2D array.
        $X1 = $this->new_array(Mat); // will be allocated as an array of double (1D array)
        $X2 = $this->new_array(Mat); // same as above

        $B0 = 0.0; //отстояние н.о. Y  от о.с. , СМ
        $Y0 = 0.0; //отстояние н.о. Z  от о.с., СМ
        $F = 0.0; //Площадь поперечного сечения, см2
        $Iy0 = 0.0; //момент инерции относительно оси Y, СМ4
        $Msw = 0.0; //изгибающий момент, кгс*см
        $Msw = $res[0]->Msw;

        $Nsw = 0.0; //перерезывающую силу, кгс
        $Nsw = $res[0]->Nsw;

        $Iz0 = 0.0; //момент инерции относительно оси Z, СМ4
        $OM = 0.0; //Приведенная площадь по сдвигу, см2
        $G = $this->new_matrix($M, $M, 0.0);
        $Nx = 0;
        $a = 0.0;
        $b = 0.0;
        $Fi = $this->new_array($M, 0.0);
        // Формирование матрицы инцидентности
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $N; $J++) {
                if ($J == ($C[$I][0] - 1)) {
                    $G[$J][$I] = 1;
                }
                if ($J == ($C[$I][1] - 1)) {
                    $G[$J][$I] = -1;
                }
            }
        }
        // Ориентация ребер
        for ($I = 0; $I < $N; $I++) {
            for ($J = 0; $J < $M; $J++) {
                if ($G[$I][$J] > 0) {
                    $Bm[$J] = $Bn[$I];
                    $Ym[$J] = $Yn[$I];  // Note: In the Pascal code, Yn is used here. Using Bn for simulation.
                    $Nx = round($C[$J][1] - 1);
                    $a = $Yn[$Nx] - $Ym[$J];
                    $b = $Bn[$Nx] - $Bm[$J];
                }
                if ($G[$I][$J] < 0) {
                    $Nx = round($C[$J][0] - 1);
                    $Bm[$J] = $Bn[$Nx];
                    $Ym[$J] = $Yn[$Nx];  // Using Bn in place of Yn as per provided code structure.
                    $a = $Yn[$I] - $Ym[$J];
                    $b = $Bn[$I] - $Bm[$J];
                }
                if ($G[$I][$J] == 0)
                    continue;
                $QL[$J] = sqrt($a * $a + $b * $b);
                if ($b == 0) {
                    if ($a > 0) {
                        $Fi[$J] = M_PI / 2;
                    } else {
                        $Fi[$J] = -M_PI / 2;
                    }
                }
                if ($b < 0) {
                    if ($a >= 0) {
                        $Fi[$J] = M_PI + atan($a / $b);
                    } else {
                        $Fi[$J] = -M_PI + atan($a / $b);
                    }
                }
                if ($b > 0) {
                    $Fi[$J] = atan($a / $b);
                }
                $Q1[$J] = M_PI - $Fi[$J];
            }
        }
        $G = $this->new_matrix($M, $M, 0.0);
        $As = $this->new_matrix($M, $M, 0.0);
        $Au = $this->new_matrix($M, $M, 0.0);
        $AA = $this->new_matrix($M, $M, 0.0);
        $Nt = $this->new_matrix($M, $M, 0.0);

        // One dimensional arrays of length Mat (simulate MD)
        $X = $this->new_array($M, 0.0);
        $FI = $this->new_array($M, 0.0);
        $S = $this->new_array($M, 0.0);
        $Um = $this->new_array($M, 0.0);
        $T0m = $this->new_array($M, 0.0);
        $Bs = $this->new_array($M, 0.0);
        $L = $this->new_array($M, 0.0);
        $BB = $this->new_array($M, 0.0);
        $Bu = $this->new_array($M, 0.0);
        $Hm = $this->new_array($M, 0.0);
        $A2k = $this->new_array($M, 0.0);
        $Fm = $this->new_array($M, 0.0);
        $Sym = $this->new_array($M, 0.0);
        $Szm = $this->new_array($M, 0.0);
        $Iym = $this->new_array($M, 0.0);
        $Izm = $this->new_array($M, 0.0);
        $Iyzm = $this->new_array($M, 0.0);
        $Psim = $this->new_array($M, 0.0);
        $Un = $this->new_array($M, 0.0);
        $DU = $this->new_array($M, 0.0);

        // X1 and X2 passed as MD are assumed to be allocated already by caller.
        // For memory check simulation we ignore MaxAvail conditions.

        // Display dynamic memory (simulated)
        // GotoXY(15, 7);
        // echo "Остаток динамической памяти : " . PHP_INT_MAX . "\n\n";

        // Zero out matrices AA, Nt, G, As, Au
        $this->ZerMatr($AA, $M);
        $this->ZerMatr($Nt, $M);
        $this->ZerMatr($G, $M);
        $this->ZerMatr($As, $M);
        $this->ZerMatr($Au, $M);

        $F = 0;
        $Sy = 0;
        $Sz = 0;
        $Iy = 0;
        $Iz = 0;
        $Iyz = 0;

        // Global variables used: $M, $N, $Tm, $ks, $C, $Q1, $QL, $Bm, $Ym, $Bn
        // global $M, $N, $Tm, $ks, $C, $Q1, $QL, $Bm, $Ym, $Bn;

        // Формирование матрицы инцидентности с учетом фактической толщины
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $N; $J++) {
                if ($J == $C[$I][0] - 1) {
                    $G[$J][$I] = 1;
                }
                if ($J == $C[$I][1] - 1) {
                    $G[$J][$I] = -1;
                }
                $As[$J][$I] = $G[$J][$I] * $Tm[$I] / $ks[$I];
            }
        }
        // Nt^(K, M) - транспонированная матрица контуров
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $K; $J++) {
                $Nt[$J][$I] = $C[$I][$J + 2];
            }
        }

        for ($I = 0; $I < $M; $I++) {
            $FI[$I] = M_PI - $Q1[$I]; // Note: In Pascal, FI:=Pi-Q1
            $C1 = cos($FI[$I]);
            $S1 = sin($FI[$I]);
            $Fm[$I] = $Tm[$I] * $QL[$I];
            $Bm0 = $Bm[$I] + $QL[$I] * $C1 / 2;
            $Ym0 = $Ym[$I] + $QL[$I] * $S1 / 2;
            $Sym[$I] = $Fm[$I] * $Bm0;
            $Szm[$I] = $Fm[$I] * $Ym0;
            $Iym[$I] = $Tm[$I] * $this->Step($QL[$I], 3) * $C1 * $C1 / 12;
            $Iym[$I] = $Iym[$I] + $this->Step($Tm[$I], 3) * $QL[$I] * $S1 * $S1 / 12;
            $Iym[$I] = $Iym[$I] + $Fm[$I] * $Bm0 * $Bm0;
            $Izm[$I] = $Tm[$I] * $this->Step($QL[$I], 3) * $S1 * $S1 / 12;
            $Izm[$I] = $Izm[$I] + $this->Step($Tm[$I], 3) * $QL[$I] * $C1 * $C1 / 12;
            $Izm[$I] = $Izm[$I] + $Fm[$I] * $Ym0 * $Ym0;
            $Iyzm[$I] = ($this->Step($QL[$I], 2) / 12 - $this->Step($Tm[$I], 2) / 12) * $S1 * $C1;
            $Iyzm[$I] = $Fm[$I] * ($Iyzm[$I] + $Bm0 * $Ym0);
            $F += $Fm[$I];
            $Sy += $Sym[$I];
            $Sz += $Szm[$I];
            $Iy += $Iym[$I];
            $Iz += $Izm[$I];
            $Iyz += $Iyzm[$I];
        }
        $B0 = $Sy / $F;
        $Y0 = $Sz / $F;
        $Iy0 = $Iy - $B0 * $Sy;
        $Iz0 = $Iz - $Y0 * $Sz;

        for ($I = 0; $I < $M; $I++) {
            $B0m = $Bm[$I] - $B0;
            // echo $B0m . '<br>';

            $C1 = cos(($Q1[$I] + $QL[$I]) / 2);
            $S1 = sin(($QL[$I] - $Q1[$I]) / 2);
            $S2 = sin(($QL[$I] + $Q1[$I]) / 2);
            $S[$I] = $QL[$I] * ($B0m + $QL[$I] * cos($FI[$I]) / 2);
            $Um[$I] = $QL[$I] * $QL[$I] * ($B0m / 2 + $QL[$I] * cos($FI[$I]) / 6);
            $T0m[$I] = $B0m * $B0m * $this->Step($QL[$I], 3) / 3;
            $T0m[$I] = $T0m[$I] + $B0m * $this->Step($QL[$I], 4) * cos($FI[$I]) / 4;
            $T0m[$I] = $T0m[$I] + $this->Step($QL[$I], 5) * cos($FI[$I]) * cos($FI[$I]) / 20;
        }
        // dd($B0m, $FI, $QL, $S);
        for ($I = 0; $I < ($M - $K); $I++) {
            $A = 0;
            for ($J = 0; $J < $M; $J++) {
                $A += $G[$I][$J] * $S[$J] * $Tm[$J] * (1 - $G[$I][$J]) / 2;
            }
            $Bs[$I] = $A;
        }
        for ($I = 0; $I < $K; $I++) {
            for ($J = 0; $J < $M; $J++) {
                $L[$J] = $QL[$J];
                $Au[$I][$J] = $Nt[$I][$J] * $L[$J];
            }
        }

        for ($I = 0; $I < $M; $I++) {
            $Um[$I] = $Um[$I] * $ks[$I];
        }
        for ($I = 0; $I < $M; $I++) {
            $Bu[$I] = 0;
            for ($J1 = 0; $J1 < $M; $J1++) {
                $Bu[$I] += $Nt[$I][$J1] * $Um[$J1];
            }
        }

        // Формирование разрешающей матрицы
        for ($I = 0; $I < $M; $I++) {
            for ($J = 0; $J < $M; $J++) {
                if ($I < ($M - $K)) {
                    $AA[$I][$J] = $As[$I][$J];
                } else {
                    $AA[$I][$J] = $Au[$I - $M + $K][$J];
                }
            }
            if ($I < ($M - $K)) {
                $BB[$I] = $Bs[$I];
            } else {
                $BB[$I] = $Bu[$I - $M + $K];
            }
        }
        // dd($AA[62][62]);
        // Solve system using Gauss elimination.
        // dd($X1);
        $this->Gauss($M, $AA, $BB, $X1);
        // dd($X1);

        $T = 0;
        for ($I = 0; $I < $M; $I++) {
            $T += $Tm[$I] * ($X1[$I] * $X1[$I] * $L[$I] / $ks[$I]
                - 2 * $X1[$I] * $Um[$I] / $ks[$I] + $ks[$I] * $T0m[$I]);
        }

        for ($I = 0; $I < $M; $I++) {
            $X2[$I] = $X1[$I] - $S[$I] * $ks[$I];
        }
        // dd($S, $X2);
        $OM = ($Iy0 * $Iy0) / $T;
        $Q1_rez = [];
        $Tm_rez = [];
        $Xt = [];
        $Xt2 = [];

        $T1 = [];
        $T2 = [];
        $Sigbn1 = [];
        $Sigbn2 = [];
        for ($I = 0; $I < $M; $I++) {
            $Sigbn1[$I] = $Msw / $Iy0 * ($Bm[$I] - $B0);
            $Sigbn2[$I] = $Msw / $Iy0 * (($Bm[$I] + $QL[$I] * cos(M_PI - $Q1[$I])) - $B0);
            $Xt[$I] = $Nsw * $X1[$I] / $Iy0;
            $Xt2[$I] = $Nsw * $X2[$I] / $Iy0;
            $T1[$I] = $Xt[$I] * $Tm[$I] / $ks[$I];
            $T2[$I] = $Xt2[$I] * $Tm[$I] / $ks[$I];
            $Q1_rez[$I] = 180 - $Q1[$I] * 180 / M_PI;
            $Tm_rez[$I] = $Tm[$I] / $ks[$I];
        }

        return [
            $F,
            $Q1_rez,
            $Tm_rez,
            $OM,
            $Iy0,
            $Iz0,
            $B0,
            $Y0,
            $Bn,
            $Bm,
            $Yn,
            $Ym,
            $QL,
            $Tm,
            $ks,
            $C,
            $Xt,
            $Xt2,
            $T1,
            $T2,
            $Sigbn1,
            $Sigbn2,
        ];
    }

    private function new_array($length, $init = 0.0)
    {
        $arr = array();
        for ($i = 0; $i <= $length; $i++) {
            $arr[$i] = $init;
        }
        return $arr;
    }

    private function new_matrix($rows, $cols, $init = 0.0)
    {
        $mat = array();
        for ($i = 0; $i < $rows; $i++) {
            $mat[$i] = array();
            for ($j = 0; $j < $cols; $j++) {
                $mat[$i][$j] = $init;
            }
        }
        return $mat;
    }

    // Dummy implementation for Menu3.
    // Parameters: $St (menu items array), $NN, $X, $Y, $XX, $AtF, $AtR, $AtZ, $AtW, $Attr, &$Us, &$Ind
    // We simulate by displaying the items and reading an integer choice.



    // -------------------------------------------------------------------------
    // Function Step(F, Ex: double): double;
    // Возведение положительного числа F в степень Ex через экспоненту и натуральный логарифм.
    private function Step($F, $Ex)
    {
        if ($F < 0) {
            echo "отрицательное число не возводится в степень\n";
            // Wait for user input
            fgets(STDIN);
        }
        if ($F == 0) {
            return 0;
        } else {
            $E = $Ex * log($F);
            return exp($E);
        }
    }

    // -------------------------------------------------------------------------
    // Procedure Gauss(P: integer; X: MatrMas; Y: MatrMassiv; Var Z: MatrMassiv);
    // Решение системы методом Гаусса с выбором главного элемента.
    private function Gauss($P, $X, $Y, &$Z)
    {
        //dd($X);

        $K = 1;
        $M = 0;
        $MM = 0;

        // dd($P, $Y, $Z);
        for ($M = 0; $M < ($P - 1); $M++) {
            $L = $M;
            for ($I = $M + 1; $I < $P; $I++) {
                if (abs($X[$I][$M]) > abs($X[$L][$M])) {
                    $L = $I;
                }
            }
            if ($L > $M) {
                $K = -$K;
                for ($J = $M; $J < $P; $J++) {
                    $Ftmp = $X[$M][$J];
                    $X[$M][$J] = $X[$L][$J];
                    $X[$L][$J] = $Ftmp;
                }
                $Ftmp = $Y[$M];
                $Y[$M] = $Y[$L];
                $Y[$L] = $Ftmp;
            }

            for ($I = $M + 1; $I < $P; $I++) { // Исключение неизвестных
                $F = $X[$I][$M] / $X[$M][$M];
                $X[$I][$M] = 0.0;
                for ($J = $M + 1; $J < $P; $J++) {
                    $X[$I][$J] = $X[$I][$J] - $F * $X[$M][$J];
                }
                $Y[$I] = $Y[$I] - $F * $Y[$M];
            }
        }

        $Z[$P - 1] = $Y[$P - 1] / $X[$P - 1][$P - 1]; // Обратная подстановка
        for ($L = 2; $L <= $P; $L++) {
            $I = $P  - $L;
            $F = 0;
            $MM = $L - 1;
            for ($J = 0; $J < $MM; $J++) {
                $F = $F + $Z[$P - $J - 1] * $X[$I][$P  - $J -  1];
            }
            // echo ($F . '<br>');
            // dd($I, $F);
            $Z[$I] = ($Y[$I] - $F) / $X[$I][$I];
            // if ($I == 0)
            //     dd($F, $Z[0], $Y[0], $X[0][0]);
        }

        // for ($J = 65; $J >= 0; $J--)
        // echo $J . ' ' . $X[$J][$J] . '<br>';
        // dd($Y);
        // dd($I, $Z[$I], $X[65][65]);
        // dd($Z);
    }

    // -------------------------------------------------------------------------
    // Procedure ZerMatr(Var C: MatrMas);
    // Обнуление элементов матрицы.
    private function ZerMatr(&$C, $M)
    {
        // Sets matrix entries from 1 to M+2 (both rows and columns) to 0.0.
        for ($I = 0; $I < $M + 2; $I++) {
            for ($J = 0; $J < $M + 2; $J++) {
                $C[$I][$J] = 0.0;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Procedure Cont(Var B0, Y0, F, Iy0, Iz0, OM: double; Var X1, X2: MD);
    // private function Cont(&$B0, &$Y0, &$F, &$Iy0, &$Iz0, &$OM, &$X1, &$X2)
    // {
    //     // Label Lm1 is not used explicitly in PHP.
    //     // Variable declarations (simulated as local variables)
    //     // MD variables (1D arrays) and MMD variables (2D arrays)
    //     // Allocate variables with dynamic arrays.

    //     // For matrices, we allocate with size Mat x Mat.
    //     $G = $this->new_matrix($M, $M, 0.0);
    //     $As = $this->new_matrix($M, $M, 0.0);
    //     $Au = $this->new_matrix($M, $M, 0.0);
    //     $AA = $this->new_matrix($M, $M, 0.0);
    //     $Nt = $this->new_matrix($M, $M, 0.0);

    //     // One dimensional arrays of length Mat (simulate MD)
    //     $X = $this->new_array($M, 0.0);
    //     $FI = $this->new_array($M, 0.0);
    //     $S = $this->new_array($M, 0.0);
    //     $Um = $this->new_array($M, 0.0);
    //     $T0m = $this->new_array($M, 0.0);
    //     $Bs = $this->new_array($M, 0.0);
    //     $L = $this->new_array($M, 0.0);
    //     $BB = $this->new_array($M, 0.0);
    //     $Bu = $this->new_array($M, 0.0);
    //     $Hm = $this->new_array($M, 0.0);
    //     $A2k = $this->new_array($M, 0.0);
    //     $Fm = $this->new_array($M, 0.0);
    //     $Sym = $this->new_array($M, 0.0);
    //     $Szm = $this->new_array($M, 0.0);
    //     $Iym = $this->new_array($M, 0.0);
    //     $Izm = $this->new_array($M, 0.0);
    //     $Iyzm = $this->new_array($M, 0.0);
    //     $Psim = $this->new_array($M, 0.0);
    //     $Un = $this->new_array($M, 0.0);
    //     $DU = $this->new_array($M, 0.0);

    //     // X1 and X2 passed as MD are assumed to be allocated already by caller.
    //     // For memory check simulation we ignore MaxAvail conditions.

    //     // Display dynamic memory (simulated)
    //     // GotoXY(15, 7);
    //     // echo "Остаток динамической памяти : " . PHP_INT_MAX . "\n\n";

    //     // Zero out matrices AA, Nt, G, As, Au
    //     $this->ZerMatr($AA);
    //     $this->ZerMatr($Nt);
    //     $this->ZerMatr($G);
    //     $this->ZerMatr($As);
    //     $this->ZerMatr($Au);

    //     $F = 0;
    //     $Sy = 0;
    //     $Sz = 0;
    //     $Iy = 0;
    //     $Iz = 0;
    //     $Iyz = 0;

    //     // Global variables used: $M, $N, $Tm, $ks, $C, $Q1, $QL, $Bm, $Ym, $Bn
    //     // global $M, $N, $Tm, $ks, $C, $Q1, $QL, $Bm, $Ym, $Bn;

    //     // Формирование матрицы инцидентности с учетом фактической толщины
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         for ($J = 1; $J <= $this->N; $J++) {
    //             if ($J == $this->C[$I][1]) {
    //                 $G[$J][$I] = 1;
    //             }
    //             if ($J == $this->C[$I][2]) {
    //                 $G[$J][$I] = -1;
    //             }
    //             $As[$J][$I] = $G[$J][$I] * $this->Tm[$I] / $this->ks[$I];
    //         }
    //     }
    //     // Nt^(K, M) - транспонированная матрица контуров
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         for ($J = 1; $J <= $this->K; $J++) {
    //             $Nt[$J][$I] = $this->C[$I][$J + 2];
    //         }
    //     }

    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $FI[$I] = M_PI - $this->Q1[$I]; // Note: In Pascal, FI:=Pi-Q1
    //         $C1 = cos($FI[$I]);
    //         $S1 = sin($FI[$I]);
    //         $Fm[$I] = $this->Tm[$I] * $this->QL[$I];
    //         $Bm0 = $this->Bm[$I] + $this->QL[$I] * $C1 / 2;
    //         $Ym0 = $this->Ym[$I] + $this->QL[$I] * $S1 / 2;
    //         $Sym[$I] = $Fm[$I] * $Bm0;
    //         $Szm[$I] = $Fm[$I] * $Ym0;
    //         $Iym[$I] = $this->Tm[$I] * $this->Step($this->QL[$I], 3) * $C1 * $C1 / 12;
    //         $Iym[$I] = $Iym[$I] + $this->Step($this->Tm[$I], 3) * $this->QL[$I] * $S1 * $S1 / 12;
    //         $Iym[$I] = $Iym[$I] + $Fm[$I] * $Bm0 * $Bm0;
    //         $Izm[$I] = $this->Tm[$I] * $this->Step($this->QL[$I], 3) * $S1 * $S1 / 12;
    //         $Izm[$I] = $Izm[$I] + $this->Step($this->Tm[$I], 3) * $this->QL[$I] * $C1 * $C1 / 12;
    //         $Izm[$I] = $Izm[$I] + $Fm[$I] * $Ym0 * $Ym0;
    //         $Iyzm[$I] = ($this->Step($this->QL[$I], 2) / 12 - $this->Step($this->Tm[$I], 2) / 12) * $S1 * $C1;
    //         $Iyzm[$I] = $Fm[$I] * ($Iyzm[$I] + $Bm0 * $Ym0);
    //         $F += $Fm[$I];
    //         $Sy += $Sym[$I];
    //         $Sz += $Szm[$I];
    //         $Iy += $Iym[$I];
    //         $Iz += $Izm[$I];
    //         $Iyz += $Iyzm[$I];
    //     }
    //     $B0 = $Sy / $F;
    //     $Y0 = $Sz / $F;
    //     $Iy0 = $Iy - $B0 * $Sy;
    //     $Iz0 = $Iz - $Y0 * $Sz;

    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $B0m = $this->Bm[$I] - $B0;
    //         $C1 = cos(($this->Q1[$I] + $this->QL[$I]) / 2);
    //         $S1 = sin(($this->QL[$I] - $this->Q1[$I]) / 2);
    //         $S2 = sin(($this->QL[$I] + $this->Q1[$I]) / 2);
    //         $S[$I] = $this->QL[$I] * ($B0m + $this->QL[$I] * cos($FI[$I]) / 2);
    //         $Um[$I] = $this->QL[$I] * $this->QL[$I] * ($B0m / 2 + $this->QL[$I] * cos($FI[$I]) / 6);
    //         $T0m[$I] = $B0m * $B0m * $this->Step($this->QL[$I], 3) / 3;
    //         $T0m[$I] = $T0m[$I] + $B0m * $this->Step($this->QL[$I], 4) * cos($FI[$I]) / 4;
    //         $T0m[$I] = $T0m[$I] + $this->Step($this->QL[$I], 5) * cos($FI[$I]) * cos($FI[$I]) / 20;
    //     }
    //     for ($I = 1; $I <= ($this->M - $this->K); $I++) {
    //         $A = 0;
    //         // Note: The following loop uses g^[I,J] but g was built as $G with indices [J,I]
    //         // So we interpret g^[I,J] as $G[$I][$J] (assuming symmetry of indices in usage)
    //         for ($J = 1; $J <= $this->M; $J++) {
    //             $A += $G[$I][$J] * $S[$J] * $this->Tm[$J] * (1 - $G[$I][$J]) / 2;
    //         }
    //         $Bs[$I] = $A;
    //     }
    //     for ($I = 1; $I <= $this->K; $I++) {
    //         for ($J = 1; $J <= $this->M; $J++) {
    //             $L[$J] = $this->QL[$J];
    //             $Au[$I][$J] = $Nt[$I][$J] * $L[$J];
    //         }
    //     }

    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $Um[$I] = $Um[$I] * $this->ks[$I];
    //     }
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $Bu[$I] = 0;
    //         for ($J1 = 1; $J1 <= $this->M; $J1++) {
    //             $Bu[$I] += $Nt[$I][$J1] * $Um[$J1];
    //         }
    //     }

    //     // Формирование разрешающей матрицы
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         for ($J = 1; $J <= $this->M; $J++) {
    //             if ($I <= ($this->M - $this->K)) {
    //                 $AA[$I][$J] = $As[$I][$J];
    //             } else {
    //                 $AA[$I][$J] = $Au[$I - $this->M + $this->K][$J];
    //             }
    //         }
    //         if ($I <= ($this->M - $this->K)) {
    //             $BB[$I] = $Bs[$I];
    //         } else {
    //             $BB[$I] = $Bu[$I - $this->M + $this->K];
    //         }
    //     }
    //     // Solve system using Gauss elimination.
    //     $this->Gauss($this->M, $AA, $BB, $this->X1);
    //     $T = 0;
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $T += $this->Tm[$I] * ($X1[$I] * $X1[$I] * $L[$I] / $this->ks[$I]
    //             - 2 * $X1[$I] * $Um[$I] / $this->ks[$I] + $this->ks[$I] * $T0m[$I]);
    //     }
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $X2[$I] = $X1[$I] - $S[$I] * $this->ks[$I];
    //     }
    //     $OM = ($Iy0 * $Iy0) / $T;

    //     // Dispose dynamically allocated arrays by unsetting them.
    //     unset($G);
    //     unset($As);
    //     unset($Au);
    //     unset($AA);
    //     unset($Nt);
    //     unset($X);
    //     unset($FI);
    //     unset($S);
    //     unset($Um);
    //     unset($T0m);
    //     unset($Bs);
    //     unset($L);
    //     unset($BB);
    //     unset($Bu);
    //     unset($Hm);
    //     unset($A2k);
    //     unset($Fm);
    //     unset($Sym);
    //     unset($Szm);
    //     unset($Iym);
    //     unset($Izm);
    //     unset($Iyzm);
    //     unset($Psim);
    //     unset($Un);
    //     unset($DU);
    // }


    // -------------------------------------------------------------------------
    // Procedure Wwod3(Var Bm, Ym, Q1, QL: MatrMassiv);
    // private function Wwod3(&$Bm, &$Ym, &$Q1, &$QL)
    // {
    //     // global $M, $N, $C, $Bn;
    //     $G = $this->new_matrix($M, $M, 0.0);
    //     $Nx = 0;
    //     $a = 0.0;
    //     $b = 0.0;
    //     $Fi = $this->new_array($M, 0.0);
    //     // Формирование матрицы инцидентности
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         for ($J = 1; $J <= $this->N; $J++) {
    //             if ($J == $this->C[$I][1]) {
    //                 $G[$I][$J] = 1;
    //             }
    //             if ($J == $this->C[$I][2]) {
    //                 $G[$I][$J] = -1;
    //             }
    //         }
    //     }
    //     // Ориентация ребер
    //     for ($I = 1; $I <= $this->N; $I++) {
    //         for ($J = 1; $J <= $this->M; $J++) {
    //             if ($G[$I][$J] > 0) {
    //                 $Bm[$J] = $this->Bn[$I];
    //                 $Ym[$J] = $this->Yn[$I];  // Note: In the Pascal code, Yn is used here. Using Bn for simulation.
    //                 $Nx = round($this->C[$J][2]);
    //                 $a = $this->Bn[$Nx] - $Ym[$J];
    //                 $b = $this->Bn[$Nx] - $Bm[$J];
    //             }
    //             if ($G[$I][$J] < 0) {
    //                 $Nx = round($this->C[$J][1]);
    //                 $Bm[$J] = $this->Bn[$Nx];
    //                 $Ym[$J] = $this->Bn[$Nx];  // Using Bn in place of Yn as per provided code structure.
    //                 $a = $this->Bn[$I] - $this->Ym[$J];
    //                 $b = $this->Bn[$I] - $this->Bm[$J];
    //             }
    //             if ($G[$I][$J] == 0)
    //                 continue;
    //             $QL[$J] = sqrt($a * $a + $b * $b);
    //             if ($b == 0) {
    //                 if ($a > 0) {
    //                     $Fi[$J] = M_PI / 2;
    //                 } else {
    //                     $Fi[$J] = -M_PI / 2;
    //                 }
    //             }
    //             if ($b < 0) {
    //                 if ($a >= 0) {
    //                     $Fi[$J] = M_PI + atan($a / $b);
    //                 } else {
    //                     $Fi[$J] = -M_PI + atan($a / $b);
    //                 }
    //             }
    //             if ($b > 0) {
    //                 $Fi[$J] = atan($a / $b);
    //             }
    //             $Q1[$J] = M_PI - $Fi[$J];
    //         }
    //     }
    //     unset($G);
    // }

    // -------------------------------------------------------------------------
    // Procedure Dann;


    // -------------------------------------------------------------------------
    // Procedure Korr(Var M, K, N: integer; Var Bn, Yn, Tm, ks: MatrMassiv; Var C: MMD);
    // private function Korr(&$M, &$K, &$N, &$Bn, &$Yn, &$Tm, &$ks, &$C)
    // {
    //     $Us = 7;
    //     $St1[6] = ' количество ребер и контуров               ';
    //     $St1[7] = '          выход из режима                  ';
    //     $St1[8] = ' корректировка ';
    //     while (true) {
    //         TextBackGround(1);
    //         TextColor(14);
    //         ClrScr();
    //         GotoXY(20, 3);
    //         echo "какой раздел будете корректировать ?\n";
    //         Menu3($St1, 7, 20, 5, 43, 30, 112, 112, 112, 32, $Us, $Ind);
    //         ClrScr();
    //         GotoXY(1, 7);
    //         switch ($Ind) {
    //             case 1:
    //             case 2:
    //             case 3:
    //             case 4:
    //                 echo "  вводите порядковый номер and новое значение через пробел,\n";
    //                 GotoXY(25, 10);
    //                 $line = trim(fgets(STDIN));
    //                 list($I, $NewXX) = sscanf($line, "%d %f");
    //                 break;
    //             case 5:
    //                 echo "  вводите номер строки , столбца и новое значение через пробел,\n";
    //                 GotoXY(25, 10);
    //                 $line = trim(fgets(STDIN));
    //                 list($I, $J, $NewXX) = sscanf($line, "%d %d %f");
    //                 break;
    //         }
    //         switch ($Ind) {
    //             case 1:
    //                 $Bn[$I] = $NewXX;
    //                 break;
    //             case 2:
    //                 $Yn[$I] = $NewXX;
    //                 break;
    //             case 3:
    //                 $Tm[$I] = $NewXX;
    //                 break;
    //             case 4:
    //                 $ks[$I] = $NewXX;
    //                 break;
    //             case 5:
    //                 $C[$I][$J] = $NewXX;
    //                 break;
    //             case 6:
    //                 $Mnew = 0;
    //                 $Knew = 0;
    //                 GotoXY(10, 15);
    //                 echo "Количество ребер и контуров можно увеличивать или уменьшать.\n";
    //                 GotoXY(10, 16);
    //                 echo "Добавление производится с конца.\n";
    //                 GotoXY(10, 17);
    //                 echo "В случае увеличения дополнительная информация обнуляется и\n";
    //                 GotoXY(10, 18);
    //                 echo "заносится в исходные данные через режим корректировки .\n";
    //                 GotoXY(10, 19);
    //                 echo "Количество убираемых ребер или контуров вводится со знаком минус.\n";
    //                 GotoXY(10, 21);
    //                 echo "Будете из";
    //                 // The Pascal code is truncated here.
    //                 break;
    //         }
    //         // For demonstration, break out after one correction iteration.
    //         break;
    //     }
    // }

    // -------------------------------------------------------------------------
    // Main Program Entry Point
    // private function Screen()
    // {
    //     // global $B0, $Y0, $F, $Iy0, $Iz0, $OM, $X1, $X2, $Tm, $ks, $Key, $M;
    //     // Local variables
    //     $Xt = array();
    //     $Xt2 = array();
    //     $Sigbn1 = array();
    //     $Sigbn2 = array();

    //     // Call Cont(B0,Y0,F,Iy0,Iz0,OM,X1,X2);
    //     $this->Cont($this->B0, $this->Y0, $this->F, $this->Iy0, $this->Iz0, $this->OM, $this->X1, $this->X2);
    //     // ClrScr();
    //     // GoToXY(1, 5);
    //     echo "        площадь поперечного сечения       : " . $this->F . " см2" . PHP_EOL;
    //     echo "        приведенная площадь по сдвигу     : " . $this->OM . " см2" . PHP_EOL;
    //     echo "        момент инерции относительно оси Y : " . $this->Iy0 . " см4" . PHP_EOL;
    //     echo "        момент инерции относительно оси Z : " . $this->Iz0 . " см4" . PHP_EOL;
    //     echo "        отстояние н.о. Y  от о.с.         : " . $this->B0 . " см" . PHP_EOL;
    //     echo "        отстояние н.о. Z  от о.с.         : " . $this->Y0 . " см" . PHP_EOL;


    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $Xt[$I] = $this->Nsw * $this->X1[$I] / $this->Iy0;
    //         $Xt2[$I] = $this->Nsw * $this->X2[$I] / $this->Iy0;
    //     }
    //     echo " касат. усилия и напряжения в начале и конце ребра, кгс/см2 " . PHP_EOL;
    //     echo " реб. T(нач.реб.)       tau(нач.реб.)     T(кон.реб.)       tau(кон.реб.)   " . PHP_EOL;
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         if ($I == 21 || $I == 44 || $I == 67) {
    //             continue;
    //         }
    //         // Using sprintf to simulate Pascal formatting
    //         // Format: I:2, value:16 etc.
    //         $str = sprintf(
    //             "%2d  %16.6f  %16.6f  %16.6f  %16.6f",
    //             $I,
    //             $Xt[$I] * $this->Tm[$I] / $this->ks[$I],
    //             $Xt[$I],
    //             $Xt2[$I] * $this->Tm[$I] / $this->ks[$I],
    //             $Xt2[$I]
    //         );
    //         echo $str . PHP_EOL;
    //     }
    //     echo PHP_EOL;
    //     echo "где T   - распределение потока касательных усилий по вершинам ребер" . PHP_EOL;
    //     echo "    tau - касат.напряжения в вершинах ребер " . PHP_EOL;
    //     echo "    введите любую клавишу для продолжения " . PHP_EOL;

    //     // Dispose(X1); Dispose(X2);
    //     echo "Выводить распределение нормальных напряжений ? (y/n) : ";

    //     for ($I = 1; $I <= $this->M; $I++) {
    //         $Sigbn1[$I] = $this->Msw / $this->Iy0 * ($this->Bm[$I] - $this->B0);
    //         $Sigbn2[$I] = $this->Msw / $this->Iy0 * (($this->Bm[$I] + $this->QL[$I] * cos(M_PI - $this->Q1[$I])) - $this->B0);
    //     }
    //     echo " нормальные напряжения при изгибе в начале и конце ребра, кгс/см2 " . PHP_EOL;
    //     echo " реб. sigm(нач.реб.)     sigm(кон.реб.)   " . PHP_EOL;
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         if ($I == 21 || $I == 44 || $I == 67) {
    //             continue;
    //         }
    //         $str = sprintf("%2d  %16.6f  %16.6f", $I, $Sigbn1[$I], $Sigbn2[$I]);
    //         echo $str . PHP_EOL;
    //     }
    // }

    // {-----------------------------------------------------------------------------}
    // Procedure Prin translated from Pascal
    // private function Prin()
    // {
    //     // global $Lst, $FilName, $B0, $Y0, $F, $Iy0, $Iz0, $OM, $X1, $X2;
    //     // global $I, $Bn, $Yn, $Tm, $ks, $C, $Bm, $Ym, $Q1, $QL;
    //     // // Local variables
    //     $Year = 0;
    //     $Month = 0;
    //     $Day = 0;
    //     $Z = 0;
    //     $Xt = array();
    //     $Xt2 = array();
    //     $Sigbn1 = array();
    //     $Sigbn2 = array();
    //     $Yn1 = '';
    //     $Yn2 = '';
    //     $Yn3 = '';
    //     $Nsw = 0.0;
    //     $Msw = 0.0;

    //     // Open file 'Shear.txt' for writing (Lst already opened globally)
    //     // GetDate(Year,Month,Day,Z);
    //     $Year = (int) date("Y");
    //     $Month = (int) date("m");
    //     $Day = (int) date("d");
    //     // Write header information
    //     // fprintf($Lst, "      Programm Shear.   Original data see file - %s. %s", $FilName, PHP_EOL);
    //     // fprintf($Lst, "      %2d. %2d. %2d. %s", $Day, $Month, $Year, PHP_EOL);
    //     // // Call Cont(B0,Y0,F,Iy0,Iz0,OM,X1,X2);
    //     $this->Cont($this->B0, $this->Y0, $this->F, $this->Iy0, $this->Iz0, $this->OM, $this->X1, $this->X2);
    //     echo "Выводить исходные данные ?            (y/n) : ";
    //     for ($I = 1; $I <= $this->N; $I++) {
    //         // Assume $Bn and $Yn arrays hold dummy data; if not, fill with sample values
    //         if (!isset($this->Bn[$I])) {
    //             $this->Bn[$I] = 10 + $I * 0.1;
    //         }
    //         if (!isset($this->Yn[$I])) {
    //             $this->Yn[$I] = 20 + $I * 0.1;
    //         }
    //     }
    //     for ($I = 1; $I <= $this->M; $I++) {
    //         // Ensure dummy values for required arrays if not set
    //         if (!isset($this->Bm[$I])) {
    //             $this->Bm[$I] = 10 + $I;
    //         }
    //         if (!isset($this->Ym[$I])) {
    //             $this->Ym[$I] = 20 + $I;
    //         }
    //         if (!isset($this->Q1[$I])) {
    //             $this->Q1[$I] = 0.5;
    //         }
    //         if (!isset($this->Tm[$I])) {
    //             $this->Tm[$I] = 1.2;
    //         }
    //         if (!isset($this->ks[$I])) {
    //             $this->ks[$I] = 2.0;
    //         }
    //         if (!isset($this->QL[$I])) {
    //             $this->QL[$I] = 5.0;
    //         }
    //         // For matrix C (2D): assume first two columns exist; fill dummy if not set.
    //         if (!isset($this->C[$I])) {
    //             $this->C[$I] = array();
    //             $this->C[$I][1] = 1;
    //             $this->C[$I][2] = 2;
    //             for ($j = 3; $j <= ($this->K + 2); $j++) {
    //                 $C[$I][$j] = $j;
    //             }
    //         }
    //     fprintf(
    //         $Lst,
    //         "     %2d  %3.0f%3.0f%9.3f%9.3f%8.3f%8.3f%8.3f%8.3f%s",
    //         $I,
    //         $C[$I][1],
    //         $C[$I][2],
    //         $Bm[$I],
    //         $Ym[$I],
    //         (180 - $Q1[$I] * 180 / M_PI),
    //         ($Tm[$I] / $ks[$I]),
    //         $Tm[$I],
    //         $ks[$I],
    //         $QL[$I],
    //         PHP_EOL
    //     );
    // }
    // fwrite($Lst, PHP_EOL);
    // fprintf($Lst, "   where Zm - initial point ordinate of branch, cm (determined);%s", PHP_EOL);
    // fprintf($Lst, "         Ym - initial point abscissa of branch, cm (determined);%s", PHP_EOL);
    // fprintf($Lst, "         Fi - branch deflection from Z-axis, degree (determined);%s", PHP_EOL);
    // fprintf($Lst, "         Tm - actual thickness of branch, cm (determined);%s", PHP_EOL);
    // fprintf($Lst, "         Hm - reduced thickness of branch, cm;%s", PHP_EOL);
    // fprintf($Lst, "         km - koefficient of reduction.%s", PHP_EOL);
    // fprintf($Lst, "         Lm - length of branch, cm (determined);%s", PHP_EOL);
    // fwrite($Lst, PHP_EOL);
    // fprintf($Lst, "       Matrix of Indexes    Matrix of Contours%s", PHP_EOL);
    // fwrite($Lst, PHP_EOL);
    // // Write header for matrix of indexes and contours
    // $header = "      m     i1    i2     ";
    // for ($I = 1; $I <= K; $I++) {
    //     $header .= sprintf("%3d", $I);
    // }
    // fprintf($Lst, "%s%s", $header, PHP_EOL);
    // fwrite($Lst, PHP_EOL);
    // for ($I = 1; $I <= M; $I++) {
    //     $line = sprintf("     %2d    %3.0f   %3.0f     ", $I, $C[$I][1], $C[$I][2]);
    //     for ($J = 1; $J <= K; $J++) {
    //         // C^[I,J+2] becomes $C[$I][$J+2]
    //         $line .= sprintf("%3.0f", $C[$I][$J + 2]);
    //     }
    //     fprintf($Lst, "%s%s", $line, PHP_EOL);
    // }
    // fwrite($Lst, PHP_EOL);

    // fprintf($Lst, PHP_EOL);
    // fprintf($Lst, "  Results%s", PHP_EOL);
    // fprintf($Lst, PHP_EOL);
    // fprintf($Lst, "  Characteristics of Section%s", PHP_EOL);
    // fprintf($Lst, PHP_EOL);
    // fprintf($Lst, "  Cross-sectional area                               F = %14.6f cm2%s", $F, PHP_EOL);
    // fprintf($Lst, "  Shearing area                                      Om= %14.6f cm2%s", $OM, PHP_EOL);
    // fprintf($Lst, "  Inertia moment about the horizontal neutral axis Y Iy= %14.6f cm4%s", $Iy0, PHP_EOL);
    // fprintf($Lst, "  Inertia moment about the vertical neutral axis Z   Iz= %14.6f cm4%s", $Iz0, PHP_EOL);
    // fprintf($Lst, "  Centre of gravity from the base lines: %s", PHP_EOL);
    // fprintf($Lst, "  - from horizontal base line Y                      z = %14.6f cm%s", $B0, PHP_EOL);
    // fprintf($Lst, "  - from vertical base line Z                        y = %14.6f cm%s", $Y0, PHP_EOL);
    // fprintf($Lst, PHP_EOL);
    // GoToXY(10, 9);
    // echo "Выводить касательные усилия в узлах ? (y/n) : ";
    // $Yn3 = readln();
    // if ($Yn3 == 'y' || $Yn3 == 'Y') {
    //     GoToXY(10, 11);
    //     echo "Введите перерезывающую силу,           кгс : ";
    //     $Nsw = floatval(readln());
    //     for ($I = 1; $I <= M; $I++) {
    //         $Xt[$I] = $Nsw * $X1[$I] / $Iy0;
    //     }
    //     for ($I = 1; $I <= M; $I++) {
    //         $Xt2[$I] = $Nsw * $X2[$I] / $Iy0;
    //     }
    //     fprintf($Lst, PHP_EOL);
    //     fprintf($Lst, "  Shear Force,        N = %15.6f  kgf%s", $Nsw, PHP_EOL);
    //     fprintf($Lst, PHP_EOL);
    //     fprintf($Lst, "      m    T1,kgf/cm        tau1,kgf/cm2     T2,kgf/cm        tau2,kgf/cm2%s", PHP_EOL);
    //     for ($I = 1; $I <= M; $I++) {
    //         fprintf(
    //             $Lst,
    //             "     %2d  %15.6f  %15.6f  %15.6f  %15.6f%s",
    //             $I,
    //             $Xt[$I] * $Tm[$I] / $ks[$I],
    //             $Xt[$I],
    //             $Xt2[$I] * $Tm[$I] / $ks[$I],
    //             $Xt2[$I],
    //             PHP_EOL
    //         );
    //     }
    //     fprintf($Lst, PHP_EOL);
    //     fprintf($Lst, "  T1,T2  - stream of shear forces at initial and end points of branch%s", PHP_EOL);
    //     fprintf($Lst, "  tau1,tau2 - shear stresses at initial and end points of branch %s", PHP_EOL);
    //     fprintf($Lst, "      %s", PHP_EOL);
    //     fprintf($Lst, "      %s", PHP_EOL);
    // }
    // // Dispose(X1); Dispose(X2);
    // unset($X1);
    // unset($X2);
    // GoToXY(10, 13);
    // echo "Выводить распределение нормальных напряжений ? (y/n) : ";
    // $Yn3 = readln();
    // if ($Yn3 == 'y' || $Yn3 == 'Y') {
    //     GoToXY(10, 15);
    //     echo "Введите изгибающий момент,          кгс*см : ";
    //     $Msw = floatval(readln());
    //     for ($I = 1; $I <= M; $I++) {
    //         $Sigbn1[$I] = $Msw / $Iy0 * ($Bm[$I] - $B0);
    //         $Sigbn2[$I] = $Msw / $Iy0 * (($Bm[$I] + $QL[$I] * cos(M_PI - $Q1[$I])) - $B0);
    //     }
    //     fprintf($Lst, PHP_EOL);
    //     fprintf($Lst, "  Bending Moment,       My = %15.6f  kgf*cm%s", $Msw, PHP_EOL);
    //     fprintf($Lst, PHP_EOL);
    //     fprintf($Lst, "      m    Sigm1,kgf/cm2     Sigm2,kgf/cm2%s", PHP_EOL);
    //     for ($I = 1; $I <= M; $I++) {
    //         fprintf($Lst, "     %2d  %16.6f  %16.6f%s", $I, $Sigbn1[$I], $Sigbn2[$I], PHP_EOL);
    //     }
    //     fprintf($Lst, PHP_EOL);
    //     fprintf($Lst, "  sigm1,sigm2 - bending stresses at initial and end points of branch%s", PHP_EOL);
    //     fprintf($Lst, "       %s", PHP_EOL);
    //     fprintf($Lst, "  The End.%s", PHP_EOL);
    // }
    // fprintf($Lst, "  The End.%s", PHP_EOL);
    // }
    // }
}
