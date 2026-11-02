@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-linear-to-br from-indigo-500 to-violet-600 text-white shadow-sm shadow-indigo-500/30">
            <x-app-logo-icon class="size-5" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <a {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
        <span class="flex size-8 items-center justify-center rounded-lg bg-linear-to-br from-indigo-500 to-violet-600 text-white shadow-sm shadow-indigo-500/30">
            <x-app-logo-icon class="size-5" />
        </span>
        <span class="text-[15px] font-semibold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name', 'Laravel') }}</span>
    </a>
@endif
