<x-app-layout>
    <x-admin-portal
        title="Competition Settings"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        @include('admin.partials.page-header', [
            'title' => 'Competition Settings',
            'description' => $talentEvent->title,
            'showAction' => false,
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.talent-competition.settings.update', $talentEvent) }}" class="max-w-2xl space-y-6"
              x-data="{ votingMethod: @js(old('voting_method', $talentEvent->voting_method?->value ?? 'student_only')) }">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Visibility</h2>
                <label class="mt-4 flex items-center gap-3 text-sm text-slate-300">
                    <input type="checkbox" name="published_to_students" value="1" @checked(old('published_to_students', $talentEvent->published_to_students))
                        class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                    Visible to students (competition listing / registration)
                </label>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Registration & Submission</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Registration Method</label>
                        <select name="registration_method" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($registrationMethods as $method)
                                <option value="{{ $method->value }}" @selected(old('registration_method', $talentEvent->registration_method?->value) === $method->value)>{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Submission Method</label>
                        <select name="submission_method" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($submissionMethods as $method)
                                <option value="{{ $method->value }}" @selected(old('submission_method', $talentEvent->submission_method?->value) === $method->value)>{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Voting & Ranking</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Voting Method</label>
                        <select name="voting_method" x-model="votingMethod" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($votingMethods as $method)
                                <option value="{{ $method->value }}">{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Ranking Method</label>
                        <select name="ranking_method" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($rankingMethods as $method)
                                <option value="{{ $method->value }}" @selected(old('ranking_method', $talentEvent->ranking_method?->value) === $method->value)>{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="votingMethod === 'judges_and_students'" x-cloak>
                        <label class="block text-sm font-medium text-slate-300">Judge %</label>
                        <input type="number" name="judge_percentage" min="0" max="100" value="{{ old('judge_percentage', $talentEvent->judge_percentage ?? 70) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    </div>
                    <div x-show="votingMethod === 'judges_and_students'" x-cloak>
                        <label class="block text-sm font-medium text-slate-300">Student %</label>
                        <input type="number" name="student_vote_percentage" min="0" max="100" value="{{ old('student_vote_percentage', $talentEvent->student_vote_percentage ?? 30) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Automation & Notifications</h2>
                <label class="mt-4 flex items-center gap-3 text-sm text-slate-300">
                    <input type="checkbox" name="auto_status_updates" value="1" @checked(old('auto_status_updates', $talentEvent->auto_status_updates ?? true))
                        class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                    Automatic status updates based on schedule
                </label>
                <p class="mt-3 text-xs text-slate-500">
                    System notifications for submissions, approvals, voting open, and results published continue to use the existing Communication module.
                    Result visibility for students is controlled via the Results module (Publish Results).
                </p>
            </section>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Save Settings</button>
                <a href="{{ route('admin.talent-competition.show', $talentEvent) }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Back to Details</a>
            </div>
        </form>

        <style>[x-cloak]{display:none !important;}</style>
    </x-admin-portal>
</x-app-layout>
