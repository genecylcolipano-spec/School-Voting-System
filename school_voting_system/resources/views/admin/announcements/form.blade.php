@php
    $announcement = $announcement ?? null;
    $isEdit = $announcement !== null;
    $selectedAudiences = old('target_audiences', $isEdit ? ($announcement->target_audiences ?? [\App\Enums\AnnouncementAudience::AllUsers->value]) : [\App\Enums\AnnouncementAudience::AllUsers->value]);
    $relatedModule = old('related_module', $isEdit ? optional($announcement->related_module)->value : \App\Enums\AnnouncementRelatedModule::None->value);
    $relatedId = old('related_id', $isEdit ? $announcement->related_id : null);
    $resolvedStatus = $isEdit ? $announcement->resolvedStatus() : null;
@endphp
<x-app-layout>
    <x-admin-portal :title="$isEdit ? 'Edit Announcement' : 'New Announcement'" :user="$user" :notifications-count="$notificationsCount">
        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        @if ($isEdit && $announcement->is_auto_generated)
            <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                This announcement was auto-generated from a system event. Review and publish when ready.
            </div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-white">{{ $isEdit ? 'Edit Announcement' : 'Create Announcement' }}</h1>
                <p class="mt-1 text-sm text-slate-400">Communication Center — publish targeted announcements with banners, attachments, and notifications.</p>
            </div>
            <a href="{{ route('admin.announcements.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to announcements</a>
        </div>

        <form method="POST" action="{{ $isEdit ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @if($isEdit) @method('PUT') @endif

            {{-- Section 1: Announcement Information --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Announcement Information</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        @include('admin.partials.form-input', ['label' => 'Title', 'name' => 'title', 'value' => optional($announcement)->title, 'required' => true])
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Category</label>
                        <select name="category" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($categories as $category)
                                <option value="{{ $category->value }}" @selected(old('category', optional($announcement)->category?->value ?? \App\Enums\AnnouncementCategory::General->value) === $category->value)>{{ $category->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Priority</label>
                        <select name="priority" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority', optional($announcement)->priority?->value ?? \App\Enums\AnnouncementPriority::Normal->value) === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Summary</label>
                        <textarea name="summary" rows="2" maxlength="500" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('summary', optional($announcement)->summary) }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Body</label>
                        <textarea name="body" rows="8" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('body', optional($announcement)->body) }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Section 2: Audience --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Audience</h2>
                <p class="mt-1 text-xs text-slate-500">Only selected recipients will receive notifications and see targeted announcements.</p>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($audiences as $audience)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2 text-sm text-slate-300">
                            <input type="checkbox" name="target_audiences[]" value="{{ $audience->value }}" @checked(in_array($audience->value, $selectedAudiences, true)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                            {{ $audience->label() }}
                        </label>
                    @endforeach
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Target Grade Level</label>
                        <select name="target_grade_level" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            <option value="">— Select grade —</option>
                            @foreach ($gradeLevels as $grade)
                                <option value="{{ $grade }}" @selected(old('target_grade_level', optional($announcement)->target_grade_level) === $grade)>Grade {{ $grade }}</option>
                            @endforeach
                        </select>
                        @error('target_grade_level')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Target Section</label>
                        <select name="target_section" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            <option value="">— Select section —</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section }}" @selected(old('target_section', optional($announcement)->target_section) === $section)>{{ $section }}</option>
                            @endforeach
                        </select>
                        @error('target_section')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Section 3: Related Module --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5" x-data="announcementRelatedModule(@js([
                'module' => $relatedModule,
                'relatedId' => $relatedId,
                'elections' => $elections->map(fn ($r) => ['id' => $r->id, 'title' => $r->title])->values(),
                'talentEvents' => $talentEvents->map(fn ($r) => ['id' => $r->id, 'title' => $r->title])->values(),
                'events' => $events->map(fn ($r) => ['id' => $r->id, 'title' => $r->title])->values(),
                'fundraisers' => $fundraisers->map(fn ($r) => ['id' => $r->id, 'title' => $r->title])->values(),
            ]))">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Related Module</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Module</label>
                        <select name="related_module" x-model="module" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($relatedModules as $moduleOption)
                                <option value="{{ $moduleOption->value }}">{{ $moduleOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="module !== 'none'" x-cloak>
                        <label class="block text-sm font-medium text-slate-300">Related Record</label>
                        <select name="related_id" x-model="relatedId" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            <option value="">— Select record —</option>
                            <template x-for="record in activeRecords()" :key="record.id">
                                <option :value="record.id" x-text="record.title"></option>
                            </template>
                        </select>
                        @error('related_id')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Attachments --}}
            <section
                class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5"
                x-data="announcementAttachments()"
                x-cloak
            >
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Attachments</h2>
                <p class="mt-1 text-xs text-slate-500">Upload supporting documents or images (PDF, JPG, PNG). Maximum size: 10 MB.</p>
                <p class="mt-1 text-xs text-slate-600">Optional — attach supporting files only.</p>

                @if ($isEdit && $announcement->attachments->isNotEmpty())
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Existing attachments</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($announcement->attachments as $attachment)
                                <li
                                    class="flex flex-col gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-3 sm:flex-row sm:items-center sm:justify-between"
                                    x-show="!removedExisting.includes({{ $attachment->id }})"
                                >
                                    <div class="flex min-w-0 items-start gap-3">
                                        @if ($attachment->isImage() && $attachment->publicUrl())
                                            <img src="{{ $attachment->publicUrl() }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover ring-1 ring-slate-700" />
                                        @elseif ($attachment->isPdf())
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-rose-500/15 text-xs font-bold text-rose-300 ring-1 ring-rose-500/30" aria-hidden="true">PDF</span>
                                        @else
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-xs font-bold text-slate-400 ring-1 ring-slate-700" aria-hidden="true">FILE</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-slate-200">{{ $attachment->original_name }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $attachment->typeLabel() }} · {{ $attachment->formattedSize() }}
                                                · Uploaded {{ optional($attachment->created_at)->format('M d, Y') ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3 self-end sm:self-auto">
                                        <a href="{{ route('admin.announcements.attachments.download', [$announcement, $attachment]) }}" class="text-xs font-semibold text-cyan-300 hover:text-cyan-200">Download</a>
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-rose-300 hover:text-rose-200"
                                            @click="confirmRemoveExisting({{ $attachment->id }}, @js($attachment->original_name))"
                                        >Delete</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <template x-for="id in removedExisting" :key="'rm-'+id">
                            <input type="hidden" name="remove_attachment_ids[]" :value="id" />
                        </template>
                    </div>
                @endif

                <div class="mt-4">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-violet-500/30 bg-violet-500/10 px-4 py-2.5 text-sm font-semibold text-violet-100 hover:bg-violet-500/20">
                        <span>Choose files</span>
                        <input
                            type="file"
                            name="attachments[]"
                            class="sr-only"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                            x-ref="fileInput"
                            @change="onFilesSelected($event)"
                        />
                    </label>
                    <p class="mt-2 text-xs text-slate-500">You can select multiple PDF or image files.</p>
                    <p x-show="errorMessage" x-text="errorMessage" class="mt-2 text-sm text-rose-300" x-cloak></p>
                    @error('attachments')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                    @error('attachments.*')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <ul x-show="pending.length > 0" class="mt-4 space-y-2" x-cloak>
                    <template x-for="(file, index) in pending" :key="file.key">
                        <li class="flex flex-col gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <template x-if="file.preview">
                                    <img :src="file.preview" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover ring-1 ring-slate-700" />
                                </template>
                                <template x-if="!file.preview && file.isPdf">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-rose-500/15 text-xs font-bold text-rose-300 ring-1 ring-rose-500/30" aria-hidden="true">PDF</span>
                                </template>
                                <template x-if="!file.preview && !file.isPdf">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-xs font-bold text-slate-400 ring-1 ring-slate-700" aria-hidden="true">FILE</span>
                                </template>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-200" x-text="file.name"></p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        <span x-text="file.typeLabel"></span> · <span x-text="file.sizeLabel"></span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" class="self-end text-xs font-semibold text-rose-300 hover:text-rose-200 sm:self-auto" @click="removePending(index)">Remove</button>
                        </li>
                    </template>
                </ul>
            </section>

            {{-- Section 6: Publishing --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Publishing</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Publish Date & Time</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional(optional($announcement)->published_at)->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Expiration Date & Time</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', optional(optional($announcement)->expires_at)->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                        @error('expires_at')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Manual Status</label>
                        <select name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}" @selected(old('status', optional($announcement)->status?->value ?? \App\Enums\AnnouncementStatus::Draft->value) === $statusOption->value)>{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($isEdit)
                        <div class="flex items-end">
                            <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Resolved Status</p>
                                <p class="mt-1 text-sm font-bold text-white">{{ $resolvedStatus?->label() ?? 'Draft' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <label class="mt-4 flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', optional($announcement)->is_published)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                    Published
                </label>
                <label class="mt-3 flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', optional($announcement)->is_pinned)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                    📌 Pin announcement (appears first)
                </label>
            </section>

            {{-- Section 7: Notifications --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Notification Settings</h2>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="notify_in_app" value="1" @checked(old('notify_in_app', optional($announcement)->notify_in_app ?? true)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                        Send in-app notification
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="show_on_dashboard" value="1" @checked(old('show_on_dashboard', optional($announcement)->show_on_dashboard ?? true)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                        Show on dashboard
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="pin_to_homepage" value="1" @checked(old('pin_to_homepage', optional($announcement)->pin_to_homepage)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                        Pin to homepage
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="send_email" value="1" @checked(old('send_email', optional($announcement)->send_email)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                        Send email notification
                    </label>
                </div>
                @if ($isEdit)
                    <label class="mt-4 flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="resend_notifications" value="1" class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                        Resend notifications on save
                    </label>
                @else
                    <input type="hidden" name="notify_students" value="1" />
                @endif
            </section>

            @if ($isEdit)
                <footer class="border-t border-violet-500/10 pt-3 text-xs text-slate-500">
                    <div class="flex flex-col gap-1 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-4 sm:gap-y-1">
                        <span>Created by <span class="text-slate-300">{{ $announcement->author?->name ?? '—' }}</span></span>
                        <span class="hidden text-slate-700 sm:inline">·</span>
                        <span>{{ optional($announcement->created_at)->format('M d, Y g:i A') ?? '—' }}</span>
                        <span class="hidden text-slate-700 sm:inline">·</span>
                        <span>Updated by <span class="text-slate-300">{{ $announcement->updater?->name ?? '—' }}</span></span>
                        <span class="hidden text-slate-700 sm:inline">·</span>
                        <span>{{ optional($announcement->updated_at)->format('M d, Y g:i A') ?? '—' }}</span>
                    </div>
                </footer>
            @endif

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.announcements.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</a>
                @if ($isEdit)
                    <a href="{{ route('admin.announcements.preview', $announcement) }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-cyan-500/30 px-5 py-2.5 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/10">Preview Announcement</a>
                @endif
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">{{ $isEdit ? 'Save Changes' : 'Create Announcement' }}</button>
            </div>
        </form>
    </x-admin-portal>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('announcementRelatedModule', (config) => ({
                    module: config.module ?? 'none',
                    relatedId: config.relatedId ?? '',
                    elections: config.elections ?? [],
                    talentEvents: config.talentEvents ?? [],
                    events: config.events ?? [],
                    fundraisers: config.fundraisers ?? [],
                    activeRecords() {
                        return {
                            election: this.elections,
                            talent_competition: this.talentEvents,
                            school_event: this.events,
                            fundraising: this.fundraisers,
                        }[this.module] ?? [];
                    },
                }));

                Alpine.data('announcementAttachments', () => ({
                    pending: [],
                    removedExisting: [],
                    errorMessage: '',
                    maxBytes: 10 * 1024 * 1024,
                    allowedExt: ['pdf', 'jpg', 'jpeg', 'png'],
                    allowedMimes: ['application/pdf', 'image/jpeg', 'image/png'],

                    formatSize(bytes) {
                        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                        if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
                        return bytes + ' B';
                    },

                    typeLabelFor(file) {
                        const ext = (file.name.split('.').pop() || '').toLowerCase();
                        if (ext === 'pdf' || file.type === 'application/pdf') return 'PDF';
                        if (['jpg', 'jpeg'].includes(ext)) return 'JPG';
                        if (ext === 'png') return 'PNG';
                        return 'File';
                    },

                    isAllowed(file) {
                        const ext = (file.name.split('.').pop() || '').toLowerCase();
                        const mimeOk = !file.type || this.allowedMimes.includes(file.type);
                        const extOk = this.allowedExt.includes(ext);
                        return mimeOk && extOk;
                    },

                    onFilesSelected(event) {
                        this.errorMessage = '';
                        const selected = Array.from(event.target.files || []);
                        const next = [...this.pending.map((p) => p.file)];

                        for (const file of selected) {
                            if (! this.isAllowed(file)) {
                                this.errorMessage = `"${file.name}" is not allowed. Use PDF, JPG, or PNG only.`;
                                continue;
                            }
                            if (file.size > this.maxBytes) {
                                this.errorMessage = `"${file.name}" exceeds the 10 MB limit.`;
                                continue;
                            }
                            if (next.some((f) => f.name === file.name && f.size === file.size)) {
                                continue;
                            }
                            next.push(file);
                        }

                        this.syncFiles(next);
                    },

                    removePending(index) {
                        const next = this.pending.map((p) => p.file);
                        next.splice(index, 1);
                        this.syncFiles(next);
                        this.errorMessage = '';
                    },

                    confirmRemoveExisting(id, name) {
                        if (! confirm(`Delete attachment "${name}"? This will remove it when you save.`)) {
                            return;
                        }
                        if (! this.removedExisting.includes(id)) {
                            this.removedExisting.push(id);
                        }
                    },

                    syncFiles(files) {
                        this.pending.forEach((item) => {
                            if (item.preview) URL.revokeObjectURL(item.preview);
                        });

                        this.pending = files.map((file, index) => {
                            const isImage = (file.type || '').startsWith('image/');
                            const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
                            return {
                                key: `${file.name}-${file.size}-${index}-${Date.now()}`,
                                file,
                                name: file.name,
                                sizeLabel: this.formatSize(file.size),
                                typeLabel: this.typeLabelFor(file),
                                isPdf,
                                preview: isImage ? URL.createObjectURL(file) : null,
                            };
                        });

                        const input = this.$refs.fileInput;
                        if (! input) return;

                        const transfer = new DataTransfer();
                        files.forEach((file) => transfer.items.add(file));
                        input.files = transfer.files;
                    },
                }));
            });
        </script>
    @endpush
</x-app-layout>
