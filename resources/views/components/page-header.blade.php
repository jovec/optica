@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @isset($eyebrow)
            <div class="mb-2">{{ $eyebrow }}</div>
        @endisset

        <h1 class="truncate text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
