<?php

use App\Livewire\Forms\PatientForm;
use App\Models\Patient;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Patient')] class extends Component {
    public Patient $patient;

    public PatientForm $form;

    /**
     * Make sure the user can see the patient.
     */
    public function mount(Patient $patient): void
    {
        $this->authorize('view', $patient);

        $this->patient = $patient;
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

    <x-patients.form-modal :editing="true" />
</div>
