@props([
    'icon',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-white/5 dark:text-zinc-500">
        <flux:icon :name="$icon" class="size-6" />
    </span>

    <p class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
