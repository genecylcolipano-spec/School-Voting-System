@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false, 'maxlength' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-300">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        {{ $attributes->merge(['class' => 'mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30']) }}
    />
    @error($name)
        <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
    @enderror
</div>
