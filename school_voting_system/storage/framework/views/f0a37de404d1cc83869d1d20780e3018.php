<?php
    $isEdit = $talentEvent !== null;
    $acceptedFormats = old('accepted_video_formats', $isEdit ? $talentEvent->acceptedVideoFormatsArray() : ['mp4', 'mov', 'webm']);
    $maxVideoMinutes = old('max_video_duration_minutes', $isEdit ? max(1, intdiv($talentEvent->maxVideoDurationSeconds(), 60)) : 5);
    $maxUploadSize = old('max_upload_size_mb', $isEdit ? $talentEvent->maxUploadSizeMb() : 100);
    $performanceDuration = old('performance_duration', $talentEvent?->performanceDurationPreset() ?? '5');
    $performanceDurationCustom = old('performance_duration_custom', $talentEvent && $performanceDuration === 'custom' ? $talentEvent->max_performance_duration_minutes : '');
    $winnersCount = old('winners_count', $talentEvent?->winnersCountPreset() ?? '3');
    $winnersCountCustom = old('winners_count_custom', $talentEvent && $winnersCount === 'custom' ? $talentEvent->number_of_winners : '');
    $votingMethod = old('voting_method', $talentEvent?->voting_method?->value ?? 'student_only');
    $judgePercentage = old('judge_percentage', $talentEvent?->judge_percentage ?? 70);
    $studentVotePercentage = old('student_vote_percentage', $talentEvent?->student_vote_percentage ?? 30);
    $registrationMethods = $registrationMethods ?? \App\Enums\TalentRegistrationMethod::cases();
    $submissionMethods = $submissionMethods ?? \App\Enums\TalentSubmissionMethod::cases();
    $rankingMethods = $rankingMethods ?? \App\Enums\TalentRankingMethod::cases();
?>

<form
    method="POST"
    action="<?php echo e($isEdit ? route('admin.talent-competition.update', $talentEvent) : route('admin.talent-competition.store')); ?>"
    enctype="multipart/form-data"
    data-confirm-sensitive
    class="space-y-6"
