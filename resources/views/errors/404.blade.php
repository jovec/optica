<x-layouts::error code="404" :title="__('Page not found')">
    <flux:text>{{ __('The page you are looking for does not exist, or the link has expired.') }}</flux:text>

    <flux:button :href="config('app.url')" variant="primary">{{ __('Go to the home page') }}</flux:button>
</x-layouts::error>
