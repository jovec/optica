@props(['name'])

@php
    $palette = [
        'from-indigo-500 to-violet-500',
        'from-sky-500 to-indigo-500',
        'from-emerald-500 to-teal-500',
        'from-amber-500 to-orange-500',
        'from-rose-500 to-pink-500',
        'from-fuchsia-500 to-purple-500',
    ];

    $gradient = $palette[crc32($name) % count($palette)];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-linear-to-br {$gradient} text-xs font-semibold text-white"]) }}>
    {{ Illuminate\Support\Str::substr(Illuminate\Support\Str::initials($name), 0, 2) }}
</span>
