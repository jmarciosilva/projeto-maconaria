<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#14213D] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1B2A4A] focus:bg-[#1B2A4A] active:bg-[#0B1526] focus:outline-none focus:ring-2 focus:ring-[#C9A227] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
