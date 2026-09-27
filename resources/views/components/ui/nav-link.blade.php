@props(['href', 'ativo' => false])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'block rounded-md px-3 py-2 font-medium transition '.($ativo ? 'bg-[#C9A227] text-[#14213D]' : 'text-blue-100 hover:bg-[#1B2A4A]')]) }}
>
    {{ $slot }}
</a>
