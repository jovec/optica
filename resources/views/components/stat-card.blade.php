@props([
    'label',
    'value',
    'icon',
    'color' => 'indigo',
    'hint' => null,
])

@php
    $tones = [
        'indigo' => 'bg-indigo-50 text-indigo-600 ring-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
        'sky' => 'bg-sky-50 text-sky-600 ring-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-white/10 dark:bg-zinc-900']) }}>
    <div class="flex items-start justify-between">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</p>

        <span class="flex size-9 items-center justify-center rounded-xl ring-1 ring-inset {{ $tones[$color] }}">
            <flux:icon :name="$icon" variant="mini" class="size-5" />
        </span>
    </div>

    <p class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900 tabular-nums dark:text-white">{{ $value }}</p>

    @if ($hint)
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</p>
    @endif
</div>