>
    <?php echo csrf_field(); ?>
    <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <section
        class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6"
        x-data="{
            performanceDuration: <?php echo \Illuminate\Support\Js::from($performanceDuration)->toHtml() ?>,
            winnersCount: <?php echo \Illuminate\Support\Js::from($winnersCount)->toHtml() ?>,
            votingMethod: <?php echo \Illuminate\Support\Js::from($votingMethod)->toHtml() ?>,
            judgePct: <?php echo \Illuminate\Support\Js::from((int) $judgePercentage)->toHtml() ?>,
            studentPct: <?php echo \Illuminate\Support\Js::from((int) $studentVotePercentage)->toHtml() ?>,
            syncStudentPct() {
                if (this.votingMethod === 'judges_and_students') {
                    this.studentPct = Math.max(0, 100 - (parseInt(this.judgePct, 10) || 0));
                }
            },
            syncJudgePct() {
                if (this.votingMethod === 'judges_and_students') {
                    this.judgePct = Math.max(0, 100 - (parseInt(this.studentPct, 10) || 0));
                }
            },
        }"
    >
        <h2 class="text-lg font-semibold text-white">Basic Information</h2>
        <p class="mt-1 text-sm text-slate-400">Assigned election: <?php echo e($election->title); ?></p>

        <?php if($isEdit): ?>
            <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</span>
                <span class="rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-200">
                    <?php echo e($talentEvent->displayStatusLabel()); ?>

                </span>
            </div>
        <?php endif; ?>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Competition Title</label>
                <input type="text" name="title" value="<?php echo e(old('title', $talentEvent?->title)); ?>" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Competition Code <span class="text-slate-500">(optional)</span></label>
                <input type="text" name="competition_code" value="<?php echo e(old('competition_code', $talentEvent?->competition_code)); ?>" placeholder="e.g. TC-2026-01"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['competition_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Event Type</label>
                <select name="type" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>" <?php if(old('type', $talentEvent?->type?->value) === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Talent Category</label>
                <select name="talent_category" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->value); ?>" <?php if(old('talent_category', $talentEvent?->talent_category?->value ?? \App\Enums\TalentCategory::OpenTalent->value) === $category->value): echo 'selected'; endif; ?>><?php echo e($category->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['talent_category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Organizer <span class="text-slate-500">(optional)</span></label>
                <input type="text" name="organizer" value="<?php echo e(old('organizer', $talentEvent?->organizer)); ?>" placeholder="Student Affairs Office"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['organizer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Date & Time</label>
                <input type="datetime-local" name="event_date" value="<?php echo e(old('event_date', optional($talentEvent?->event_date)->format('Y-m-d\TH:i'))); ?>" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['event_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Venue</label>
                <input type="text" name="venue" value="<?php echo e(old('venue', $talentEvent?->venue)); ?>" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['venue'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Description <span class="text-slate-500">(optional)</span></label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e(old('description', $talentEvent?->description)); ?></textarea>
            </div>

            <div class="sm:col-span-2">
                <?php
                    $bannerPreviewSrc = \App\Support\EventImageUrl::placeholder();
                    $bannerHasUploaded = false;
                    $bannerContain = false;
                    $bannerOrientation = null;
                    $bannerWarnPortrait = false;

                    if ($isEdit && $talentEvent->has_uploaded_image && ! $talentEvent->isLegacyPortraitAsBanner()) {
                        $bannerPreviewSrc = $talentEvent->image_url;
                        $bannerHasUploaded = true;
                        $bannerContain = $talentEvent->bannerNeedsContainLayout();
                        $bannerOrientation = $talentEvent->imageOrientation();
                        $bannerWarnPortrait = $talentEvent->shouldWarnNonLandscapeBanner();
                    }
                ?>
                <?php if (isset($component)) { $__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.event-image-field','data' => ['src' => $bannerPreviewSrc,'hasUploaded' => $bannerHasUploaded,'contain' => $bannerContain,'orientation' => $bannerOrientation,'warnPortrait' => $bannerWarnPortrait,'label' => 'Competition Banner *']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('event-image-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bannerPreviewSrc),'has-uploaded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bannerHasUploaded),'contain' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bannerContain),'orientation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bannerOrientation),'warn-portrait' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bannerWarnPortrait),'label' => 'Competition Banner *']); ?>
                    <input id="event-image-input" type="file" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500/20 file:px-3 file:py-1.5 file:text-sm file:text-cyan-300" <?php if(! $isEdit): echo 'required'; endif; ?>>
                    <div class="mt-2 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2 text-xs text-slate-400">
                        <p class="font-semibold text-slate-300">Upload guidelines</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5">
                            <li>Recommended size: <span class="text-slate-200">1600 × 900 px</span></li>
                            <li>Aspect ratio: <span class="text-slate-200">16:9 landscape</span></li>
                            <li>Formats: JPG / PNG · Maximum 2 MB</li>
                            <li>Used on dashboard cards, headers, live monitoring, and results</li>
                        </ul>
                    </div>
                    <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d)): ?>
