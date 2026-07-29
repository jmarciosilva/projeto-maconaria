<x-layouts.restrito titulo="Meu Perfil">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Meu Perfil</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="max-w-xl rounded-lg bg-white p-4 shadow-sm sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="max-w-xl rounded-lg bg-white p-4 shadow-sm sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="max-w-xl rounded-lg bg-white p-4 shadow-sm sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-layouts.restrito>
