<?php

namespace App\classes;

class ComplexNumber
{
    /**
     * Create a new class instance.
     */
    public $real;
    public $imaginary;
    public function __construct($real, $imaginary)
    {
        $this->real = $real;
        $this->imaginary = $imaginary;
    }
    public function add(ComplexNumber $complexNumber)
    {
        return new ComplexNumber(
            $this->real + $complexNumber->getReal(),
            $this->imaginary + $complexNumber->getImaginary()
        );
    }
    public function subtract(ComplexNumber $complexNumber)
    {
        return new ComplexNumber(
            $this->real - $complexNumber->getReal(),
            $this->imaginary - $complexNumber->getImaginary()
        );
    }
    public function multiply(ComplexNumber $complexNumber)
    {
        $real = $this->real * $complexNumber->getReal()
            - $this->imaginary * $complexNumber->getImaginary();

        $imaginary = $this->real * $complexNumber->getImaginary()
            + $this->imaginary * $complexNumber->getReal();

        return new ComplexNumber($real, $imaginary);
    }
    public function __toString()
    {
        $real = round($this->real, 2);
        $imaginary = round($this->imaginary, 2);
        return "({$real}, {$imaginary}i)";
    }
    public function getReal()
    {
        return $this->real;
    }

    public function getImaginary()
    {
        return $this->imaginary;
    }

    public function StepC($ex)
    {
        $real = $this->real;
        $imaginary = $this->imaginary;
        $R = sqrt($real * $real + $imaginary * $imaginary);
        if ($imaginary == 0) {
            $real = 1;
            for ($i = 0; $i < $ex; $i++) {
                $real *= $this->real;
            }
            $imaginary = 0;
        } else {
            $Fi = atan($imaginary / $real);
            if ($real < 0)
                $Fi = pi() + $Fi;
            $real = pow($R, $ex) * cos($ex * $Fi);
            $imaginary = pow($R, $ex) * sin($ex * $Fi);
        }

        return new ComplexNumber($real, $imaginary);
    }
}
