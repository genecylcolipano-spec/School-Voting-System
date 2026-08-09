@props([
    'title' => 'Account Summary',
    'borderClass' => 'border-cyan-500/15',
    'rows' => [],
])

<section {{ $attributes->merge(['class' => "rounded-2xl border {$borderClass} bg-slate-900/70 p-5 sm:p-6"]) }}>
    <h2 class="text-lg font-semibold text-white">{{ $title }}</h2>
    <dl class="mt-4 space-y-3 text-sm">
        @foreach ($rows as $row)
            <div @class([
                'flex justify-between gap-3',
                'border-b border-slate-800 pb-2' => ! $loop->last,
            ])>
                <dt class="text-slate-500">{{ $row['label'] }}</dt>
                <dd @class(['font-medium text-right', $row['valueClass'] ?? 'text-slate-200'])>
                    {{ $row['value'] }}
                </dd>
            </div>
        @endforeach
    </dl>
    {{ $slot }}
</section>
