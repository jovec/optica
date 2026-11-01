<?php

namespace App\Livewire\Forms;

use App\Models\Patient;
use Livewire\Form;

class PatientForm extends Form
{
    public ?Patient $patient = null;

    public string $first_name = '';

    public string $last_name = '';

    public ?string $date_of_birth = null;

    public ?string $phone = null;

    public ?string $email = null;

    public ?string $notes = null;

    /**
     * Get the validation rules for the patient fields.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Fill the fields with an existing patient for editing.
     */
    public function setPatient(Patient $patient): void
    {
        $this->patient = $patient;

        $this->first_name = $patient->first_name;
        $this->last_name = $patient->last_name;
        $this->date_of_birth = $patient->date_of_birth?->format('Y-m-d');
        $this->phone = $patient->phone;
        $this->email = $patient->email;
        $this->notes = $patient->notes;
    }

    /**
     * Validate the fields and create a new patient.
     */
    public function store(): Patient
    {
        $patient = Patient::create($this->validate());

        $this->reset();

        return $patient;
    }

    /**
     * Validate the fields and update the patient being edited.
     */
    public function update(): void
    {
        $this->patient?->update($this->validate());

        $this->reset();
    }
}
