@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#14213D] focus:ring-[#C9A227] rounded-md shadow-sm']) }}>
