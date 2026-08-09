<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasskeyBootstrapController;
use App\Http\Controllers\Auth\PasskeyDeviceController;
use App\Http\Controllers\Auth\PasskeyRecoveryController;
use App\Http\Controllers\Auth\PortalRegistrationController;
use App\Http\Controllers\Admin\AdminActionController;
use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\AdminCampaignController;
use App\Http\Controllers\Admin\AdminCandidateController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminElectionController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminEventsTalentController;
use App\Http\Controllers\Admin\AdminFundraiserController;
use App\Http\Controllers\Admin\System\SystemAuditLogController;
use App\Http\Controllers\Admin\System\SystemBackupController;
use App\Http\Controllers\Admin\System\SystemMaintenanceController;
use App\Http\Controllers\Admin\System\SystemSettingsController;
use App\Http\Controllers\Admin\SuperAdminActionController;
use App\Http\Controllers\Admin\SuperAdminDashboardController;
use App\Http\Controllers\Admin\SuperAdminStaffUserController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminResultsController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AllowedAdministratorController;
use App\Http\Controllers\Admin\AllowedFacultyController;
use App\Http\Controllers\Admin\AllowedStudentController;
use App\Http\Controllers\Admin\AdminLiveMonitoringController;
use App\Http\Controllers\Admin\AdminTalentCompetitionController;
use App\Http\Controllers\Admin\AdminTalentJudgingController;
use App\Http\Controllers\Admin\AdminTalentParticipantController;
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\Faculty\FacultyJudgingController;
use App\Http\Controllers\Faculty\FacultyPortalController;
use App\Http\Controllers\Student\StudentPortalController;
use App\Http\Controllers\Student\StudentTalentRegistrationController;
use App\Http\Controllers\TalentVideoStreamController;
use App\Http\Controllers\ProfileController;
use App\Services\Auth\RoleRedirectService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest portal — passkey login & account registration
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'guest', 'guest.portal', 'passkey.secure'])->group(function () {
    Route::get('/', [LoginController::class, 'showLogin'])->name('login');

    Route::get('/login/options', [LoginController::class, 'loginOptions'])
        ->middleware('throttle:10,1')
        ->name('login.options');

    Route::post('/login/verify', [LoginController::class, 'loginVerify'])
        ->middleware('throttle:10,1')
        ->name('login.verify');

    Route::get('/register', [PortalRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [PortalRegistrationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');

    Route::get('/register/passkey-setup', [PortalRegistrationController::class, 'passkeySetup'])
        ->name('register.passkey.setup');

    Route::get('/login/recovery', [PasskeyRecoveryController::class, 'show'])->name('login.recovery');
    Route::post('/login/recovery', [PasskeyRecoveryController::class, 'requestReset'])
        ->middleware('throttle:5,1')
        ->name('login.recovery.request');
});

/*
|--------------------------------------------------------------------------
| Signed passkey enrollment — must NOT use guest middleware
| (admins may open the link while still logged in.)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'passkey.secure'])->group(function () {
    Route::get('/enroll/passkey/{user}', [PasskeyBootstrapController::class, 'show'])
        ->middleware('signed')
        ->name('register.passkey.bootstrap');

    Route::get('/enroll/passkey-options', [LoginController::class, 'registerOptions'])
        ->middleware(['passkey.bootstrap', 'throttle:10,1'])
        ->name('register.passkey.bootstrap.options');

    Route::post('/enroll/passkey-verify', [LoginController::class, 'registerVerify'])
        ->middleware(['passkey.bootstrap', 'throttle:10,1'])
        ->name('register.passkey.bootstrap.verify');
});

/*
|--------------------------------------------------------------------------
| Authenticated area — dashboards, devices, passkey registration
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'passkey.secure', 'session.inactivity', 'admin.ip', 'app.maintenance'])->group(function () {
    Route::get('/dashboard', function (RoleRedirectService $redirects) {
        return redirect($redirects->dashboardPathFor(auth()->user()));
    })->name('dashboard');

    Route::get('/media/talent-video/{entry}', TalentVideoStreamController::class)
        ->middleware('throttle:120,1')
        ->name('talent.video.stream');

    Route::get('/student/dashboard', [DashboardController::class, 'student'])
        ->middleware('role:student')
        ->name('student.dashboard');

    Route::get('/faculty/dashboard', FacultyDashboardController::class)
        ->middleware('role:faculty')
        ->name('faculty.dashboard');

    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('/elections', [FacultyPortalController::class, 'elections'])->name('elections.index');
        Route::get('/elections/{election:slug}', [FacultyPortalController::class, 'electionShow'])->name('elections.show');

        Route::get('/events', [FacultyPortalController::class, 'events'])->name('events.index');
        Route::get('/events/{event:slug}', [FacultyPortalController::class, 'eventShow'])->name('events.show');

        Route::get('/announcements', [FacultyPortalController::class, 'announcements'])->name('announcements.index');
        Route::get('/announcements/{announcement:slug}', [FacultyPortalController::class, 'announcementShow'])->name('announcements.show');
        Route::get('/announcements/{announcement:slug}/attachments/{attachment}', [FacultyPortalController::class, 'downloadAnnouncementAttachment'])
            ->name('announcements.attachments.download');

        Route::get('/judging', [FacultyJudgingController::class, 'index'])->name('judging.index');
        Route::get('/judging/performances', [FacultyJudgingController::class, 'performances'])->name('judging.performances');
        Route::get('/judging/submitted', [FacultyJudgingController::class, 'submitted'])->name('judging.submitted');
        Route::get('/judging/{talentEvent:slug}', [FacultyJudgingController::class, 'show'])->name('judging.show');
        Route::get('/judging/{talentEvent:slug}/entries/{entry}', [FacultyJudgingController::class, 'scoreForm'])->name('judging.score');
        Route::post('/judging/{talentEvent:slug}/entries/{entry}/draft', [FacultyJudgingController::class, 'saveDraft'])
            ->middleware('throttle:30,1')
            ->name('judging.draft');
        Route::post('/judging/{talentEvent:slug}/entries/{entry}/submit', [FacultyJudgingController::class, 'submit'])
            ->middleware('throttle:20,1')
            ->name('judging.submit');

        Route::get('/notifications', [FacultyPortalController::class, 'notifications'])->name('notifications.index');
        Route::get('/notifications/feed', [FacultyPortalController::class, 'notificationsFeed'])->name('notifications.feed');
        Route::post('/notifications/read', [FacultyPortalController::class, 'markAllNotificationsRead'])->name('notifications.read');
        Route::post('/notifications/{notification}/read', [FacultyPortalController::class, 'markNotificationRead'])->name('notifications.read-one');
        Route::delete('/notifications/{notification}', [FacultyPortalController::class, 'destroyNotification'])->name('notifications.destroy');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/events', [StudentPortalController::class, 'events'])->name('events.index');
        Route::get('/events/{event:slug}', [StudentPortalController::class, 'eventShow'])->name('events.show');

        Route::get('/statistics', [StudentPortalController::class, 'statistics'])->name('statistics');

        Route::get('/campaigns', [StudentPortalController::class, 'campaigns'])->name('campaigns.index');
        Route::get('/campaigns/{partylist}', [StudentPortalController::class, 'campaignShow'])->name('campaigns.show');

        Route::get('/voting', [StudentPortalController::class, 'voting'])->name('voting.index');
        Route::get('/voting/{election:slug}', [StudentPortalController::class, 'votingShow'])->name('voting.show');
        Route::post('/voting/candidates/{candidate}/vote', [StudentPortalController::class, 'castVote'])
            ->middleware('throttle:20,1')
            ->name('voting.cast');
        Route::post('/voting/{election:slug}/submit', [StudentPortalController::class, 'submitBallot'])
            ->middleware('throttle:20,1')
            ->name('voting.submit');

        Route::get('/results', [StudentPortalController::class, 'resultsIndex'])->name('results.index');
        Route::get('/results/election/{election:slug}', [StudentPortalController::class, 'resultsShowElection'])->name('results.election.show');
        Route::get('/results/talent/{talentEvent:slug}', [StudentPortalController::class, 'resultsShowTalent'])->name('results.talent.show');

        Route::get('/talent-voting', [StudentPortalController::class, 'talentVoting'])->name('talent-voting.index');
        Route::get('/talent-voting/{talentEvent:slug}', [StudentPortalController::class, 'talentVotingShow'])->name('talent-voting.show');
        Route::get('/talent-voting/{talentEvent:slug}/standings', [StudentPortalController::class, 'talentStandings'])
            ->middleware('throttle:60,1')
            ->name('talent-voting.standings');
        Route::post('/talent-voting/entries/{entry}/vote', [StudentPortalController::class, 'castTalentVote'])
            ->middleware('throttle:20,1')
            ->name('talent-voting.vote');
        Route::post('/talent-voting/entries/{entry}/view', [StudentPortalController::class, 'recordTalentView'])
            ->middleware('throttle:120,1')
            ->name('talent-voting.view');

        Route::get('/talent-registration', [StudentTalentRegistrationController::class, 'index'])->name('talent-registration.index');
        Route::get('/talent-registration/my-entries', [StudentTalentRegistrationController::class, 'myEntries'])->name('talent-registration.my-entries');
        Route::get('/talent-registration/my-entries/{entry}', [StudentTalentRegistrationController::class, 'showEntry'])->name('talent-registration.entry.show');
        Route::get('/talent-registration/my-entries/{entry}/confirmation', [StudentTalentRegistrationController::class, 'downloadConfirmation'])
            ->name('talent-registration.entry.confirmation');

        Route::get('/talent-registration/{talentEvent:slug}', [StudentTalentRegistrationController::class, 'show'])->name('talent-registration.show');
        Route::get('/talent-registration/{talentEvent:slug}/register', [StudentTalentRegistrationController::class, 'register'])->name('talent-registration.register');
        Route::post('/talent-registration/{talentEvent:slug}/review', [StudentTalentRegistrationController::class, 'prepareReview'])
            ->middleware('throttle:10,1')
            ->name('talent-registration.review.store');
        Route::get('/talent-registration/{talentEvent:slug}/review', [StudentTalentRegistrationController::class, 'review'])->name('talent-registration.review');
        Route::post('/talent-registration/{talentEvent:slug}', [StudentTalentRegistrationController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('talent-registration.store');
        Route::get('/talent-registration/{talentEvent:slug}/success', [StudentTalentRegistrationController::class, 'success'])->name('talent-registration.success');

        // Legacy form URL → competition details (or entry if already submitted).
        Route::get('/talent-registration/{talentEvent:slug}/form', [StudentTalentRegistrationController::class, 'create'])->name('talent-registration.create');

        Route::get('/fundraising', [StudentPortalController::class, 'fundraising'])->name('fundraising.index');
        Route::get('/fundraising/{fundraiser:slug}', [StudentPortalController::class, 'fundraiserShow'])->name('fundraising.show');
        Route::post('/fundraising/{fundraiser:slug}/donate', [StudentPortalController::class, 'donate'])
            ->middleware('throttle:20,1')
            ->name('fundraising.donate');

        Route::get('/announcements', [StudentPortalController::class, 'announcements'])->name('announcements.index');
        Route::get('/announcements/{announcement:slug}', [StudentPortalController::class, 'announcementShow'])->name('announcements.show');
        Route::get('/announcements/{announcement:slug}/attachments/{attachment}', [StudentPortalController::class, 'downloadAnnouncementAttachment'])->name('announcements.attachments.download');
        Route::get('/notifications', [StudentPortalController::class, 'notifications'])->name('notifications.index');
        Route::get('/notifications/feed', [StudentPortalController::class, 'notificationsFeed'])->name('notifications.feed');
        Route::post('/notifications/read', [StudentPortalController::class, 'markAllNotificationsRead'])->name('notifications.read');
        Route::post('/notifications/{notification}/read', [StudentPortalController::class, 'markNotificationRead'])->name('notifications.read-one');
        Route::delete('/notifications/{notification}', [StudentPortalController::class, 'destroyNotification'])->name('notifications.destroy');
        Route::get('/candidates/{candidate}', [StudentPortalController::class, 'candidateShow'])->name('candidates.show');
    });

    Route::get('/admin/dashboard', AdminDashboardController::class)
        ->middleware('role:admin,super_admin')
        ->name('admin.dashboard');

    Route::get('/admin/dashboard/live-voting', [AdminDashboardController::class, 'liveVoting'])
        ->middleware(['role:admin,super_admin', 'throttle:120,1'])
        ->name('admin.dashboard.live-voting');

    Route::get('/admin/dashboard/live', [AdminDashboardController::class, 'live'])
        ->middleware(['role:admin,super_admin', 'throttle:120,1'])
        ->name('admin.dashboard.live');

    Route::middleware(['role:admin,super_admin', 'throttle:60,1'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/talent-competition', [AdminTalentCompetitionController::class, 'index'])->name('talent-competition.index');
        Route::get('/talent-competition/create', [AdminTalentCompetitionController::class, 'create'])->name('talent-competition.create');
        Route::post('/talent-competition', [AdminTalentCompetitionController::class, 'store'])->name('talent-competition.store');
        Route::get('/talent-competition/{talentEvent}', [AdminTalentCompetitionController::class, 'show'])->name('talent-competition.show');
        Route::get('/talent-competition/{talentEvent}/edit', [AdminTalentCompetitionController::class, 'edit'])->name('talent-competition.edit');
        Route::get('/talent-competition/{talentEvent}/settings', [AdminTalentCompetitionController::class, 'settings'])->name('talent-competition.settings');
        Route::put('/talent-competition/{talentEvent}/settings', [AdminTalentCompetitionController::class, 'updateSettings'])->name('talent-competition.settings.update');
        Route::get('/talent-competition/{talentEvent}/judges', [AdminTalentJudgingController::class, 'edit'])->name('talent-competition.judges');
        Route::post('/talent-competition/{talentEvent}/judges', [AdminTalentJudgingController::class, 'assign'])->name('talent-competition.judges.assign');
        Route::delete('/talent-competition/{talentEvent}/judges/{faculty}', [AdminTalentJudgingController::class, 'remove'])->name('talent-competition.judges.remove');
        Route::put('/talent-competition/{talentEvent}/criteria', [AdminTalentJudgingController::class, 'updateCriteria'])->name('talent-competition.criteria.update');
        Route::put('/talent-competition/{talentEvent}', [AdminTalentCompetitionController::class, 'update'])->name('talent-competition.update');
        Route::post('/talent-competition/{talentEvent}/duplicate', [AdminTalentCompetitionController::class, 'duplicate'])->name('talent-competition.duplicate');
        Route::post('/talent-competition/{talentEvent}/publish', [AdminTalentCompetitionController::class, 'publish'])->name('talent-competition.publish');
        Route::post('/talent-competition/{talentEvent}/archive', [AdminTalentCompetitionController::class, 'archive'])->name('talent-competition.archive');
        Route::post('/talent-competition/{talentEvent}/open-registration', [AdminTalentCompetitionController::class, 'openRegistration'])->name('talent-competition.open-registration');
        Route::post('/talent-competition/{talentEvent}/close-registration', [AdminTalentCompetitionController::class, 'closeRegistration'])->name('talent-competition.close-registration');
        Route::post('/talent-competition/{talentEvent}/close-voting', [AdminTalentCompetitionController::class, 'closeVoting'])->name('talent-competition.close-voting');
        Route::delete('/talent-competition/{talentEvent}', [AdminTalentCompetitionController::class, 'destroy'])->name('talent-competition.destroy');

        Route::post('/elections/{election}/pause', [AdminActionController::class, 'pauseElection'])->name('election.pause');
        Route::post('/elections/{election}/resume', [AdminActionController::class, 'resumeElection'])->name('election.resume');
        Route::post('/elections/{election}/publish-results', [AdminActionController::class, 'publishElectionResults'])->name('election.publish-results');
        Route::post('/elections/{election}/unpublish-results', [AdminActionController::class, 'unpublishElectionResults'])->name('election.unpublish-results');
        Route::get('/export/preliminary', [AdminActionController::class, 'exportPreliminary'])->name('export.preliminary');
        Route::post('/voters/remind', [AdminActionController::class, 'sendReminders'])->name('voters.remind');
        Route::post('/candidates/{candidate}/verify', [AdminActionController::class, 'verifyCandidate'])->name('candidates.verify');
        Route::post('/posters/{poster}/approve', [AdminActionController::class, 'approvePoster'])->name('posters.approve');
        Route::post('/posters/{poster}/reject', [AdminActionController::class, 'rejectPoster'])->name('posters.reject');
        Route::post('/talent-events/{talentEvent}/open-voting', [AdminActionController::class, 'openTalentVoting'])->name('talent.open-voting');
        Route::post('/talent-events/{talentEvent}/publish-results', [AdminActionController::class, 'publishTalentResults'])->name('talent.publish-results');
        Route::post('/talent-entries/{entry}/approve', [AdminActionController::class, 'approveTalentEntry'])->name('talent.entries.approve');
        Route::post('/talent-entries/{entry}/reject', [AdminActionController::class, 'rejectTalentEntry'])->name('talent.entries.reject');
        Route::post('/talent-entries/{entry}/status', [AdminActionController::class, 'updateTalentEntryStatus'])->name('talent.entries.status');
        Route::post('/complaints/{complaint}/resolve', [AdminActionController::class, 'resolveComplaint'])->name('complaints.resolve');
    });

    Route::middleware(['role:admin,super_admin', 'throttle:60,1'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/recovery', [AdminDashboardController::class, 'recovery'])
            ->middleware('role:super_admin')
            ->name('recovery.index');

        Route::get('/students', [AdminStudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [AdminStudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [AdminStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [AdminStudentController::class, 'update'])->name('students.update');
        Route::patch('/students/{student}/toggle-active', [AdminStudentController::class, 'toggleActive'])->name('students.toggle-active');
        Route::post('/students/{student}/archive', [AdminStudentController::class, 'archive'])->name('students.archive');
        Route::post('/students/{student}/restore', [AdminStudentController::class, 'restore'])->name('students.restore');

        // Redirect old temporary admin roster URLs to Roster Management.
        Route::redirect('/allowed-students/import', '/super-admin/roster/students/import');
        Route::redirect('/allowed-students/import/template', '/super-admin/roster/students/import/template');

        Route::get('/campaigns', [AdminCampaignController::class, 'index'])->name('campaigns.index');
        Route::get('/campaigns/create', [AdminCampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campaigns', [AdminCampaignController::class, 'store'])->name('campaigns.store');
        Route::get('/campaigns/{partylist}/edit', [AdminCampaignController::class, 'edit'])->name('campaigns.edit');
        Route::put('/campaigns/{partylist}', [AdminCampaignController::class, 'update'])->name('campaigns.update');
        Route::post('/campaigns/{partylist}/poster', [AdminCampaignController::class, 'storePoster'])->name('campaigns.poster.store');
        Route::delete('/campaigns/{partylist}', [AdminCampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::get('/events-talent', [AdminEventsTalentController::class, 'index'])->name('events-talent.index');

        Route::get('/talent-participants', [AdminTalentParticipantController::class, 'index'])->name('talent-participants.index');
        Route::get('/talent-participants/create', [AdminTalentParticipantController::class, 'create'])->name('talent-participants.create');
        Route::post('/talent-participants', [AdminTalentParticipantController::class, 'store'])->name('talent-participants.store');
        Route::get('/talent-participants/{entry}', [AdminTalentParticipantController::class, 'show'])->name('talent-participants.show');
        Route::get('/talent-participants/{entry}/edit', [AdminTalentParticipantController::class, 'edit'])->name('talent-participants.edit');
        Route::put('/talent-participants/{entry}', [AdminTalentParticipantController::class, 'update'])->name('talent-participants.update');
        Route::delete('/talent-participants/{entry}', [AdminTalentParticipantController::class, 'destroy'])->name('talent-participants.destroy');

        Route::resource('candidates', AdminCandidateController::class)->except(['index', 'create']);
        Route::resource('elections', AdminElectionController::class)->except(['show']);
        Route::resource('events', AdminEventController::class)->except(['show']);
        Route::get('/announcements/{announcement}/preview', [AdminAnnouncementController::class, 'preview'])->name('announcements.preview');
        Route::get('/announcements/{announcement}/attachments/{attachment}', [AdminAnnouncementController::class, 'downloadAttachment'])->name('announcements.attachments.download');
        Route::resource('announcements', AdminAnnouncementController::class)->except(['show']);

        Route::get('/fundraisers/donations', [AdminFundraiserController::class, 'donations'])->name('fundraisers.donations');
        Route::get('/fundraisers/transactions', [AdminFundraiserController::class, 'transactions'])->name('fundraisers.transactions');
        Route::get('/fundraisers/{fundraiser}/preview', [AdminFundraiserController::class, 'preview'])->name('fundraisers.preview');
        Route::resource('fundraisers', AdminFundraiserController::class)->except(['show']);

        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/live', [AdminAnalyticsController::class, 'live'])
            ->middleware('throttle:120,1')
            ->name('analytics.live');

        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/feed', [AdminNotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('/notifications/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markOne'])->name('notifications.read-one');
        Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/talent', [AdminReportController::class, 'talent'])->name('reports.talent');
        Route::get('/reports/fundraising', [AdminReportController::class, 'fundraising'])->name('reports.fundraising');

        Route::get('/live-monitoring/elections', [AdminLiveMonitoringController::class, 'election'])->name('live.election');
        Route::get('/live-monitoring/elections/poll', [AdminLiveMonitoringController::class, 'electionPoll'])
            ->middleware('throttle:120,1')
            ->name('live.election.poll');
        Route::get('/live-monitoring/talent', [AdminLiveMonitoringController::class, 'talent'])->name('live.talent');
        Route::get('/live-monitoring/talent/poll', [AdminLiveMonitoringController::class, 'talentPoll'])
            ->middleware('throttle:120,1')
            ->name('live.talent.poll');
        Route::post('/live-monitoring/talent/{talentEvent}/pause', [AdminLiveMonitoringController::class, 'pause'])->name('live.talent.pause');
        Route::post('/live-monitoring/talent/{talentEvent}/resume', [AdminLiveMonitoringController::class, 'resume'])->name('live.talent.resume');
        Route::post('/live-monitoring/talent/{talentEvent}/close', [AdminLiveMonitoringController::class, 'close'])->name('live.talent.close');
        Route::get('/live-monitoring/talent/{talentEvent}/export', [AdminLiveMonitoringController::class, 'export'])->name('live.talent.export');

        Route::get('/results', [AdminResultsController::class, 'index'])->name('results.index');
        Route::get('/results/elections', [AdminResultsController::class, 'electionsIndex'])->name('results.elections');
        Route::get('/results/competitions', [AdminResultsController::class, 'talentIndex'])->name('results.competitions');
        Route::get('/results/election/{election:slug}', [AdminResultsController::class, 'showElection'])->name('results.election.show');
        Route::get('/results/election/{election:slug}/live', [AdminResultsController::class, 'liveElection'])
            ->middleware('throttle:120,1')
            ->name('results.election.live');
        Route::get('/results/election/{election:slug}/export/{format}', [AdminResultsController::class, 'exportElection'])
            ->whereIn('format', ['pdf', 'excel', 'csv', 'print'])
            ->name('results.election.export');
        Route::get('/results/election/{election:slug}/turnout.csv', [AdminResultsController::class, 'exportElectionTurnout'])
            ->name('results.election.turnout');
        Route::post('/results/election/{election:slug}/verify-integrity', [AdminResultsController::class, 'verifyElectionIntegrity'])
            ->name('results.election.verify-integrity');
        Route::get('/results/talent/{talentEvent:slug}', [AdminResultsController::class, 'showTalent'])->name('results.talent.show');
        Route::get('/results/talent/{talentEvent:slug}/live', [AdminResultsController::class, 'liveTalent'])
            ->middleware('throttle:120,1')
            ->name('results.talent.live');
        Route::get('/results/talent/{talentEvent:slug}/export/{format}', [AdminResultsController::class, 'exportTalent'])
            ->whereIn('format', ['pdf', 'excel', 'csv', 'print'])
            ->name('results.talent.export');
    });

    Route::get('/super-admin/dashboard', SuperAdminDashboardController::class)
        ->middleware('role:super_admin')
        ->name('super-admin.dashboard');

    Route::middleware(['role:super_admin', 'throttle:60,1'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/administrators', [SuperAdminStaffUserController::class, 'administratorsIndex'])->name('administrators.index');
        Route::get('/administrators/create', [SuperAdminStaffUserController::class, 'administratorsCreate'])->name('administrators.create');
        Route::post('/administrators', [SuperAdminStaffUserController::class, 'administratorsStore'])->name('administrators.store');
        Route::get('/administrators/{user}', [SuperAdminStaffUserController::class, 'show'])->name('administrators.show');
        Route::get('/administrators/{user}/edit', [SuperAdminStaffUserController::class, 'edit'])->name('administrators.edit');
        Route::put('/administrators/{user}', [SuperAdminStaffUserController::class, 'update'])->name('administrators.update');

        Route::get('/faculty', [SuperAdminStaffUserController::class, 'facultyIndex'])->name('faculty.index');
        Route::get('/faculty/create', [SuperAdminStaffUserController::class, 'facultyCreate'])->name('faculty.create');
        Route::post('/faculty', [SuperAdminStaffUserController::class, 'facultyStore'])->name('faculty.store');
        Route::get('/faculty/{user}', [SuperAdminStaffUserController::class, 'show'])->name('faculty.show');
        Route::get('/faculty/{user}/edit', [SuperAdminStaffUserController::class, 'edit'])->name('faculty.edit');
        Route::put('/faculty/{user}', [SuperAdminStaffUserController::class, 'update'])->name('faculty.update');
        Route::post('/faculty/{user}/competitions', [SuperAdminStaffUserController::class, 'assignCompetition'])->name('faculty.competitions.assign');
        Route::put('/faculty/{user}/competitions/{talentEvent}/role', [SuperAdminStaffUserController::class, 'updateCompetitionRole'])->name('faculty.competitions.role');
        Route::delete('/faculty/{user}/competitions/{talentEvent}', [SuperAdminStaffUserController::class, 'removeCompetition'])->name('faculty.competitions.remove');

        Route::post('/staff-users/{user}/enrollment', [SuperAdminStaffUserController::class, 'sendEnrollment'])->name('staff.enrollment');
        Route::patch('/staff-users/{user}/toggle-active', [SuperAdminStaffUserController::class, 'toggleActive'])->name('staff.toggle-active');
        Route::post('/staff-users/{user}/archive', [SuperAdminStaffUserController::class, 'archive'])->name('staff.archive');
        Route::post('/staff-users/{user}/restore', [SuperAdminStaffUserController::class, 'restore'])->name('staff.restore');
        Route::delete('/staff-users/{user}', [SuperAdminStaffUserController::class, 'destroy'])->name('staff.destroy');

        Route::prefix('roster')->name('roster.')->group(function () {
            Route::prefix('students')->name('students.')->group(function () {
                Route::get('/', [AllowedStudentController::class, 'index'])->name('index');
                Route::get('/export', [AllowedStudentController::class, 'export'])->name('export');
                Route::get('/import', [AllowedStudentController::class, 'importForm'])->name('import');
                Route::post('/import', [AllowedStudentController::class, 'importStore'])->name('import.store');
                Route::get('/import/template', [AllowedStudentController::class, 'importTemplate'])->name('import.template');
                Route::get('/{allowedStudent}', [AllowedStudentController::class, 'show'])->name('show');
                Route::get('/{allowedStudent}/edit', [AllowedStudentController::class, 'edit'])->name('edit');
                Route::put('/{allowedStudent}', [AllowedStudentController::class, 'update'])->name('update');
                Route::post('/{allowedStudent}/archive', [AllowedStudentController::class, 'archive'])->name('archive');
                Route::post('/{allowedStudent}/restore', [AllowedStudentController::class, 'restore'])->name('restore');
                Route::delete('/{allowedStudent}', [AllowedStudentController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('faculty')->name('faculty.')->group(function () {
                Route::get('/', [AllowedFacultyController::class, 'index'])->name('index');
                Route::get('/export', [AllowedFacultyController::class, 'export'])->name('export');
                Route::get('/import', [AllowedFacultyController::class, 'importForm'])->name('import');
                Route::post('/import', [AllowedFacultyController::class, 'importStore'])->name('import.store');
                Route::get('/import/template', [AllowedFacultyController::class, 'importTemplate'])->name('import.template');
                Route::get('/{allowedFaculty}', [AllowedFacultyController::class, 'show'])->name('show');
                Route::get('/{allowedFaculty}/edit', [AllowedFacultyController::class, 'edit'])->name('edit');
                Route::put('/{allowedFaculty}', [AllowedFacultyController::class, 'update'])->name('update');
                Route::post('/{allowedFaculty}/archive', [AllowedFacultyController::class, 'archive'])->name('archive');
                Route::post('/{allowedFaculty}/restore', [AllowedFacultyController::class, 'restore'])->name('restore');
                Route::delete('/{allowedFaculty}', [AllowedFacultyController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('administrators')->name('administrators.')->group(function () {
                Route::get('/', [AllowedAdministratorController::class, 'index'])->name('index');
                Route::get('/export', [AllowedAdministratorController::class, 'export'])->name('export');
                Route::get('/import', [AllowedAdministratorController::class, 'importForm'])->name('import');
                Route::post('/import', [AllowedAdministratorController::class, 'importStore'])->name('import.store');
                Route::get('/import/template', [AllowedAdministratorController::class, 'importTemplate'])->name('import.template');
                Route::get('/{allowedAdministrator}', [AllowedAdministratorController::class, 'show'])->name('show');
                Route::get('/{allowedAdministrator}/edit', [AllowedAdministratorController::class, 'edit'])->name('edit');
                Route::put('/{allowedAdministrator}', [AllowedAdministratorController::class, 'update'])->name('update');
                Route::post('/{allowedAdministrator}/archive', [AllowedAdministratorController::class, 'archive'])->name('archive');
                Route::post('/{allowedAdministrator}/restore', [AllowedAdministratorController::class, 'restore'])->name('restore');
                Route::delete('/{allowedAdministrator}', [AllowedAdministratorController::class, 'destroy'])->name('destroy');
            });
        });

        // Backward-compatible redirects from the old Official Roster URLs.
        Route::redirect('/allowed-students', '/super-admin/roster/students');
        Route::redirect('/allowed-students/import', '/super-admin/roster/students/import');
        Route::redirect('/allowed-students/import/template', '/super-admin/roster/students/import/template');
        Route::get('/allowed-students/export', fn () => redirect()->route('super-admin.roster.students.export'));
        Route::get('/allowed-students/{allowedStudent}/edit', function (\App\Models\AllowedStudent $allowedStudent) {
            return redirect()->route('super-admin.roster.students.edit', $allowedStudent);
        });

        Route::get('/search', [SuperAdminActionController::class, 'search'])->name('search');
        Route::post('/users/bulk', [SuperAdminActionController::class, 'bulkUsers'])->name('users.bulk');
        Route::post('/elections/{election}/action', [SuperAdminActionController::class, 'electionAction'])->name('elections.action');
        Route::post('/passkeys/{passkey}/action', [SuperAdminActionController::class, 'passkeyAction'])->name('passkeys.action');
        Route::post('/settings', [SuperAdminActionController::class, 'updateSettings'])->name('settings.update');
        Route::post('/backups', [SuperAdminActionController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/{backup}/download', [SuperAdminActionController::class, 'downloadBackup'])->name('backups.download');
        Route::get('/audit-logs/export', [SuperAdminActionController::class, 'exportAuditLogs'])->name('audit.export');
        Route::get('/reports/generate', [SuperAdminActionController::class, 'generateReport'])->name('reports.generate');

        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/settings', [SystemSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SystemSettingsController::class, 'update'])->name('settings.update');

            Route::get('/maintenance', [SystemMaintenanceController::class, 'edit'])->name('maintenance.edit');
            Route::post('/maintenance/enable', [SystemMaintenanceController::class, 'enable'])->name('maintenance.enable');
            Route::put('/maintenance', [SystemMaintenanceController::class, 'update'])->name('maintenance.update');
            Route::post('/maintenance/disable', [SystemMaintenanceController::class, 'disable'])->name('maintenance.disable');

            Route::get('/backups', [SystemBackupController::class, 'index'])->name('backups.index');
            Route::post('/backups', [SystemBackupController::class, 'store'])->name('backups.store');
            Route::get('/backups/{backup}', [SystemBackupController::class, 'show'])->name('backups.show');
            Route::get('/backups/{backup}/download', [SystemBackupController::class, 'download'])->name('backups.download');
            Route::delete('/backups/{backup}', [SystemBackupController::class, 'destroy'])->name('backups.destroy');

            Route::get('/audit-logs', [SystemAuditLogController::class, 'index'])->name('audit.index');
        });
    });

    Route::get('/register/passkey/options', [LoginController::class, 'registerOptions'])
        ->middleware('throttle:10,1')
        ->name('register.passkey.options');

    Route::post('/register/passkey/verify', [LoginController::class, 'registerVerify'])
        ->middleware('throttle:10,1')
        ->name('register.passkey.verify');

    Route::get('/user/passkeys', [PasskeyDeviceController::class, 'index'])->name('passkeys.index');
    Route::patch('/user/passkeys/{passkey}', [PasskeyDeviceController::class, 'update'])->name('passkeys.update');
    Route::delete('/user/passkeys/{passkey}', [PasskeyDeviceController::class, 'destroy'])->name('passkeys.destroy');

    Route::post('/admin/users/{user}/passkey-reset', [PasskeyRecoveryController::class, 'issueEnrollmentLink'])
        ->middleware('role:admin,super_admin')
        ->name('admin.passkey.reset');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logout-other-sessions', [ProfileController::class, 'logoutOtherSessions'])->name('profile.logout-other-sessions');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

