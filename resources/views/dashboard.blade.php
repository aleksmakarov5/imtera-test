<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Отчет по движению денежных средств
        </h2>
    </x-slot>
    <div class="container">

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="overflow: auto;">
            <div class="" id="app">
                <work :in_transactions_date="{{ json_encode($transactions_date) }}" />
            </div>
        </div>
    </div>
</x-app-layout>
