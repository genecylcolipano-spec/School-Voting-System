<?php

namespace Tests\Feature\Admin;

use App\Enums\RosterRegistrationStatus;
use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\AllowedStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RosterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_add_one_student_roster_row_without_creating_a_login(): void
    {
        Mail::fake();

        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.create'))
            ->assertOk()
            ->assertSee('Add Student Roster Record')
            ->assertSee('This does not create a login or send an enrollment email.', false);

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.store'), [
                'account_id' => '2026-90001',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'grade_level' => '10',
                'section' => 'A',
            ])
            ->assertRedirect(route('super-admin.roster.students.index'))
            ->assertSessionHas('success');

        $row = AllowedStudent::query()->where('account_id', '2026-90001')->first();

        $this->assertNotNull($row);
        $this->assertFalse($row->is_registered);
        $this->assertFalse($row->isFullyRegistered());
        $this->assertSame(RosterRegistrationStatus::NotRegistered, $row->registrationStatus());
        $this->assertNull(User::findByAccountId('2026-90001'));
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_regular_admin_cannot_open_or_add_roster_rows(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.store'), [
                'account_id' => '2026-90002',
                'first_name' => 'Blocked',
                'last_name' => 'Admin',
            ])
            ->assertForbidden();

        $this->assertNull(AllowedStudent::query()->where('account_id', '2026-90002')->first());

        $row = AllowedStudent::query()->create([
            'account_id' => '2026-90002',
            'first_name' => 'Blocked',
            'last_name' => 'Admin',
            'is_registered' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('super-admin.roster.students.destroy', $row))
            ->assertForbidden();

        $this->assertNotNull(AllowedStudent::query()->where('account_id', '2026-90002')->first());
    }

    public function test_student_roster_search_matches_grade_and_section(): void
    {
        $admin = User::factory()->superAdmin()->create();

        AllowedStudent::query()->create([
            'account_id' => 'STU-GRADE',
            'first_name' => 'Grade',
            'last_name' => 'Match',
            'grade_level' => '11',
            'section' => 'Pearl',
        ]);
        AllowedStudent::query()->create([
            'account_id' => 'STU-OTHER',
            'first_name' => 'Other',
            'last_name' => 'Student',
            'grade_level' => '10',
            'section' => 'Ruby',
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.index'))
            ->assertOk()
            ->assertSee('Search Student ID, name, grade, section, or school year', false)
            ->assertDontSee('>Clear</a>', false);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.index', ['q' => 'Pearl']))
            ->assertOk()
            ->assertSee('Grade Match')
            ->assertDontSee('Other Student')
            ->assertSee('>Clear</a>', false);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.index', ['q' => '11']))
            ->assertOk()
            ->assertSee('Grade Match')
            ->assertDontSee('Other Student');
    }

    public function test_faculty_roster_search_matches_department_and_position(): void
    {
        $admin = User::factory()->superAdmin()->create();

        AllowedFaculty::query()->create([
            'account_id' => 'FAC-SCI',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'department' => 'Science',
            'position' => 'Adviser',
        ]);
        AllowedFaculty::query()->create([
            'account_id' => 'FAC-MATH',
            'first_name' => 'Ben',
            'last_name' => 'Cruz',
            'department' => 'Mathematics',
            'position' => 'Coordinator',
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.faculty.index', ['q' => 'Science']))
            ->assertOk()
            ->assertSee('Ana Reyes', false)
            ->assertDontSee('Ben Cruz', false)
            ->assertSee('Search Faculty ID, name, department, or position', false)
            ->assertSee('>Clear</a>', false);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.faculty.index', ['q' => 'Coordinator']))
            ->assertOk()
            ->assertSee('Ben Cruz', false)
            ->assertDontSee('Ana Reyes', false);
    }

    public function test_registered_account_id_cannot_be_changed(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-LOCKED',
            'first_name' => 'Locked',
            'last_name' => 'Id',
            'grade_level' => '10',
            'section' => 'A',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
        ]);
        $user = User::factory()->create([
            'account_id' => 'STU-LOCKED',
            'name' => 'Locked Id',
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.edit', $row))
            ->assertOk()
            ->assertSee('Account ID cannot be changed after this person has registered.', false)
            ->assertDontSee('name="account_id"', false);

        $this->actingAs($admin)
            ->put(route('super-admin.roster.students.update', $row), [
                'account_id' => 'STU-TAMPERED',
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'grade_level' => '11',
                'section' => 'B',
            ])
            ->assertRedirect(route('super-admin.roster.students.index'));

        $row->refresh();

        $this->assertSame('STU-LOCKED', $row->account_id);
        $this->assertSame('Updated', $row->first_name);
        $this->assertSame('Name', $row->last_name);
        $this->assertSame('11', $row->grade_level);
        $this->assertSame('B', $row->section);
        $this->assertSame('11', User::findByAccountId('STU-LOCKED')?->grade_level);
        $this->assertSame('B', User::findByAccountId('STU-LOCKED')?->section);
        $this->assertNotNull(User::findByAccountId('STU-LOCKED'));
        $this->assertNull(User::findByAccountId('STU-TAMPERED'));
        $this->assertSame($user->id, User::findByAccountId('STU-LOCKED')?->id);
    }

    public function test_unregistered_account_id_can_still_be_changed(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-OPEN',
            'first_name' => 'Open',
            'last_name' => 'Row',
            'grade_level' => '9',
            'section' => 'C',
            'is_registered' => false,
        ]);

        $this->actingAs($admin)
            ->put(route('super-admin.roster.students.update', $row), [
                'account_id' => 'STU-RENAMED',
                'first_name' => 'Open',
                'last_name' => 'Row',
                'grade_level' => '9',
                'section' => 'C',
            ])
            ->assertRedirect(route('super-admin.roster.students.index'));

        $this->assertNull(AllowedStudent::query()->where('account_id', 'STU-OPEN')->first());
        $this->assertNotNull(AllowedStudent::query()->where('account_id', 'STU-RENAMED')->first());
    }

    public function test_registered_row_links_to_the_portal_account(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-PORTAL',
            'first_name' => 'Portal',
            'last_name' => 'Student',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
        ]);
        $user = User::factory()->create([
            'account_id' => 'STU-PORTAL',
            'name' => 'Portal Student',
        ]);

        $accountUrl = route('admin.students.show', $user);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.index'))
            ->assertOk()
            ->assertSee('Portal account')
            ->assertSee($accountUrl, false);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.show', $row))
            ->assertOk()
            ->assertSee('View portal account')
            ->assertSee($accountUrl, false);
    }

    public function test_unregistered_row_does_not_link_to_a_portal_account(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-PENDING',
            'first_name' => 'Pending',
            'last_name' => 'Row',
            'is_registered' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.show', $row))
            ->assertOk()
            ->assertDontSee('View portal account');
    }

    public function test_super_admin_can_add_faculty_and_administrator_roster_rows(): void
    {
        Mail::fake();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('super-admin.roster.faculty.store'), [
                'account_id' => 'FAC-90001',
                'first_name' => 'Liza',
                'last_name' => 'Tan',
                'department' => 'English',
                'position' => 'Teacher',
            ])
            ->assertRedirect(route('super-admin.roster.faculty.index'));

        $this->actingAs($admin)
            ->post(route('super-admin.roster.administrators.store'), [
                'account_id' => 'EMP-90001',
                'first_name' => 'Carlo',
                'last_name' => 'Diaz',
                'department' => 'Registrar',
                'position' => 'Officer',
            ])
            ->assertRedirect(route('super-admin.roster.administrators.index'));

        $this->assertNotNull(AllowedFaculty::query()->where('account_id', 'FAC-90001')->first());
        $this->assertNotNull(AllowedAdministrator::query()->where('account_id', 'EMP-90001')->first());
        $this->assertNull(User::findByAccountId('FAC-90001'));
        $this->assertNull(User::findByAccountId('EMP-90001'));
        Mail::assertNothingSent();
    }

    public function test_student_roster_pages_show_remove(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-REMOVE-UI',
            'first_name' => 'Remove',
            'last_name' => 'Me',
            'is_registered' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.index'))
            ->assertOk()
            ->assertSee('Remove')
            ->assertSee(route('super-admin.roster.students.destroy', $row), false);

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.show', $row))
            ->assertOk()
            ->assertSee('Remove')
            ->assertSee(route('super-admin.roster.students.destroy', $row), false);
    }

    public function test_super_admin_can_remove_an_unregistered_roster_row(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-REMOVE',
            'first_name' => 'Gone',
            'last_name' => 'Row',
            'is_registered' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('super-admin.roster.students.destroy', $row))
            ->assertRedirect(route('super-admin.roster.students.index'))
            ->assertSessionHas('success', 'Roster record removed.');

        $this->assertNull(AllowedStudent::query()->where('account_id', 'STU-REMOVE')->first());
    }

    public function test_super_admin_can_remove_a_registered_row_after_the_user_deleted_their_account(): void
    {
        Mail::fake();
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-ORPHAN',
            'first_name' => 'Orphan',
            'last_name' => 'Row',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
        ]);

        $this->assertNull(User::findByAccountId('STU-ORPHAN'));

        $this->actingAs($admin)
            ->from(route('super-admin.roster.students.index'))
            ->delete(route('super-admin.roster.students.destroy', $row))
            ->assertRedirect(route('super-admin.roster.students.index'))
            ->assertSessionHasErrors('record');

        $this->assertNotNull(AllowedStudent::query()->where('account_id', 'STU-ORPHAN')->first());

        $this->actingAs($admin)
            ->delete(route('super-admin.roster.students.destroy', $row), [
                'confirm_linked' => '1',
            ])
            ->assertRedirect(route('super-admin.roster.students.index'))
            ->assertSessionHas('success', 'Roster record removed.');

        $this->assertNull(AllowedStudent::query()->where('account_id', 'STU-ORPHAN')->first());

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.store'), [
                'account_id' => 'STU-ORPHAN',
                'first_name' => 'Orphan',
                'last_name' => 'Row',
                'grade_level' => '10',
                'section' => 'A',
            ])
            ->assertRedirect(route('super-admin.roster.students.index'));

        $restored = AllowedStudent::query()->where('account_id', 'STU-ORPHAN')->first();

        $this->assertNotNull($restored);
        $this->assertFalse($restored->is_registered);
        $this->assertFalse($restored->isFullyRegistered());
        $this->assertSame(RosterRegistrationStatus::NotRegistered, $restored->registrationStatus());
    }

    public function test_removing_a_roster_row_does_not_delete_the_portal_account(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $row = AllowedStudent::query()->create([
            'account_id' => 'STU-KEEP-LOGIN',
            'first_name' => 'Keep',
            'last_name' => 'Login',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
        ]);
        $user = User::factory()->create([
            'account_id' => 'STU-KEEP-LOGIN',
            'name' => 'Keep Login',
        ]);

        $this->actingAs($admin)
            ->delete(route('super-admin.roster.students.destroy', $row), [
                'confirm_linked' => '1',
            ])
            ->assertRedirect(route('super-admin.roster.students.index'))
            ->assertSessionHas('success', 'Roster record removed.');

        $this->assertNull(AllowedStudent::query()->where('account_id', 'STU-KEEP-LOGIN')->first());
        $this->assertNotNull(User::findByAccountId('STU-KEEP-LOGIN'));
        $this->assertSame($user->id, User::findByAccountId('STU-KEEP-LOGIN')?->id);
    }
}
