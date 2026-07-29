@props(['cabecalhos' => []])

<div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                @foreach ($cabecalhos as $cabecalho)
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">{{ $cabecalho }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
