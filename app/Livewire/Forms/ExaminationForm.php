<?php

namespace App\Livewire\Forms;

use App\Models\Examination;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class ExaminationForm extends Form
{
    public ?Examination $examination = null;

    public string $examined_at = '';

    public ?string $od_sphere = null;

    public ?string $od_cylinder = null;

    public ?string $od_axis = null;

    public ?string $od_addition = null;

    public ?string $os_sphere = null;

    public ?string $os_cylinder = null;

    public ?string $os_axis = null;

    public ?string $os_addition = null;

    public ?string $pd = null;

    public ?string $notes = null;

    /**
     * Get the validation rules for the examination fields.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        $rules = [
            'examined_at' => ['required', 'date', 'before_or_equal:today'],
            'pd' => ['nullable', 'numeric', 'between:40,80'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];

        foreach (['od', 'os'] as $eye) {
            $rules["{$eye}_sphere"] = ['nullable', 'numeric', 'between:-30,30', 'multiple_of:0.25'];
            $rules["{$eye}_cylinder"] = ['nullable', 'numeric', 'between:-10,10', 'multiple_of:0.25'];
            $rules["{$eye}_axis"] = ['nullable', "required_with:{$eye}_cylinder", 'integer', 'between:1,180'];
            $rules["{$eye}_addition"] = ['nullable', 'numeric', 'between:0.25,4', 'multiple_of:0.25'];
        }

        return $rules;
    }

    /**
     * Start a new examination dated today.
     */
    public function start(): void
    {
        $this->reset();

        $this->examined_at = now()->format('Y-m-d');
    }

    /**
     * Fill the fields with an existing examination for editing.
     */
    public function setExamination(Examination $examination): void
    {
        $this->examination = $examination;

        $this->examined_at = $examination->examined_at->format('Y-m-d');
        $this->pd = $examination->pd;
        $this->notes = $examination->notes;

        foreach (['od', 'os'] as $eye) {
            foreach (['sphere', 'cylinder', 'axis', 'addition'] as $field) {
                $value = $examination->getAttribute("{$eye}_{$field}");

                $this->{"{$eye}_{$field}"} = $value === null ? null : (string) $value;
            }
        }
    }

    /**
     * Validate the fields and add a new examination for the patient.
     */
    public function store(Patient $patient): Examination
    {
        $examination = $patient->examinations()->make($this->validated());
        $examination->examiner()->associate(Auth::user());
        $examination->save();

        $this->reset();

        return $examination;
    }

    /**
     * Validate the fields and update the examination being edited.
     */
    public function update(): void
    {
        $this->examination?->update($this->validated());

        $this->reset();
    }

    /**
     * Validate the fields and turn empty inputs into nulls.
     *
     * @return array<string, mixed>
     */
    protected function validated(): array
    {
        return array_map(
            fn ($value) => $value === '' ? null : $value,
            $this->validate(),
        );
    }
}
