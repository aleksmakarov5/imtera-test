<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Отчет по движению денежных средств
        </h2>
    </x-slot>
    <div class="container">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="overflow: auto;">


            <table class="table table-bordered " style="white-space: nowrap; font-size: 12px;">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Контрагент
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статья
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Сумма
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Счет
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Дата оплаты
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Описание
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Сделка
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статус
                        </th>
                </thead>
                <tbody>
                    @foreach ($transactions_date as $key => $transaction_date)
                        <tr>
                            <td colspan="8" style="font-size: 14px; font-style: bold;">{{ $key }}</td>
                        </tr>
                        @foreach ($transaction_date as $transaction)
                            <tr>
                                <td>@php
                                    echo $transaction->Kontragent;
                                @endphp </td>
                                <td>
                                    @if ($transaction->Type)
                                        Нераспределенные выплаты
                                    @else
                                        Нераспределенные поступления
                                    @endif
                                </td>
                                <td
                                    style=@if (!$transaction->Type) "color: green"
                                @else
                                    "color: red" @endif>
                                    @if ($transaction->Type)
                                        -
                                    @endif{{ $transaction->Summ }}
                                </td>
                                <td>{{ $transaction->Sch }}</td>
                                <td>{{ $transaction->Date }}</td>
                                <td>{{ $transaction->NazPay }}</td>
                                <td>{{ $transaction->deal_id }}</td>
                                <td>{{ $transaction->status_id }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                </tbody>
            </table>


        </div>
    </div>
</x-app-layout>
