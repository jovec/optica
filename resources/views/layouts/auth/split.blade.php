<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-950">
        <div class="grid min-h-dvh lg:grid-cols-2">
            <div class="flex flex-col px-6 py-8 sm:px-12">
                <x-app-logo :href="config('app.url')" />

                <div class="flex flex-1 items-center justify-center py-12">
                    <div class="w-full max-w-sm">
                        {{ $slot }}
                    </div>
                </div>

                <p class="text-xs text-zinc-400">© {{ now()->year }} {{ config('app.name') }}</p>
            </div>

            <div class="relative hidden overflow-hidden bg-zinc-950 lg:block">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,var(--color-indigo-500)_0%,transparent_55%),radial-gradient(ellipse_at_bottom_left,var(--color-violet-600)_0%,transparent_50%)] opacity-70"></div>
                <div class="bg-grid absolute inset-0 opacity-40 [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>

                <div class="relative flex h-full flex-col justify-between p-12 text-white">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-medium backdrop-blur">
                        <span class="size-1.5 rounded-full bg-emerald-400"></span>
                        {{ __('Built for optics') }}
                    </span>

                    <div class="mx-auto w-full max-w-md">
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-5 shadow-2xl backdrop-blur-xl">
                            <div class="flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-full bg-linear-to-br from-sky-400 to-indigo-500 text-sm font-semibold">EW</span>
                                <div>
                                    <p class="text-sm font-semibold">Emma Wilson</p>
                                    <p class="text-xs text-white/60">{{ __('Examination') }} · 02.11.2026.</p>
                                </div>
                                <span class="ms-auto rounded-full bg-emerald-400/20 px-2 py-0.5 text-[11px] font-medium text-emerald-300">{{ __('Printed') }}</span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2 font-mono text-sm">
                                <div class="rounded-xl bg-white/10 px-3 py-2"><p class="font-sans text-[10px] text-white/50">OD</p>-1.25 / -0.50 x 90</div>
                                <div class="rounded-xl bg-white/10 px-3 py-2"><p class="font-sans text-[10px] text-white/50">OS</p>-1.00 / -0.25 x 85</div>
                            </div>
                        </div>

                        <div class="-mt-3 ms-8 me-4 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-xl">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-white/70">{{ __('Examinations this month') }}</span>
                                <span class="font-semibold">128</span>
                            </div>
                            <div class="mt-3 flex h-10 items-end gap-1.5">
                                @foreach ([30, 45, 35, 60, 50, 75, 65, 90, 70, 100] as $height)
                                    <span class="flex-1 rounded-t bg-linear-to-t from-indigo-400/40 to-indigo-300" style="height: {{ $height }}%"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-2xl font-semibold tracking-tight">{{ __('Patients, examinations and prescriptions. In one calm place.') }}</p>
                        <p class="mt-2 text-sm text-white/60">{{ __('Each optics gets its own address. Nobody else can see your data.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
