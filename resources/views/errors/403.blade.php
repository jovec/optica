<x-layouts::error code="403" :title="__('You do not have access')">
    @auth
        <flux:text>
            {{ __('You are logged in as :email, and this account does not work in this optics.', ['email' => auth()->user()->email]) }}
        </flux:text>

        @if (auth()->user()->organizations->isNotEmpty())
            <div class="space-y-2">
                <flux:text>{{ __('Your optics:') }}</flux:text>

                @foreach (auth()->user()->organizations as $organization)
                    <flux:button :href="$organization->url('/dashboard')" class="w-full">{{ $organization->name }}</flux:button>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full">{{ __('Log out and use another account') }}</flux:button>
        </form>
    @else
        <flux:text>{{ __('You are not allowed to open this page.') }}</flux:text>

        <flux:button :href="config('app.url')" variant="primary">{{ __('Go to the home page') }}</flux:button>
    @endauth
</x-layouts::error>
