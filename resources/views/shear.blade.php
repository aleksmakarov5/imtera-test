<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            SHEAR (Страница в разработке)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                Наименование программы – «SHEAR» в переводе с английского срез, сдвиг, т.е.
                название отражает основную задачу, решаемую программой – определение распределения
                внутренних усилий – касательных напряжений и параметров сдвига для поперечного
                сечения,
                представляющего
                многосвязный
                тонкостенный
                профиль,
                прямолинейного
                стержня (балки) при действии поперечной нагрузки – перерезывающей силы. Также
                программа определяет распределение внутренних усилий – нормальных напряжений от
                действия изгибающего момента и выполняет расчет общих характеристик поперечного
                сечения корпуса судна (эквивалентного бруса).




            </div>
        </div>
        <br>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div id="app">
                    <shear />
                </div>



            </div>
        </div>
    </div>

</x-app-layout>
