<?php

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Patients')] class extends Component {
    /**
     * Get the patients of the current organization.
     *
     * @return Collection<int, Patient>
     */
    #[Computed]
    public function patients(): Collection
    {
        return Patient::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="xl">{{ __('Patients') }}</flux:heading>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Date of birth') }}</flux:table.column>
            <flux:table.column>{{ __('Phone') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->patients as $patient)
                <flux:table.row :key="$patient->id">
                    <flux:table.cell variant="strong">{{ $patient->last_name }} {{ $patient->first_name }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->date_of_birth?->format('d.m.Y.') }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->phone }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->email }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">{{ __('No patients yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
