<?php

use App\Livewire\Forms\PatientForm;
use App\Models\Patient;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Patients')] class extends Component {
    use WithPagination;

    public string $search = '';

    public PatientForm $form;

    /**
     * Make sure the user can see the list of patients.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Patient::class);
    }

    /**
     * Go back to the first page when the search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the modal with an empty form.
     */
    public function create(): void
    {
        $this->authorize('create', Patient::class);

        $this->form->reset();
        $this->form->resetErrorBag();

        Flux::modal('patient-form')->show();
    }

    /**
     * Open the modal with the patient's data.
     */
    public function edit(int $id): void
    {
        $patient = Patient::findOrFail($id);

        $this->authorize('update', $patient);

        $this->form->resetErrorBag();
        $this->form->setPatient($patient);

        Flux::modal('patient-form')->show();
    }

    /**
     * Delete the patient if the user is allowed to.
     */
    public function delete(int $id): void
    {
        $patient = Patient::findOrFail($id);

        $this->authorize('delete', $patient);

        $patient->delete();
    }

    /**
     * Save the patient and close the modal.
     */
    public function save(): void
    {
        if ($this->form->patient) {
            $this->authorize('update', $this->form->patient);
            $this->form->update();
        } else {
            $this->authorize('create', Patient::class);
            $this->form->store();
        }

        Flux::modal('patient-form')->close();
    }

    /**
     * Get the patients of the current organization.
     *
     * @return LengthAwarePaginator<int, Patient>
     */
    #[Computed]
    public function patients(): LengthAwarePaginator
    {
        return Patient::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);
    }
}; ?>

<div class="space-y-8">
    <x-page-header :title="__('Patients')" :description="__('Everyone who has visited your optics, in one place.')">
        <x-slot:actions>
            @can('create', App\Models\Patient::class)
                <flux:button wire:click="create" variant="primary" icon="plus">{{ __('New patient') }}</flux:button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-panel>
        <div class="flex flex-col gap-3 border-b border-zinc-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/5">
            <div class="w-full sm:max-w-sm">
                <flux:input wire:model.live.debounce.250ms="search" icon="magnifying-glass" :placeholder="__('Search patients...')" clearable />
            </div>

            <p class="text-sm text-zinc-500 tabular-nums dark:text-zinc-400">
                {{ trans_choice(':count patient|:count patients', $this->patients->total(), ['count' => $this->patients->total()]) }}
            </p>
        </div>

        @if ($this->patients->isEmpty())
            <x-empty-state
                icon="users"
                :title="$search !== '' ? __('No patients match your search.') : __('No patients yet.')"
                :description="$search !== '' ? __('Try a different name.') : __('Add your first patient to start keeping records.')"
            />
        @else
            <div class="px-5 pb-2">
                <flux:table :paginate="$this->patients">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column class="max-md:hidden">{{ __('Date of birth') }}</flux:table.column>
                        <flux:table.column class="max-sm:hidden">{{ __('Phone') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->patients as $patient)
                            <flux:table.row :key="$patient->id">
                                <flux:table.cell>
                                    <a href="{{ route('patients.show', $patient) }}" wire:navigate class="group flex items-center gap-3">
                                        <x-initials-avatar :name="$patient->first_name.' '.$patient->last_name" />

                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-zinc-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $patient->last_name }} {{ $patient->first_name }}</p>
                                            @if ($patient->email)
                                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $patient->email }}</p>
                                            @endif
                                        </div>
                                    </a>
                                </flux:table.cell>
                                <flux:table.cell class="tabular-nums max-md:hidden">{{ $patient->date_of_birth?->format('d.m.Y.') ?? '—' }}</flux:table.cell>
                                <flux:table.cell class="tabular-nums max-sm:hidden">{{ $patient->phone ?? '—' }}</flux:table.cell>
                                <flux:table.cell align="end">
                                    <div class="flex justify-end gap-1">
                                        @can('update', $patient)
                                            <flux:button wire:click="edit({{ $patient->id }})" variant="ghost" size="sm" icon="pencil-square" class="text-zinc-400!" :tooltip="__('Edit')" :aria-label="__('Edit')" />
                                        @endcan

                                        @can('delete', $patient)
                                            <flux:button
                                                wire:click="delete({{ $patient->id }})"
                                                wire:confirm="{{ __('Are you sure you want to delete this patient?') }}"
                                                variant="ghost"
                                                size="sm"
                                                icon="trash"
                                                class="text-zinc-400! hover:text-red-600!"
                                                :tooltip="__('Delete')"
                                                :aria-label="__('Delete')"
                                            />
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @endif
    </x-panel>

    <x-patients.form-modal :editing="$form->patient !== null" />
</div>
