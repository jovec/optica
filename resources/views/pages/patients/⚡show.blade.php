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

<div class="space-y-6">
    <div>
        <flux:link :href="route('patients.index')" variant="subtle" wire:navigate>← {{ __('Patients') }}</flux:link>

        <div class="mt-2 flex items-center justify-between">
            <flux:heading size="xl">{{ $patient->last_name }} {{ $patient->first_name }}</flux:heading>

            <div class="flex gap-2">
                @can('update', $patient)
                    <flux:button wire:click="edit" icon="pencil-square">{{ __('Edit') }}</flux:button>
                @endcan

                @can('delete', $patient)
                    <flux:button
                        wire:click="delete"
                        wire:confirm="{{ __('Are you sure you want to delete this patient?') }}"
                        variant="danger"
                        icon="trash"
                    >
                        {{ __('Delete') }}
                    </flux:button>
                @endcan
            </div>
        </div>
    </div>

    <flux:card class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <flux:text>{{ __('Date of birth') }}</flux:text>
                <flux:heading>{{ $patient->date_of_birth?->format('d.m.Y.') ?? '—' }}</flux:heading>
            </div>
            <div>
                <flux:text>{{ __('Phone') }}</flux:text>
                <flux:heading>{{ $patient->phone ?? '—' }}</flux:heading>
            </div>
            <div>
                <flux:text>{{ __('Email') }}</flux:text>
                <flux:heading>{{ $patient->email ?? '—' }}</flux:heading>
            </div>
        </div>

        <div>
            <flux:text>{{ __('Notes') }}</flux:text>
            <flux:heading class="whitespace-pre-line">{{ $patient->notes ?? '—' }}</flux:heading>
        </div>
    </flux:card>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Examinations') }}</flux:heading>

            @can('create', App\Models\Examination::class)
                <flux:button wire:click="createExamination" variant="primary" icon="plus">{{ __('New examination') }}</flux:button>
            @endcan
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>OD</flux:table.column>
                <flux:table.column>OS</flux:table.column>
                <flux:table.column>ADD</flux:table.column>
                <flux:table.column>PD</flux:table.column>
                <flux:table.column>{{ __('Examined by') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->examinations as $examination)
                    <flux:table.row :key="$examination->id">
                        <flux:table.cell>{{ $examination->examined_at->format('d.m.Y.') }}</flux:table.cell>
                        <flux:table.cell class="font-mono">{{ $examination->prescription('od') }}</flux:table.cell>
                        <flux:table.cell class="font-mono">{{ $examination->prescription('os') }}</flux:table.cell>
                        <flux:table.cell class="font-mono">{{ $examination->od_addition ? Examination::formatDiopter($examination->od_addition) : '—' }}</flux:table.cell>
                        <flux:table.cell class="font-mono">{{ $examination->pd ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $examination->examiner?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button :href="route('examinations.print', $examination)" target="_blank" variant="ghost" size="sm" icon="printer" :aria-label="__('Print')" />

                            @can('update', $examination)
                                <flux:button wire:click="editExamination({{ $examination->id }})" variant="ghost" size="sm" icon="pencil-square" :aria-label="__('Edit')" />
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7">{{ __('No examinations yet.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <x-patients.form-modal :editing="true" />
    <x-examinations.form-modal :editing="$examinationForm->examination !== null" />
</div>
