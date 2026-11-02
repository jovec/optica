<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-white">
        {{-- Navigation --}}
        <header class="sticky top-0 z-40 border-b border-zinc-200/60 bg-white/70 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/70">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <x-app-logo :href="route('home')" />

                <nav class="hidden items-center gap-8 text-sm text-zinc-600 md:flex dark:text-zinc-400">
                    <a href="#features" class="transition hover:text-zinc-900 dark:hover:text-white">{{ __('Features') }}</a>
                    <a href="#how" class="transition hover:text-zinc-900 dark:hover:text-white">{{ __('How it works') }}</a>
                    <a href="#pricing" class="transition hover:text-zinc-900 dark:hover:text-white">{{ __('Pricing') }}</a>
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <flux:button :href="route('organizations.open')" variant="primary" size="sm" icon-trailing="arrow-right">{{ __('My optics') }}</flux:button>
                    @else
                        <flux:button :href="route('login')" variant="ghost" size="sm">{{ __('Log in') }}</flux:button>
                        <flux:button :href="route('register')" variant="primary" size="sm">{{ __('Register your optics') }}</flux:button>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            {{-- Hero --}}
            <section class="relative isolate overflow-hidden">
                <div class="bg-grid absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>
                <div class="absolute -top-40 left-1/2 -z-10 h-[520px] w-[900px] -translate-x-1/2 rounded-full bg-linear-to-r from-indigo-400/30 via-violet-400/30 to-sky-400/30 blur-3xl dark:from-indigo-600/30 dark:via-violet-600/20 dark:to-sky-600/20"></div>

                <div class="mx-auto max-w-6xl px-6 pt-20 pb-16 text-center sm:pt-28">
                    <a href="#features" class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white/80 py-1 ps-1 pe-3 text-xs font-medium text-zinc-600 shadow-xs backdrop-blur transition hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                        <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[11px] text-white">{{ __('New') }}</span>
                        {{ __('Printable prescriptions with your logo') }}
                        <flux:icon.chevron-right variant="micro" />
                    </a>

                    <h1 class="mx-auto mt-8 max-w-3xl text-5xl font-semibold tracking-tight text-balance sm:text-6xl">
                        {{ __('Patient records for') }}
                        <span class="bg-linear-to-r from-indigo-600 via-violet-600 to-sky-500 bg-clip-text text-transparent dark:from-indigo-400 dark:via-violet-400 dark:to-sky-400">{{ __('modern optics') }}</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg text-pretty text-zinc-600 dark:text-zinc-400">
                        {{ __('Keep patients, eye examinations and prescriptions in one place. Your team works on your own address, and nobody else can see your data.') }}
                    </p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <flux:button :href="route('register')" variant="primary" icon-trailing="arrow-right" class="h-11! px-5!">{{ __('Start for free') }}</flux:button>
                        <flux:button href="#how" variant="ghost" class="h-11! px-5!">{{ __('See how it works') }}</flux:button>
                    </div>

                    <p class="mt-4 text-xs text-zinc-500">{{ __('No credit card. Ready in 2 minutes.') }}</p>
                </div>

                {{-- Product preview --}}
                <div class="mx-auto max-w-5xl px-6 pb-24">
                    <div class="rounded-2xl border border-zinc-200/80 bg-white/60 p-2 shadow-2xl shadow-indigo-500/10 ring-1 ring-black/5 backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <div class="overflow-hidden rounded-xl border border-zinc-200/80 bg-zinc-50 dark:border-white/10 dark:bg-zinc-900">
                            <div class="flex items-center gap-2 border-b border-zinc-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-zinc-900">
                                <span class="size-3 rounded-full bg-red-400"></span>
                                <span class="size-3 rounded-full bg-amber-400"></span>
                                <span class="size-3 rounded-full bg-emerald-400"></span>
                                <span class="mx-auto rounded-md bg-zinc-100 px-3 py-1 text-xs text-zinc-500 dark:bg-white/5">clearvision.{{ App\Support\CentralDomain::name() }}</span>
                            </div>

                            <div class="grid grid-cols-12">
                                <div class="col-span-3 hidden border-e border-zinc-200/80 bg-white p-4 text-left sm:block dark:border-white/10 dark:bg-zinc-900">
                                    <div class="space-y-1 text-sm">
                                        <div class="flex items-center gap-2 rounded-lg bg-zinc-100 px-2.5 py-1.5 font-medium dark:bg-white/10"><flux:icon.squares-2x2 variant="mini" class="size-4 text-indigo-600" /> {{ __('Dashboard') }}</div>
                                        <div class="flex items-center gap-2 px-2.5 py-1.5 text-zinc-500"><flux:icon.users variant="mini" class="size-4" /> {{ __('Patients') }}</div>
                                        <div class="flex items-center gap-2 px-2.5 py-1.5 text-zinc-500"><flux:icon.user-group variant="mini" class="size-4" /> {{ __('Team') }}</div>
                                    </div>
                                </div>

                                <div class="col-span-12 space-y-4 p-5 text-left sm:col-span-9">
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach ([[__('Patients'), '1,284', 'indigo'], [__('Examinations this month'), '128', 'emerald'], [__('Due for a checkup'), '37', 'amber']] as [$label, $value, $color])
                                            <div class="rounded-xl border border-zinc-200/80 bg-white p-3 dark:border-white/10 dark:bg-zinc-900">
                                                <p class="truncate text-[11px] text-zinc-500">{{ $label }}</p>
                                                <p class="mt-1 text-xl font-semibold tabular-nums">{{ $value }}</p>
                                                <div class="mt-2 h-1 rounded-full bg-zinc-100 dark:bg-white/10">
                                                    <div @class([
                                                        'h-1 rounded-full',
                                                        'w-3/4 bg-indigo-500' => $color === 'indigo',
                                                        'w-1/2 bg-emerald-500' => $color === 'emerald',
                                                        'w-1/4 bg-amber-500' => $color === 'amber',
                                                    ])></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="rounded-xl border border-zinc-200/80 bg-white dark:border-white/10 dark:bg-zinc-900">
                                        @foreach ([['Emma Wilson', '-1.25 / -0.50 x 90', 'from-sky-500 to-indigo-500'], ['Liam Johnson', '+1.50 / -0.75 x 180', 'from-emerald-500 to-teal-500'], ['Olivia Brown', '-3.00', 'from-rose-500 to-pink-500']] as [$person, $rx, $gradient])
                                            <div class="flex items-center gap-3 border-b border-zinc-100 px-4 py-3 last:border-0 dark:border-white/5">
                                                <span class="flex size-8 items-center justify-center rounded-full bg-linear-to-br {{ $gradient }} text-[11px] font-semibold text-white">{{ Illuminate\Support\Str::initials($person) }}</span>
                                                <span class="text-sm font-medium">{{ $person }}</span>
                                                <span class="ms-auto font-mono text-xs text-zinc-500">OD {{ $rx }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Features --}}
            <section id="features" class="border-t border-zinc-200/60 bg-zinc-50/60 py-24 dark:border-white/10 dark:bg-white/[0.02]">
                <div class="mx-auto max-w-6xl px-6">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Features') }}</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl">{{ __('Everything an optics needs. Nothing it does not.') }}</h2>
                    </div>

                    <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['users', __('Patient records'), __('Search, add and edit patients in seconds.')],
                            ['eye', __('Eye examinations'), __('Record SPH, CYL, AXIS, ADD and PD, and print the prescription.')],
                            ['printer', __('Printable prescriptions'), __('A5 prescription with your logo, address and the doctor\'s signature line.')],
                            ['user-group', __('Team and roles'), __('Invite doctors and staff, each with their own role.')],
                            ['bell-alert', __('Checkup reminders'), __('See at a glance who has not been in for over a year.')],
                            ['shield-check', __('Private by design'), __('Each optics gets its own address. Nobody else can see your data.')],
                        ] as [$icon, $title, $text])
                            <div class="group rounded-2xl border border-zinc-200/80 bg-white p-6 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-zinc-200/60 dark:border-white/10 dark:bg-zinc-900 dark:hover:shadow-none">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-linear-to-br from-indigo-500 to-violet-600 text-white shadow-sm shadow-indigo-500/30">
                                    <flux:icon :name="$icon" variant="mini" class="size-5" />
                                </span>
                                <h3 class="mt-5 font-semibold">{{ $title }}</h3>
                                <p class="mt-1.5 text-sm text-zinc-600 dark:text-zinc-400">{{ $text }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- How it works --}}
            <section id="how" class="py-24">
                <div class="mx-auto max-w-6xl px-6">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ __('How it works') }}</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl">{{ __('From sign up to first prescription in minutes') }}</h2>
                    </div>

                    <ol class="mt-14 grid gap-8 md:grid-cols-3">
                        @foreach ([
                            [__('Register your optics'), __('Pick a name and your own address, like clearvision.:domain.', ['domain' => App\Support\CentralDomain::name()])],
                            [__('Invite your team'), __('Doctors write examinations, staff look up patients and print.')],
                            [__('Examine and print'), __('Record the examination and hand the patient a clean prescription.')],
                        ] as $index => [$title, $text])
                            <li class="relative">
                                <span class="text-5xl font-semibold tracking-tight text-zinc-200 tabular-nums dark:text-white/10">0{{ $index + 1 }}</span>
                                <h3 class="mt-3 font-semibold">{{ $title }}</h3>
                                <p class="mt-1.5 text-sm text-zinc-600 dark:text-zinc-400">{{ $text }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Pricing / CTA --}}
            <section id="pricing" class="px-6 pb-24">
                <div class="relative mx-auto max-w-6xl overflow-hidden rounded-3xl bg-zinc-950 px-8 py-16 text-center text-white sm:px-16">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,var(--color-indigo-500)_0%,transparent_60%)] opacity-60"></div>

                    <div class="relative">
                        <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('Free while we are in early access') }}</h2>
                        <p class="mx-auto mt-4 max-w-xl text-white/70">{{ __('Register your optics today and keep every feature for free during early access.') }}</p>

                        <div class="mt-8 flex justify-center">
                            <a href="{{ route('register') }}" class="inline-flex h-11 items-center gap-2 rounded-lg bg-white px-5 text-sm font-semibold text-zinc-900 shadow-sm transition hover:bg-zinc-100">
                                {{ __('Start for free') }}
                                <flux:icon.arrow-right variant="micro" />
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-zinc-200/60 py-10 dark:border-white/10">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
                <x-app-logo :href="route('home')" />
                <p class="text-sm text-zinc-500">© {{ now()->year }} {{ config('app.name') }}</p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
