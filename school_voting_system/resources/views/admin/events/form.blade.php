@php
    $event = $event ?? null;
    $isEdit = $event !== null;
@endphp
<x-app-layout>
    <x-admin-portal :title="$isEdit ? 'Edit Event' : 'Create Event'" :user="$user" :notifications-count="$notificationsCount">
        <form method="POST" action="{{ $isEdit ? route('admin.events.update', $event) : route('admin.events.store') }}" enctype="multipart/form-data" class="max-w-2xl min-w-0 space-y-4 rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-4 sm:p-6">
            @csrf @if($isEdit) @method('PUT') @endif

            @include('admin.partials.form-input', ['label' => 'Title', 'name' => 'title', 'value' => optional($event)->title, 'required' => true])
            <div>
                <label class="block text-sm font-medium text-slate-300">Description</label>
                <textarea name="description" rows="4" autocapitalize="sentences" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('description', optional($event)->description) }}</textarea>
            </div>
            <x-event-image-field
                :src="$isEdit ? $event->image_url : \App\Support\EventImageUrl::placeholder()"
                :has-uploaded="$isEdit && $event->has_uploaded_image"
                :contain="$isEdit && $event->bannerNeedsContainLayout()"
                :orientation="$isEdit ? $event->imageOrientation() : null"
            >
                <input id="event-image-input" type="file" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:block file:w-full file:rounded-lg file:border-0 file:bg-cyan-500/20 file:px-3 file:py-1.5 file:text-sm file:text-cyan-300 sm:file:inline-block sm:file:w-auto">
                <p id="event-image-status" class="mt-1 text-xs text-slate-500"></p>
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">1600 × 900 px</span> · Landscape (16:9) · JPG or PNG · Max 2MB
                </p>
                @error('image')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </x-event-image-field>
            <div class="grid gap-4 sm:grid-cols-2">
                @include('admin.partials.form-input', ['label' => 'Event starts', 'name' => 'starts_at', 'type' => 'datetime-local', 'value' => optional(optional($event)->starts_at)->format('Y-m-d\TH:i'), 'required' => true])
                @include('admin.partials.form-input', ['label' => 'Event ends', 'name' => 'ends_at', 'type' => 'datetime-local', 'value' => optional(optional($event)->ends_at)->format('Y-m-d\TH:i'), 'required' => true])
            </div>
            @include('admin.partials.form-input', ['label' => 'Venue', 'name' => 'venue', 'value' => optional($event)->venue, 'required' => true])

            <div>
                <label for="event-status" class="block text-sm font-medium text-slate-300">Status</label>
                <select id="event-status" name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 [color-scheme:dark]">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', optional($event)->status?->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Follows the event dates automatically. Choose Cancelled to keep it cancelled.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 sm:w-auto">Save</button>
                <a href="{{ route('admin.events.index') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300 sm:w-auto">Cancel</a>
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
    <script>
        (() => {
            const start = document.getElementById('starts_at');
            const end = document.getElementById('ends_at');
            const status = document.getElementById('event-status');
            if (!start || !end || !status) {
                return;
            }

            const parseLocal = (value) => {
                if (!value) {
                    return null;
                }
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? null : date;
            };

            const syncStatusFromDates = () => {
                if (status.value === 'cancelled') {
                    return;
                }

                const startsAt = parseLocal(start.value);
                const endsAt = parseLocal(end.value);
                if (!startsAt) {
                    return;
                }

                const now = new Date();
                let next = 'scheduled';
                if (endsAt && endsAt < now) {
                    next = 'completed';
                } else if (startsAt <= now && (!endsAt || endsAt >= now)) {
                    next = 'ongoing';
                }

                if (status.value !== next) {
                    status.value = next;
                }
            };

            start.addEventListener('change', syncStatusFromDates);
            end.addEventListener('change', syncStatusFromDates);
            start.addEventListener('input', syncStatusFromDates);
            end.addEventListener('input', syncStatusFromDates);
        })();
    </script>
</x-app-layout>
