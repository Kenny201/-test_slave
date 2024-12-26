@extends('layouts.app')

@section('title', 'Rows List')

@section('content')
    <div class="bg-white shadow-md rounded-lg p-6">

        <h1 class="text-2xl font-bold text-gray-800 mb-4">Импортированные данные (Rows)</h1>

        <table class="w-full border-collapse border border-gray-300">
            <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Название</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Дата</th>
            </tr>
            </thead>
            <tbody id="tableId">
            @foreach ($rows as $date => $row)
                <tr class="bg-gray-100">
                    <td class="border border-gray-300 px-4 py-2 font-bold" colspan="3">{{ $date }}</td>
                </tr>
                @foreach ($row as $r)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $r->id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $r->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $r->date }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>

        <div class="mt-6">
            {{ $rows->links('pagination::tailwind') }}
        </div>

        <div id="newRowsNotification" class="fixed bottom-4 right-4 bg-blue-500 text-white p-4 rounded-lg shadow-md"
             style="display: none;">
            <span id="newRowsMessage">Новые строки добавлены. </span>
            <button id="refreshButton" class="bg-white text-blue-500 px-4 py-2 rounded-lg">Обновить</button>
        </div>
    </div>
@endsection
