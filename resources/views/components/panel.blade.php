@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-xs dark:border-white/10 dark:bg-zinc-900']) }}>
    @if ($title || isset($actions))
        <header class="flex items-center justify-between gap-4 border-b border-zinc-100 px-5 py-4 dark:border-white/5">
            <div>
                @if ($title)
                    <h2 class="text-[15px] font-semibold text-zinc-900 dark:text-white">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    {{ $slot }}
</section>
