<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Решение алгебраических уравнений <br> с одним неизвестным 2,3,4 степеней
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div id="app">
                    <kub />
                </div>
            </div>
            @if (isset($n))
                <div class="py-12">
                    @if ($n == 2)
                        @if ($d < 0)
                            Уравнение не имеет действительных корней
                        @else
                            Дискриминант равен {{ $d }} <br>
                            Корень X1 ранен {{ $x1 }} <br>
                            Корень X2 ранен {{ $x2 }} <br>
                        @endif
                    @endif
                </div>
            @endif
        </div>

    </div>

</x-app-layout>
