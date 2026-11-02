@props(['code', 'title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-white p-6 antialiased dark:bg-zinc-900">
        <main class="w-full max-w-md space-y-6 text-center">
            <flux:text class="text-sm font-semibold uppercase tracking-widest">{{ $code }}</flux:text>

            <flux:heading size="xl" level="1">{{ $title }}</flux:heading>

            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
