@props(['role'])

@php
    $color = match ($role) {
        App\Enums\OrganizationRole::Owner => 'indigo',
        App\Enums\OrganizationRole::Doctor => 'emerald',
        default => 'zinc',
    };
@endphp

<flux:badge :color="$color" size="sm" inset="top bottom">{{ $role?->label() }}</flux:badge>
