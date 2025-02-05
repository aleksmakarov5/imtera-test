<?php

namespace App\Classes;


// Type definitions:

// MatrMas: array[1..Mat,1..Mat] of double
class MatrMas
{
    public $data = array();  // 2D array to simulate the matrix

    public function __construct()
    {
        for ($i = 1; $i <= Mat; $i++) {
            $this->data[$i] = array();
            for ($j = 1; $j <= Mat; $j++) {
                $this->data[$i][$j] = 0.0; // initialize to double 0.0
            }
        }
    }
}

// MatrMassiv: array[1..Mat] of double
class MatrMassiv
{
    public $data = array();

    public function __construct()
    {
        for ($i = 1; $i <= Mat; $i++) {
            $this->data[$i] = 0.0; // initialize each element as double 0.0
        }
    }
}

// MD = ^MatrMassiv and MMD = ^MatrMas
// In PHP, pointers are not available; we simulate by directly using objects of MatrMassiv and MatrMas.

// Str: string[25]
class Str
{
    public $value;

    public function __construct($value = "")
    {
        // Limit the string length to 25 characters as in the Pascal definition
        $this->value = substr($value, 0, 25);
    }

    public function __toString()
    {
        return $this->value;
    }
}

// Stt: string[50]
class Stt
{
    public $value;

    public function __construct($value = "")
    {
        // Limit the string length to 50 characters as in the Pascal definition
        $this->value = substr($value, 0, 50);
    }

    public function __toString()
    {
        return $this->value;
    }
}

// MasSt: Type used for St, St1, St2.
// The original Pascal code does not provide a definition for MasSt.
// To preserve the functionality exactly, we define it as a class representing an array of strings.
class MasSt
{
    public $data = array();

    public function __construct($initial = array())
    {
        $this->data = $initial;
    }
}

// Variable declarations:

// M, K, N : integer;
// { M - количество ребер } { K - количество контуров } { N - количество вершин }
$M = 0;
$K = 0;
$N = 0;

// Bn, Yn, Tm, ks: MatrMassiv; { Массивы основных исх. данных }
$Bn = new MatrMassiv();
$Yn = new MatrMassiv();
$Tm = new MatrMassiv();
$ks = new MatrMassiv();

// Bm, Ym, Q1, QL: MatrMassiv;
$Bm = new MatrMassiv();
$Ym = new MatrMassiv();
$Q1 = new MatrMassiv();
$QL = new MatrMassiv();

// C: MMD; { Матрица индексов и контуров }
$C = new MatrMas();

// X1, X2: MD;
$X1 = new MatrMassiv();
$X2 = new MatrMassiv();

// B0, Y0, F, Iy0, Iz0, OM: double;
$B0 = 0.0;
$Y0 = 0.0;
$F  = 0.0;
$Iy0 = 0.0;
$Iz0 = 0.0;
$OM = 0.0;

// I, J, J1, Ind: integer;
$I = 0;
$J = 0;
$J1 = 0;
$Ind = 0;

// FilName: str;
$FilName = new Str();

// St, St1, St2: MasSt;
$St = new MasSt();
$St1 = new MasSt();
$St2 = new MasSt();

// YN1: char; (Using a one-character string in PHP)
$YN1 = '';

// Key: word; (PHP does not have a 'word' type, using integer instead)
$Key = 0;
