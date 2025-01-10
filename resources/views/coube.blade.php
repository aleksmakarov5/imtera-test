<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Решение алгебраических уравнений <br> с одним неизвестным 2,3,4 степеней
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">


                @if (isset($n))
                    <form action="/print" method="post" target="_blank">
                        @csrf
                        @if (isset($a))
                            <input type="hidden" name="a" value="{{ $a }}">
                        @endif
                        @if (isset($b))
                            <input type="hidden" name="b" value="{{ $b }}">
                        @endif
                        @if (isset($c))
                            <input type="hidden" name="c" value="{{ $c }}">
                        @endif
                        @if (isset($d))
                            <input type="hidden" name="d" value="{{ $d }}">
                        @endif
                        @if (isset($n))
                            <input type="hidden" name="n" value="{{ $n }}">
                        @endif
                        @if (isset($x1))
                            <input type="hidden" name="x1" value="{{ $x1 }}">
                        @endif
                        @if (isset($x2))
                            <input type="hidden" name="x2" value="{{ $x2 }}">
                        @endif
                        @if (isset($x3))
                            <input type="hidden" name="x3" value="{{ $x3 }}">
                        @endif
                        @if (isset($x4))
                            <input type="hidden" name="x4" value="{{ $x4 }}">
                        @endif
                        <button type="submit" class="btn btn-primary">Печать</button>
                    </form>


                    <div class="py-12">
                        @if ($n == 2)
                            Исходное уравнение:<br>
                            <math>
                                <mi>{{ $a }}</mi>
                                <mi>X</mi>
                                <sup>
                                    <mn>2</mn>
                                </sup>
                                <mo>+</mo>
                                <mi>{{ $b }}</mi>
                                <mi>X</mi>
                                <mo>+</mo>
                                <mi>{{ $c }}</mi>
                                <mo>=</mo>
                                <mn>0</mn>
                            </math>
                            <br>
                            @if ($d < 0)
                                Уравнение не имеет действительных корней
                            @else
                                Дискриминант равен {{ $d }} <br>
                                Корень X1 ранен {{ $x1 }} <br>
                                Корень X2 ранен {{ $x2 }} <br>
                            @endif
                        @endif
                        @if ($n == 3)
                            Исходное уравнение:<br>
                            <math>
                                <mi>X</mi>
                                <sup>
                                    <mn>3</mn>
                                </sup>
                                <mo>+</mo>
                                <mi>{{ $a }}</mi>
                                <mi>X</mi>
                                <sup>
                                    <mn>2</mn>
                                </sup>
                                <mo>+</mo>
                                <mi>{{ $b }}</mi>
                                <mi>X</mi>
                                <mo>+</mo>
                                <mi>{{ $c }}</mi>
                                <mo>=</mo>
                                <mn>0</mn>
                            </math>
                            <br>
                            Корень X1 ранен {{ $x1 }} <br>
                            Корень X2 ранен {{ $x2 }} <br>
                            Корень X3 ранен {{ $x3 }} <br>
                        @endif
                        @if ($n == 4)
                            Исходное уравнение:<br>
                            <math>
                                <mi>X</mi>
                                <sup>
                                    <mn>4</mn>
                                </sup>
                                <mo>+</mo>
                                <mi>{{ $a }}</mi>
                                <mi>X</mi>
                                <sup>
                                    <mn>3</mn>
                                </sup>
                                <mo>+</mo>
                                <mi>{{ $b }}</mi>
                                <mi>X</mi>
                                <sup>
                                    <mn>2</mn>
                                </sup>
                                <mo>+</mo>
                                <mi>{{ $c }}</mi>
                                <mi>X</mi>
                                <mo>+</mo>
                                <mi>{{ $d }}</mi>
                                <mo>=</mo>
                                <mn>0</mn>
                            </math>
                            <br>
                            Корень X1 ранен {{ $x1 }} <br>
                            Корень X2 ранен {{ $x2 }} <br>
                            Корень X3 ранен {{ $x3 }} <br>
                            Корень X4 ранен {{ $x4 }} <br>
                        @endif
                        <a href="/coube" class="btn btn-primary">Новый расчет</a>
                    </div>
                @else
                    <div id="app">
                        <kub />
                    </div>
                @endif
            </div>



        </div>

    </div>

</x-app-layout>
