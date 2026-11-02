<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="lg:px-10! lg:py-8!">
        <div class="mx-auto w-full max-w-6xl">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
