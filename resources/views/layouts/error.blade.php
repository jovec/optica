@props(['code', 'title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="relative isolate flex min-h-screen items-center justify-center overflow-hidden bg-white p-6 antialiased dark:bg-zinc-950">
        <div class="bg-grid absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_at_center,black,transparent_65%)]"></div>
        <div class="absolute top-1/2 left-1/2 -z-10 size-[480px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-400/20 blur-3xl dark:bg-indigo-600/20"></div>

        <main class="w-full max-w-md text-center">
            <p class="bg-linear-to-b from-indigo-500 to-violet-600 bg-clip-text text-7xl font-bold tracking-tight text-transparent">{{ $code }}</p>

            <h1 class="mt-4 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $title }}</h1>

            <div class="mt-4 space-y-6 text-sm text-zinc-600 dark:text-zinc-400">
                {{ $slot }}
            </div>
        </main>

        @fluxScripts
    </body>
</html>
