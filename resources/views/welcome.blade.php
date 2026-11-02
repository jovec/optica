<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-900 dark:text-white">
        <header class="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
            <x-app-logo />

            <nav class="flex items-center gap-2">
                @auth
                    <flux:button :href="route('organizations.open')" variant="primary">{{ __('My optics') }}</flux:button>
                @else
                    <flux:button :href="route('login')" variant="ghost">{{ __('Log in') }}</flux:button>
                    <flux:button :href="route('register')" variant="primary">{{ __('Register your optics') }}</flux:button>
                @endauth
            </nav>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-20 text-center">
            <flux:heading size="xl" level="1" class="text-4xl! font-bold">
                {{ __('Patient records for your optics') }}
            </flux:heading>

            <flux:text class="mx-auto mt-6 max-w-2xl text-lg">
                {{ __('Keep patients, eye examinations and prescriptions in one place. Your team works on your own address, and nobody else can see your data.') }}
            </flux:text>

            <div class="mt-10 flex justify-center">
                <flux:button :href="route('register')" variant="primary" icon-trailing="arrow-right">
                    {{ __('Start for free') }}
                </flux:button>
            </div>

            <div class="mt-20 grid gap-6 text-left sm:grid-cols-3">
                <flux:card>
                    <flux:heading>{{ __('Patients') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Search, add and edit patients in seconds.') }}</flux:text>
                </flux:card>
                <flux:card>
                    <flux:heading>{{ __('Examinations') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Record SPH, CYL, AXIS, ADD and PD, and print the prescription.') }}</flux:text>
                </flux:card>
                <flux:card>
                    <flux:heading>{{ __('Team') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Invite doctors and staff, each with their own role.') }}</flux:text>
                </flux:card>
            </div>
        </main>

        @fluxScripts
    </body>
</html>
