<?php

use App\Models\Examination;
use App\Models\Organization;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    /**
     * How many months may pass before a patient should come back.
     */
    public const CHECKUP_AFTER_MONTHS = 12;

    /**
     * Get the number of patients.
     */
    #[Computed]
    public function patientsCount(): int
    {
        return Patient::count();
    }

    /**
     * Get the number of examinations done this month.
     */
    #[Computed]
    public function examinationsThisMonth(): int
    {
        return Examination::where('examined_at', '>=', now()->startOfMonth())->count();
    }

    /**
     * Get the number of patients who are due for a checkup.
     */
    #[Computed]
    public function dueCount(): int
    {
        return $this->dueForCheckup()->count();
    }

    /**
     * Get the latest examinations.
     *
     * @return Collection<int, Examination>
     */
    #[Computed]
    public function recentExaminations(): Collection
    {
        return Examination::with('patient', 'examiner')
            ->latest('examined_at')
            ->latest('id')
            ->limit(5)
            ->get();
    }

    /**
     * Get the patients who waited the longest since their last examination.
     *
     * @return Collection<int, Patient>
     */
    #[Computed]
    public function duePatients(): Collection
    {
        return $this->dueForCheckup()
            ->withMax('examinations', 'examined_at')
            ->orderBy('examinations_max_examined_at')
            ->limit(5)
            ->get();
    }

    /**
     * Get a greeting that fits the time of day.
     */
    #[Computed]
    public function greeting(): string
    {
        return match (true) {
            now()->hour < 12 => __('Good morning'),
            now()->hour < 18 => __('Good afternoon'),
            default => __('Good evening'),
        };
    }

    /**
     * Get the organization of the current request.
     */
    #[Computed]
    public function organization(): Organization
    {
        return app('currentOrganization');
    }

    /**
     * Patients who were examined before, but not in the last 12 months.
     *
     * @return Builder<Patient>
     */
    protected function dueForCheckup(): Builder
    {
        return Patient::query()
            ->whereHas('examinations')
            ->whereDoesntHave('examinations', function (Builder $query) {
                $query->where('examined_at', '>=', now()->subMonths(self::CHECKUP_AFTER_MONTHS)->startOfDay());
            });
    }
}; ?>

<div class="space-y-8">
    <x-page-header
        :title="$this->greeting.', '.str(auth()->user()->name)->before(' ')"
        :description="__('Here is what is happening at :organization today.', ['organization' => $this->organization->name])"
    >
        <x-slot:actions>
            <flux:button :href="route('patients.index')" icon="users" wire:navigate>{{ __('Patients') }}</flux:button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card :label="__('Patients')" :value="$this->patientsCount" icon="users" color="indigo" :hint="__('In your records')" />
        <x-stat-card :label="__('Examinations this month')" :value="$this->examinationsThisMonth" icon="eye" color="emerald" :hint="now()->translatedFormat('F Y')" />
        <x-stat-card :label="__('Due for a checkup')" :value="$this->dueCount" icon="bell-alert" color="amber" :hint="__('No examination in the last 12 months')" />
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <x-panel :title="__('Recent examinations')" :description="__('The latest prescriptions written in your optics')" class="lg:col-span-3">
            <ul class="divide-y divide-zinc-100 dark:divide-white/5">
                @forelse ($this->recentExaminations as $examination)
                    <li wire:key="exam-{{ $examination->id }}">
                        <a href="{{ route('patients.show', $examination->patient) }}" wire:navigate class="flex items-center gap-4 px-5 py-3.5 transition hover:bg-zinc-50 dark:hover:bg-white/5">
                            <x-initials-avatar :name="$examination->patient->first_name.' '.$examination->patient->last_name" />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $examination->patient->last_name }} {{ $examination->patient->first_name }}</p>
                                <p class="truncate font-mono text-xs text-zinc-500 dark:text-zinc-400">OD {{ $examination->prescription('od') }} · OS {{ $examination->prescription('os') }}</p>
                            </div>

                            <div class="hidden text-right sm:block">
                                <p class="text-sm text-zinc-700 tabular-nums dark:text-zinc-300">{{ $examination->examined_at->format('d.m.Y.') }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $examination->examiner?->name ?? '—' }}</p>
                            </div>
                        </a>
                    </li>
                @empty
                    <li>
                        <x-empty-state icon="eye" :title="__('No examinations yet.')" :description="__('Open a patient and add their first examination.')" />
                    </li>
                @endforelse
            </ul>
        </x-panel>

        <x-panel :title="__('Due for a checkup')" :description="__('Waiting the longest since their last visit')" class="lg:col-span-2">
            <ul class="divide-y divide-zinc-100 dark:divide-white/5">
                @forelse ($this->duePatients as $patient)
                    <li wire:key="due-{{ $patient->id }}">
                        <a href="{{ route('patients.show', $patient) }}" wire:navigate class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-zinc-50 dark:hover:bg-white/5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $patient->last_name }} {{ $patient->first_name }}</p>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $patient->phone ?? __('No phone') }}</p>
                            </div>

                            <flux:badge color="amber" size="sm">{{ Illuminate\Support\Carbon::parse($patient->examinations_max_examined_at)->format('d.m.Y.') }}</flux:badge>
                        </a>
                    </li>
                @empty
                    <li>
                        <x-empty-state icon="check-badge" :title="__('Nobody is due for a checkup.')" :description="__('Every patient was examined in the last 12 months.')" />
                    </li>
                @endforelse
            </ul>
        </x-panel>
    </div>
</div>
