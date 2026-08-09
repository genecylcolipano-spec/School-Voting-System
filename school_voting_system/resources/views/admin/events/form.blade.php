@php
    $event = $event ?? null;
    $isEdit = $event !== null;
@endphp
<x-app-layout>
    <x-admin-portal :title="$isEdit ? 'Edit Event' : 'Create Event'" :user="$user" :notifications-count="$notificationsCount">
        <form method="POST" action="{{ $isEdit ? route('admin.events.update', $event) : route('admin.events.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
            @csrf @if($isEdit) @method('PUT') @endif

            @include('admin.partials.form-input', ['label' => 'Title', 'name' => 'title', 'value' => optional($event)->title, 'required' => true])
            <div>
                <label class="block text-sm font-medium text-slate-300">Description</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('description', optional($event)->description) }}</textarea>
            </div>
            <x-event-image-field
                :src="$isEdit ? $event->image_url : \App\Support\EventImageUrl::placeholder()"
                :has-uploaded="$isEdit && $event->has_uploaded_image"
                :contain="$isEdit && $event->bannerNeedsContainLayout()"
                :orientation="$isEdit ? $event->imageOrientation() : null"
            >
                <input id="event-image-input" type="file" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500/20 file:px-3 file:py-1.5 file:text-sm file:text-cyan-300">
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">1600 × 900 px</span> · Landscape (16:9) · JPG or PNG · Max 2MB
                </p>
                @error('image')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </x-event-image-field>
            @include('admin.partials.form-input', ['label' => 'Event date', 'name' => 'event_date', 'type' => 'datetime-local', 'value' => optional(optional($event)->event_date)->format('Y-m-d\TH:i'), 'required' => true])
            @include('admin.partials.form-input', ['label' => 'Venue', 'name' => 'venue', 'value' => optional($event)->venue, 'required' => true])

            <div>
                <label class="block text-sm font-medium text-slate-300">Status</label>
                <select name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', optional($event)->status?->value) === $status->value)>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950">Save</button>
                <a href="{{ route('admin.events.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300">Cancel</a>
            </div>
        </form>

        @if ($isEdit)
            @can('delete', $event)
                <div class="mt-3 max-w-2xl">
                    <x-admin.delete-action
                        :action="route('admin.events.destroy', $event)"
                        button-class="rounded-xl border border-rose-500/40 px-5 py-2.5 text-sm font-semibold text-rose-300 hover:bg-rose-500/10"
                        label="Delete event"
                    />
                </div>
            @endcan
        @endif
    </x-admin-portal>

    @vite('resources/js/event-image-preview.js')
</x-app-layout>
