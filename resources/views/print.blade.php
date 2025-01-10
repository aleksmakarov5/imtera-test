<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">


            @if (isset($n))

                <div class="py-12">
                    @if ($n == 2)
                        Исходное уравнение:<br>
                        <b>
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
                        </b>
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
                        <b>
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
                        </b>
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
                </div>

            @endif
        </div>
    </div>
</div>
