<?php

use App\Livewire\Forms\ExaminationForm;
use App\Livewire\Forms\PatientForm;
use App\Models\Examination;
use App\Models\Patient;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Patient')] class extends Component {
    public Patient $patient;

    public PatientForm $form;

    public ExaminationForm $examinationForm;

    /**
     * Make sure the user can see the patient.
     */
    public function mount(Patient $patient): void
    {
        $this->authorize('view', $patient);

        $this->patient = $patient;
    }

    /**
     * Get the patient's examinations with the doctor who did them.
     *
     * @return Collection<int, Examination>
     */
    #[Computed]
    public function examinations(): Collection
    {
        return $this->patient->examinations()->with('examiner')->get();
    }

    /**
     * Open the examination modal with an empty form.
     */
    public function createExamination(): void
    {
        $this->authorize('create', Examination::class);

        $this->examinationForm->resetErrorBag();
        $this->examinationForm->start();

        Flux::modal('examination-form')->show();
    }

    /**
     * Open the examination modal with the examination's data.
     */
    public function editExamination(int $id): void
    {
        $examination = $this->patient->examinations()->findOrFail($id);

        $this->authorize('update', $examination);

        $this->examinationForm->resetErrorBag();
        $this->examinationForm->setExamination($examination);

        Flux::modal('examination-form')->show();
    }

    /**
     * Save the examination and close the modal.
     */
    public function saveExamination(): void
    {
        if ($this->examinationForm->examination) {
            $this->authorize('update', $this->examinationForm->examination);
            $this->examinationForm->update();
        } else {
            $this->authorize('create', Examination::class);
            $this->examinationForm->store($this->patient);
        }

        Flux::modal('examination-form')->close();
    }

    /**
     * Open the modal with the patient's data.
     */
    public function edit(): void
    {
        $this->authorize('update', $this->patient);

        $this->form->resetErrorBag();
        $this->form->setPatient($this->patient);

        Flux::modal('patient-form')->show();
    }

    /**
     * Save the changes and close the modal.
     */
    public function save(): void
    {
        $this->authorize('update', $this->patient);

        $this->form->update();
        $this->patient->refresh();

        Flux::modal('patient-form')->close();
    }

    /**
     * Delete the patient and go back to the list.
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->patient);

        $this->patient->delete();

        $this->redirectRoute('patients.index', navigate: true);
    }
}; ?>

<div class="space-y-8">
    <div>
        <a href="{{ route('patients.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-medium text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
            <flux:icon.arrow-left variant="micro" />
            {{ __('Patients') }}
        </a>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <x-initials-avatar :name="$patient->first_name.' '.$patient->last_name" class="size-14! text-lg!" />

                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $patient->last_name }} {{ $patient->first_name }}</h1>

                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                        @if ($patient->date_of_birth)
                            <span>{{ trans_choice(':count year|:count years', $patient->date_of_birth->age, ['count' => $patient->date_of_birth->age]) }}</span>
                            <span class="text-zinc-300 dark:text-zinc-600">•</span>
                        @endif
                        <span>{{ trans_choice(':count examination|:count examinations', $this->examinations->count(), ['count' => $this->examinations->count()]) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                @can('update', $patient)
                    <flux:button wire:click="edit" icon="pencil-square">{{ __('Edit') }}</flux:button>
                @endcan

                @can('delete', $patient)
                    <flux:button
                        wire:click="delete"
                        wire:confirm="{{ __('Are you sure you want to delete this patient?') }}"
                        variant="ghost"
                        icon="trash"
                        class="text-red-600! hover:bg-red-50! dark:hover:bg-red-500/10!"
                    >
                        {{ __('Delete') }}
                    </flux:button>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-panel :title="__('Details')" class="h-fit">
            <dl class="divide-y divide-zinc-100 dark:divide-white/5">
                @foreach ([
                    ['calendar', __('Date of birth'), $patient->date_of_birth?->format('d.m.Y.')],
                    ['phone', __('Phone'), $patient->phone],
                    ['envelope', __('Email'), $patient->email],
                ] as [$icon, $label, $value])
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <flux:icon :name="$icon" variant="mini" class="size-4 text-zinc-400" />
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</dt>
                        <dd class="ms-auto truncate text-sm font-medium text-zinc-900 tabular-nums dark:text-white">{{ $value ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="border-t border-zinc-100 px-5 py-4 dark:border-white/5">
                <p class="text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400">{{ __('Notes') }}</p>
                <p class="mt-2 text-sm whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $patient->notes ?? '—' }}</p>
            </div>
        </x-panel>

        <x-panel :title="__('Examinations')" :description="__('Newest first')" class="lg:col-span-2">
            <x-slot:actions>
                @can('create', App\Models\Examination::class)
                    <flux:button wire:click="createExamination" variant="primary" size="sm" icon="plus">{{ __('New examination') }}</flux:button>
                @endcan
            </x-slot:actions>

            <ul class="divide-y divide-zinc-100 dark:divide-white/5">
                @forelse ($this->examinations as $examination)
                    <li wire:key="examination-{{ $examination->id }}" class="px-5 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 ring-inset dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20">
                                    <flux:icon.eye variant="mini" class="size-4" />
                                </span>

                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 tabular-nums dark:text-white">{{ $examination->examined_at->format('d.m.Y.') }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $examination->examiner?->name ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="flex gap-1">
                                <flux:button :href="route('examinations.print', $examination)" target="_blank" variant="ghost" size="sm" icon="printer" :tooltip="__('Print')" :aria-label="__('Print')" />

                                @can('update', $examination)
                                    <flux:button wire:click="editExamination({{ $examination->id }})" variant="ghost" size="sm" icon="pencil-square" :tooltip="__('Edit')" :aria-label="__('Edit')" />
                                @endcan
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1.6fr)_minmax(0,1fr)_minmax(0,1fr)]">
                            <div class="rounded-xl bg-zinc-50 px-3 py-2 dark:bg-white/5">
                                <p class="text-[11px] font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400">OD</p>
                                <p class="font-mono text-sm text-zinc-900 dark:text-white">{{ $examination->prescription('od') }}</p>
                            </div>
                            <div class="rounded-xl bg-zinc-50 px-3 py-2 dark:bg-white/5">
                                <p class="text-[11px] font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400">OS</p>
                                <p class="font-mono text-sm text-zinc-900 dark:text-white">{{ $examination->prescription('os') }}</p>
                            </div>
                            <div class="rounded-xl bg-zinc-50 px-3 py-2 dark:bg-white/5">
                                <p class="text-[11px] font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400">ADD</p>
                                <p class="font-mono text-sm text-zinc-900 dark:text-white">{{ $examination->diopter('od_addition') }}</p>
                            </div>
                            <div class="rounded-xl bg-zinc-50 px-3 py-2 dark:bg-white/5">
                                <p class="text-[11px] font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400">PD</p>
                                <p class="font-mono text-sm text-zinc-900 dark:text-white">{{ $examination->pd ? $examination->pd.' mm' : '—' }}</p>
                            </div>
                        </div>

                        @if ($examination->notes)
                            <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $examination->notes }}</p>
                        @endif
                    </li>
                @empty
                    <li>
                        <x-empty-state icon="eye" :title="__('No examinations yet.')" :description="__('Examinations you add will show up here, newest first.')" />
                    </li>
                @endforelse
            </ul>
        </x-panel>
    </div>

    <x-patients.form-modal :editing="true" />
    <x-examinations.form-modal :editing="$examinationForm->examination !== null" />
</div>
