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

<div class="space-y-6">
    <flux:heading size="xl">{{ $this->organization->name }}</flux:heading>

    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <flux:text>{{ __('Patients') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $this->patientsCount }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text>{{ __('Examinations this month') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $this->examinationsThisMonth }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text>{{ __('Due for a checkup') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $this->dueCount }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Recent examinations') }}</flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Patient') }}</flux:table.column>
                    <flux:table.column>{{ __('Examined by') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->recentExaminations as $examination)
                        <flux:table.row :key="$examination->id">
                            <flux:table.cell>{{ $examination->examined_at->format('d.m.Y.') }}</flux:table.cell>
                            <flux:table.cell variant="strong">
                                <flux:link :href="route('patients.show', $examination->patient)" variant="ghost" wire:navigate>
                                    {{ $examination->patient->last_name }} {{ $examination->patient->first_name }}
                                </flux:link>
                            </flux:table.cell>
                            <flux:table.cell>{{ $examination->examiner?->name ?? '—' }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3">{{ __('No examinations yet.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Due for a checkup') }}</flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Patient') }}</flux:table.column>
                    <flux:table.column>{{ __('Phone') }}</flux:table.column>
                    <flux:table.column>{{ __('Last examination') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->duePatients as $patient)
                        <flux:table.row :key="$patient->id">
                            <flux:table.cell variant="strong">
                                <flux:link :href="route('patients.show', $patient)" variant="ghost" wire:navigate>
                                    {{ $patient->last_name }} {{ $patient->first_name }}
                                </flux:link>
                            </flux:table.cell>
                            <flux:table.cell>{{ $patient->phone ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ Illuminate\Support\Carbon::parse($patient->examinations_max_examined_at)->format('d.m.Y.') }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3">{{ __('Nobody is due for a checkup.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
