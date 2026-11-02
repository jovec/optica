<x-mail::message>
# {{ __('Hello!') }}

{{ __(':inviter invited you to join :organization as :role.', [
    'inviter' => $invitation->inviter?->name ?? $organization->name,
    'organization' => $organization->name,
    'role' => $invitation->role->label(),
]) }}

<x-mail::button :url="$url">
{{ __('Accept invitation') }}
</x-mail::button>

{{ __('This invitation expires on :date.', ['date' => $invitation->expires_at->format('d.m.Y.')]) }}

{{ __('If you were not expecting this invitation, you can ignore this email.') }}
</x-mail::message>
