<x-layouts::print :title="__('Prescription').' - '.$examination->patient->last_name">
    <header class="border-b border-zinc-300 pb-4">
        <h1 class="text-xl font-semibold">{{ $organization->name }}</h1>

        <p class="mt-1 text-sm text-zinc-600">
            {{ collect([$organization->address, $organization->city])->filter()->implode(', ') }}
        </p>
        <p class="text-sm text-zinc-600">
            {{ collect([$organization->phone, $organization->email])->filter()->implode(' · ') }}
        </p>
    </header>

    <h2 class="mt-6 text-center text-lg font-semibold uppercase tracking-wide">{{ __('Eyeglass prescription') }}</h2>

    <dl class="mt-6 grid grid-cols-2 gap-y-1 text-sm">
        <dt class="text-zinc-500">{{ __('Patient') }}</dt>
        <dd>{{ $examination->patient->last_name }} {{ $examination->patient->first_name }}</dd>

        <dt class="text-zinc-500">{{ __('Date of birth') }}</dt>
        <dd>{{ $examination->patient->date_of_birth?->format('d.m.Y.') ?? '—' }}</dd>

        <dt class="text-zinc-500">{{ __('Date') }}</dt>
        <dd>{{ $examination->examined_at->format('d.m.Y.') }}</dd>
    </dl>

    <table class="mt-6 w-full border-collapse text-center font-mono text-sm">
        <thead>
            <tr class="border-b border-zinc-400">
                <th class="py-2 text-left font-sans"></th>
                <th class="py-2">SPH</th>
                <th class="py-2">CYL</th>
                <th class="py-2">AXIS</th>
                <th class="py-2">ADD</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['od' => __('Right eye (OD)'), 'os' => __('Left eye (OS)')] as $eye => $label)
                <tr class="border-b border-zinc-200">
                    <th class="py-2 text-left font-sans font-normal">{{ $label }}</th>
                    <td class="py-2">{{ $examination->diopter("{$eye}_sphere") }}</td>
                    <td class="py-2">{{ $examination->diopter("{$eye}_cylinder") }}</td>
                    <td class="py-2">{{ $examination->getAttribute("{$eye}_axis") ?? '—' }}</td>
                    <td class="py-2">{{ $examination->diopter("{$eye}_addition") }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="mt-4 text-sm"><span class="text-zinc-500">PD:</span> <span class="font-mono">{{ $examination->pd ? $examination->pd.' mm' : '—' }}</span></p>

    @if ($examination->notes)
        <p class="mt-4 whitespace-pre-line text-sm">{{ $examination->notes }}</p>
    @endif

    <footer class="mt-16 flex justify-end">
        <div class="w-48 border-t border-zinc-400 pt-1 text-center text-sm">
            {{ $examination->examiner?->name ?? __('Signature') }}
        </div>
    </footer>
</x-layouts::print>
