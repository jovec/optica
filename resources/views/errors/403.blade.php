<x-layouts::error code="403" :title="__('You do not have access')">
    @auth
        <p>{{ __('You are logged in as :email, and this account does not work in this optics.', ['email' => auth()->user()->email]) }}</p>

        @if (auth()->user()->organizations->isNotEmpty())
            <div class="rounded-2xl border border-zinc-200/80 bg-white p-2 text-left shadow-xs dark:border-white/10 dark:bg-zinc-900">
                <p class="px-3 pt-2 pb-1 text-xs font-medium tracking-wide text-zinc-500 uppercase">{{ __('Your optics:') }}</p>

                @foreach (auth()->user()->organizations as $organization)
                    <a href="{{ $organization->url('/dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-white/5">
                        <x-initials-avatar :name="$organization->name" class="size-8! rounded-lg!" />
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $organization->name }}</span>
                        <flux:icon.arrow-right variant="micro" class="ms-auto text-zinc-400" />
                    </a>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full">{{ __('Log out and use another account') }}</flux:button>
        </form>
    @else
        <p>{{ __('You are not allowed to open this page.') }}</p>

        <flux:button :href="config('app.url')" variant="primary">{{ __('Go to the home page') }}</flux:button>
    @endauth
</x-layouts::error>
