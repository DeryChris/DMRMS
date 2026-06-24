<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gaf-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gaf-dark-green focus:bg-gaf-dark-green active:bg-gaf-green focus:outline-none focus:ring-2 focus:ring-gaf-green focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
