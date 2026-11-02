<x-layouts::error code="404" :title="__('Page not found')">
    <p>{{ __('The page you are looking for does not exist, or the link has expired.') }}</p>

    <flux:button :href="config('app.url')" variant="primary" icon="arrow-left">{{ __('Go to the home page') }}</flux:button>
</x-layouts::error>