<?php $attributes = $__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d; ?>
<?php unset($__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d)): ?>
<?php $component = $__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d; ?>
<?php unset($__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d); ?>
<?php endif; ?>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Competition Poster <span class="text-slate-500">(optional)</span></label>
                <p class="mt-1 text-xs text-slate-500">Official promotional poster. Does not replace the competition banner.</p>
                <div class="mt-2 flex flex-wrap items-start gap-4">
                    <div class="aspect-[9/16] w-40 overflow-hidden rounded-xl border border-slate-700 bg-slate-950 shadow-lg shadow-black/30">
                        <?php if($isEdit && $talentEvent->hasCompetitionPoster()): ?>
                            <img id="competition-poster-preview" src="<?php echo e($talentEvent->competitionPosterUrl()); ?>" alt="Poster preview" class="h-full w-full object-contain object-center">
                        <?php else: ?>
                            <img id="competition-poster-preview" src="" alt="" class="hidden h-full w-full object-contain object-center">
                            <div id="competition-poster-placeholder" class="flex h-full w-full items-center justify-center px-2 text-center text-[10px] text-slate-600">9:16 preview</div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <input id="competition-poster-input" type="file" name="poster" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500/20 file:px-3 file:py-1.5 file:text-sm file:text-cyan-300">
                        <div class="mt-2 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2 text-xs text-slate-400">
                            <p class="font-semibold text-slate-300">Upload guidelines</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5">
                                <li>Recommended size: <span class="text-slate-200">1080 × 1920 px</span></li>
                                <li>Aspect ratio: <span class="text-slate-200">9:16 portrait</span></li>
                                <li>Formats: JPG / PNG · Maximum 2 MB</li>
                                <li>Shown on competition details as the Official Competition Poster</li>
                            </ul>
                        </div>
                        <?php $__errorArgs = ['poster'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Competition Thumbnail <span class="text-slate-500">(optional)</span></label>
                <?php if($isEdit && $talentEvent->thumbnail_path): ?>
                    <img src="<?php echo e($talentEvent->thumbnailUrl()); ?>" alt="" class="mt-2 h-20 w-20 rounded-xl object-cover object-center">
                <?php endif; ?>
                <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500/20 file:px-3 file:py-1.5 file:text-sm file:text-cyan-300">
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">600 × 600 px</span> · Square (1:1) · JPG or PNG · Max 2MB
                </p>
                <?php $__errorArgs = ['thumbnail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <h2 class="mt-8 text-lg font-semibold text-white">Schedule</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-300">Registration Opens</label>
                <input type="datetime-local" name="registration_starts_at" value="<?php echo e(old('registration_starts_at', optional($talentEvent?->registration_starts_at)->format('Y-m-d\TH:i'))); ?>"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['registration_starts_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Registration Closes</label>
                <input type="datetime-local" name="registration_ends_at" value="<?php echo e(old('registration_ends_at', optional($talentEvent?->registration_ends_at)->format('Y-m-d\TH:i'))); ?>"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['registration_ends_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Submission Deadline</label>
                <input type="datetime-local" name="submission_deadline" value="<?php echo e(old('submission_deadline', optional($talentEvent?->submission_deadline)->format('Y-m-d\TH:i'))); ?>"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['submission_deadline'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Voting Opens</label>
                <input type="datetime-local" name="voting_starts_at" value="<?php echo e(old('voting_starts_at', optional($talentEvent?->voting_starts_at)->format('Y-m-d\TH:i'))); ?>" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['voting_starts_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Voting Closes</label>
                <input type="datetime-local" name="voting_ends_at" value="<?php echo e(old('voting_ends_at', optional($talentEvent?->voting_ends_at)->format('Y-m-d\TH:i'))); ?>" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <?php $__errorArgs = ['voting_ends_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Results Publish Date <span class="text-slate-500">(optional)</span></label>
                <input type="datetime-local" name="results_publish_at" value="<?php echo e(old('results_publish_at', optional($talentEvent?->results_publish_at)->format('Y-m-d\TH:i'))); ?>"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                <p class="mt-1 text-xs text-slate-500">Scheduled target date. Official publish still requires the Publish Results action.</p>
                <?php $__errorArgs = ['results_publish_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <h2 class="mt-8 text-lg font-semibold text-white">Rules & Limits</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-300">Maximum Performance Duration</label>
                <select name="performance_duration" x-model="performanceDuration" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <option value="3">3 Minutes</option>
                    <option value="5">5 Minutes</option>
                    <option value="10">10 Minutes</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div x-show="performanceDuration === 'custom'" x-cloak>
                <label class="block text-sm font-medium text-slate-300">Custom Duration (minutes)</label>
                <input type="number" name="performance_duration_custom" min="1" max="180" value="<?php echo e($performanceDurationCustom); ?>"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Maximum Participants</label>
                <input type="number" name="max_contestants" min="1" max="500" value="<?php echo e(old('max_contestants', $talentEvent?->max_contestants)); ?>"
                    placeholder="Leave empty for unlimited"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Maximum Video Duration (minutes)</label>
                <input type="number" name="max_video_duration_minutes" min="1" max="60" value="<?php echo e($maxVideoMinutes); ?>" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Maximum Upload Size (MB)</label>
                <input type="number" name="max_upload_size_mb" min="1" max="1024" value="<?php echo e($maxUploadSize); ?>" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Accepted Video Formats</label>
                <div class="mt-2 flex flex-wrap gap-3">
                    <?php $__currentLoopData = ['mp4', 'mov', 'webm', 'mkv', 'avi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-200">
                            <input type="checkbox" name="accepted_video_formats[]" value="<?php echo e($format); ?>" <?php if(in_array($format, (array) $acceptedFormats, true)): echo 'checked'; endif; ?>
                                class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                            .<?php echo e($format); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <h2 class="mt-8 text-lg font-semibold text-white">Registration & Submission</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-300">Registration Method</label>
                <select name="registration_method" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $registrationMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($method->value); ?>" <?php if(old('registration_method', $talentEvent?->registration_method?->value ?? 'both') === $method->value): echo 'selected'; endif; ?>><?php echo e($method->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Submission Method</label>
                <select name="submission_method" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $submissionMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($method->value); ?>" <?php if(old('submission_method', $talentEvent?->submission_method?->value ?? 'both') === $method->value): echo 'selected'; endif; ?>><?php echo e($method->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <h2 class="mt-8 text-lg font-semibold text-white">Voting Settings</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-300">Voting Method</label>
                <select name="voting_method" x-model="votingMethod" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $votingMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($method->value); ?>"><?php echo e($method->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Ranking Method</label>
                <select name="ranking_method" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $rankingMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($method->value); ?>" <?php if(old('ranking_method', $talentEvent?->ranking_method?->value ?? 'votes') === $method->value): echo 'selected'; endif; ?>><?php echo e($method->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div x-show="votingMethod === 'judges_and_students'" x-cloak class="sm:col-span-2 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-300">Judge Percentage</label>
                    <input type="number" name="judge_percentage" min="0" max="100" x-model.number="judgePct" @input="syncStudentPct()"
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Student Vote Percentage</label>
                    <input type="number" name="student_vote_percentage" min="0" max="100" x-model.number="studentPct" @input="syncJudgePct()"
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Number of Winners</label>
                <select name="winners_count" x-model="winnersCount" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="5">5</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div x-show="winnersCount === 'custom'" x-cloak>
                <label class="block text-sm font-medium text-slate-300">Custom Number of Winners</label>
                <input type="number" name="winners_count_custom" min="1" max="50" value="<?php echo e($winnersCountCustom); ?>"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>
            <div class="sm:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="auto_status_updates" value="1" <?php if(old('auto_status_updates', $talentEvent?->auto_status_updates ?? true)): echo 'checked'; endif; ?>
                        class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                    Automatic status updates based on schedule
                </label>
            </div>
        </div>
    </section>

    <?php if($isEdit): ?>
        <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/5 px-5 py-4 text-sm text-slate-300">
            Participants are managed separately.
            <a href="<?php echo e(route('admin.talent-participants.index', ['event' => $talentEvent->id])); ?>" class="ml-1 font-semibold text-cyan-300 hover:text-cyan-200">Open Participants →</a>
        </div>
    <?php else: ?>
        <div class="rounded-2xl border border-slate-700 bg-slate-900/50 px-5 py-4 text-sm text-slate-400">
            After creating this competition, add or review participants from the <strong class="text-slate-200">Participants</strong> module.
        </div>
    <?php endif; ?>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
            <?php echo e($isEdit ? 'Save Changes' : 'Create Competition'); ?>

        </button>
        <a href="<?php echo e($isEdit ? route('admin.talent-competition.show', $talentEvent) : route('admin.talent-competition.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Cancel</a>
    </div>
</form>

<style>[x-cloak]{display:none !important;}</style>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/talent-competition/_form.blade.php ENDPATH**/ ?>