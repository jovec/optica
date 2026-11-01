@props(['editing' => false])

<flux:modal name="examination-form" class="md:w-2xl">
    <form wire:submit="saveExamination" class="space-y-6">
        <flux:heading size="lg">{{ $editing ? __('Edit examination') : __('New examination') }}</flux:heading>

        <flux:input wire:model="examinationForm.examined_at" :label="__('Date')" type="date" required class="max-w-48" />

        <div class="grid gap-6 sm:grid-cols-2">
            @foreach (['od' => __('Right eye (OD)'), 'os' => __('Left eye (OS)')] as $eye => $label)
                <flux:fieldset class="space-y-3">
                    <flux:legend>{{ $label }}</flux:legend>

                    <div class="grid grid-cols-2 gap-3">
                        <flux:input wire:model="examinationForm.{{ $eye }}_sphere" label="SPH" type="number" step="0.25" />
                        <flux:input wire:model="examinationForm.{{ $eye }}_cylinder" label="CYL" type="number" step="0.25" />
                        <flux:input wire:model="examinationForm.{{ $eye }}_axis" label="AXIS" type="number" step="1" min="1" max="180" />
                        <flux:input wire:model="examinationForm.{{ $eye }}_addition" label="ADD" type="number" step="0.25" min="0" />
                    </div>
                </flux:fieldset>
            @endforeach
        </div>

        <flux:input wire:model="examinationForm.pd" label="PD (mm)" type="number" step="0.5" class="max-w-48" />

        <flux:textarea wire:model="examinationForm.notes" :label="__('Notes')" rows="3" />

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
        </div>
    </form>
</flux:modal>
