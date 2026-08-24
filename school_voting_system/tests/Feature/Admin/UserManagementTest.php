<?php

namespace Tests\Feature\Admin;

use App\Enums\ElectionStatus;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AdminAssignment;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\Permission;
use App\Models\StaffRole;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_students_page_explains_roster_enrollment(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('No registered student accounts yet.')
            ->assertSee('Open Student Roster')
            ->assertSee('Manage Student Roster');
    }

    public function test_super_admin_can_filter_students_by_grade(): void
    {
        $admin = User::factory()->superAdmin()->create();
        User::factory()->create([
            'name' => 'Grade Ten Student',
            'grade_level' => '10',
            'section' => 'A',
        ]);
        User::factory()->create([
            'name' => 'Grade Eleven Student',
            'grade_level' => '11',
            'section' => 'B',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Grade 10')
            ->assertSee('Grade 11');

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['grade_level' => '10']))
            ->assertOk()
            ->assertSee('Grade Ten Student')
            ->assertDontSee('Grade Eleven Student');
    }

    public function test_regular_admin_sees_suspended_and_archived_students(): void
    {
        $admin = $this->recordsAdmin();
        $this->assignAdmin($admin, ['10'], ['A']);

        $active = User::factory()->create([
            'name' => 'Active Student',
            'grade_level' => '10',
            'section' => 'A',
            'is_active' => true,
        ]);
        $suspended = User::factory()->create([
            'name' => 'Suspended Student',
            'grade_level' => '10',
            'section' => 'A',
            'is_active' => false,
        ]);
        $archived = User::factory()->create([
            'name' => 'Archived Student',
            'grade_level' => '10',
            'section' => 'A',
            'is_active' => false,
            'archived_at' => now(),
        ]);
        $outsideAssignment = User::factory()->create([
            'name' => 'Grade Twelve Registered',
            'grade_level' => '12',
            'section' => 'C',
            'is_active' => true,
        ]);

        $ids = app(AdminScopeService::class)->manageableStudentsQuery($admin)->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertTrue($ids->contains($suspended->id));
        $this->assertTrue($ids->contains($archived->id));
        $this->assertTrue($ids->contains($outsideAssignment->id));

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee('Suspended Student')
            ->assertDontSee('Active Student')
            ->assertDontSee('Grade Twelve Registered');

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['status' => 'deactivated']))
            ->assertOk()
            ->assertSee('Archived Student')
            ->assertDontSee('Suspended Student');
    }

    public function test_regular_admin_sees_passkey_registered_students_outside_election_assignment(): void
    {
        $admin = $this->recordsAdmin();
        $this->assignAdmin($admin, ['10', '11'], ['A', 'B']);
        $superAdmin = User::factory()->superAdmin()->create();

        $student = User::factory()->create([
            'name' => 'Passkey Enrolled Senior',
            'account_id' => 'STU-1201',
            'grade_level' => '12',
            'section' => 'C',
            'student_status' => StudentStatus::Enrolled,
            'is_active' => true,
        ]);
        $this->attachPasskey($student);

        $scope = app(AdminScopeService::class);

        $this->assertTrue(
            $scope->manageableStudentsQuery($admin)->whereKey($student->id)->exists()
        );
        $this->assertFalse(
            $scope->scopedStudentsQuery($admin)->whereKey($student->id)->exists(),
            'Election turnout scope should still honor assignment grade and section.'
        );

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Passkey Enrolled Senior')
            ->assertSee('STU-1201');

        $this->actingAs($superAdmin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Passkey Enrolled Senior');

        $this->actingAs($admin)
            ->get(route('admin.students.edit', $student))
            ->assertOk()
            ->assertSee('Passkey Enrolled Senior');
    }

    public function test_operations_admin_can_list_registered_students_without_manage_students(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assignAdmin($admin, ['10', '11'], ['A', 'B']);

        User::factory()->create([
            'name' => 'Enrolled Outside Assignment',
            'account_id' => 'STU-1202',
            'grade_level' => '12',
            'section' => 'STEM',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Enrolled Outside Assignment')
            ->assertSee('STU-1202');
    }

    public function test_regular_admin_can_archive_and_restore_in_scope_student(): void
    {
        $admin = $this->recordsAdmin();
        $this->assignAdmin($admin, ['10'], ['A']);
        $student = User::factory()->create([
            'name' => 'Archivable Student',
            'account_id' => 'STU-1001',
            'grade_level' => '10',
            'section' => 'A',
            'student_status' => StudentStatus::Enrolled,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.students.index'))
            ->post(route('admin.students.archive', $student))
            ->assertRedirect(route('admin.students.index'));

        $student->refresh();
        $this->assertNotNull($student->archived_at);
        $this->assertFalse($student->is_active);
        $this->assertSame('Deactivated', $student->accountStatusLabel());

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['status' => 'deactivated']))
            ->assertOk()
            ->assertSee('Archivable Student');

        $this->actingAs($admin)
            ->from(route('admin.students.index'))
            ->post(route('admin.students.restore', $student))
            ->assertRedirect(route('admin.students.index'));

        $student->refresh();
        $this->assertNull($student->archived_at);
        $this->assertTrue($student->is_active);
        $this->assertSame('Active', $student->accountStatusLabel());
    }

    public function test_suspend_action_does_not_archive_the_student(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create([
            'name' => 'Suspend Me',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.students.index'))
            ->patch(route('admin.students.toggle-active', $student))
            ->assertRedirect(route('admin.students.index'));

        $student->refresh();
        $this->assertFalse($student->is_active);
        $this->assertNull($student->archived_at);
        $this->assertSame('Suspended', $student->accountStatusLabel());
    }

    public function test_super_admin_can_create_a_faculty_account(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('super-admin.faculty.store'), [
                'account_id' => 'FACULTY-200',
                'name' => 'Jane Faculty',
                'email' => 'jane.faculty@example.com',
                'send_enrollment_email' => false,
            ])
            ->assertRedirect(route('super-admin.faculty.index'))
            ->assertSessionHas('enrollment_url');

        $this->assertDatabaseHas('users', [
            'account_id' => 'FACULTY-200',
            'email' => 'jane.faculty@example.com',
            'role' => UserRole::Faculty->value,
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.faculty.index'))
            ->assertOk()
            ->assertSee('FACULTY-200')
            ->assertSee('Jane Faculty');
    }

    public function test_regular_admin_cannot_open_faculty_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('super-admin.faculty.index'))
            ->assertForbidden();
    }

    protected function recordsAdmin(): User
    {
        $permission = Permission::query()->create([
            'key' => 'manage_students',
            'label' => 'Manage Students',
            'category' => 'users',
        ]);
        $role = StaffRole::query()->create([
            'name' => 'Student Records Admin',
            'slug' => 'student_records_admin',
            'description' => 'Test records admin',
            'is_system' => true,
        ]);
        $role->permissions()->attach($permission->id);

        return User::factory()->admin()->create([
            'staff_role_id' => $role->id,
        ]);
    }

    protected function attachPasskey(User $student): void
    {
        $passkey = new Passkey([
            'name' => 'Enrollment Device',
            'credential_id' => 'cred-'.$student->id.'-'.uniqid(),
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
        ]);
        $passkey->user_id = $student->id;
        $passkey->save();
    }

    /**
     * @param  list<string>  $grades
     * @param  list<string>  $sections
     */
    protected function assignAdmin(User $admin, array $grades, array $sections): void
    {
        $election = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $election->id,
            'grade_levels' => $grades,
            'sections' => $sections,
            'assigned_by' => $admin->id,
        ]);
    }
}
