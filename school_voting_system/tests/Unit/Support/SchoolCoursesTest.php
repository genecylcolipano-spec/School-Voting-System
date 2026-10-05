<?php

namespace Tests\Unit\Support;

use App\Support\SchoolCourses;
use Tests\TestCase;

class SchoolCoursesTest extends TestCase
{
    public function test_it_normalizes_known_course_aliases(): void
    {
        $this->assertSame('BSIT', SchoolCourses::normalize('bsit'));
        $this->assertSame('BSIT', SchoolCourses::normalize('BS IT'));
        $this->assertSame('BSCRIM', SchoolCourses::normalize('CRIM'));
        $this->assertSame('BSCRIM', SchoolCourses::normalize('bs-crim'));
        $this->assertSame('BEED', SchoolCourses::normalize('BEED'));
        $this->assertSame('BSOA', SchoolCourses::normalize('B.S.O.A.'));
    }

    public function test_unknown_or_empty_courses_are_null(): void
    {
        $this->assertNull(SchoolCourses::normalize(null));
        $this->assertNull(SchoolCourses::normalize(''));
        $this->assertNull(SchoolCourses::normalize('STEM'));
    }
}
