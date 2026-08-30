@php
    $phoneValue = old('phone', $phoneValue ?? '');
    $phoneHint = $phoneHint ?? 'Used if they cannot receive email — Super Admin can see this on their profile and share a passkey link or call.';
@endphp

<div>
    <label for="phone" class="block text-sm font-medium text-slate-300">Phone Number <span class="text-slate-500">(optional)</span></label>
    <input id="phone" name="phone" type="tel" value="{{ $phoneValue }}"
        autocomplete="tel"
        placeholder="+63…"
        class="{{ $inputClass ?? 'mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20' }}">
    <p class="mt-1 text-xs text-slate-500">{{ $phoneHint }}</p>
    @error('phone')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
</div>
