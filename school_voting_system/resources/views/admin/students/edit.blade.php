<x-app-layout>
    <x-admin-portal title="Edit Student Record" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Edit Student Record',
            'description' => $student->name,
            'showAction' => false,
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-white">School Record</h2>
                <p class="mt-1 text-sm text-slate-400">Update profile details and assign grade, section, and enrollment status.</p>

                <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-slate-300">Account ID</label>
                        <input type="text" value="{{ $student->account_id }}" disabled class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2.5 font-mono text-slate-400">
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $student->name) }}" required
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                        @error('name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $student->email) }}" required
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                        @error('email')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="grade_level" class="block text-sm font-medium text-slate-300">Grade</label>
                            @if (count($gradeLevels) > 0)
                                <select id="grade_level" name="grade_level" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                    <option value="">Select grade</option>
                                    @foreach ($gradeLevels as $grade)
                                        <option value="{{ $grade }}" @selected((string) old('grade_level', $student->grade_level) === (string) $grade)>
                                            Grade {{ $grade }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input id="grade_level" name="grade_level" type="text" value="{{ old('grade_level', $student->grade_level) }}" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                                    placeholder="e.g. 11">
                            @endif
                            @error('grade_level')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="section" class="block text-sm font-medium text-slate-300">Section</label>
                            @if (count($sections) > 0)
                                <select id="section" name="section" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                    <option value="">Select section</option>
                                    @foreach ($sections as $sectionOption)
                                        <option value="{{ $sectionOption }}" @selected((string) old('section', $student->section) === (string) $sectionOption)>
                                            Section {{ $sectionOption }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input id="section" name="section" type="text" value="{{ old('section', $student->section) }}" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                                    placeholder="e.g. A">
                            @endif
                            @error('section')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="student_status" class="block text-sm font-medium text-slate-300">Enrollment Status</label>
                        <select id="student_status" name="student_status" required
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected((string) old('student_status', $student->student_status?->value) === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_status')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                            Save changes
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </section>

            <aside class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Account Summary</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500">Account ID</dt>
                        <dd class="font-mono text-slate-200">{{ $student->account_id }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500">Role</dt>
                        <dd class="text-slate-200">{{ $student->roleLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500">Active</dt>
                        <dd class="{{ $student->is_active ? 'text-emerald-300' : 'text-amber-300' }}">
                            {{ $student->is_active ? 'Yes' : 'No' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Member since</dt>
                        <dd class="text-slate-200">{{ optional($student->created_at)->format('M d, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </x-admin-portal>
</x-app-layout>
