<?php

namespace Tests\Unit;

use App\Models\Semester;
use PHPUnit\Framework\TestCase;

/** اسم الفصل يُفصل إلى فصل وسنة للشريط الجانبي */
class SemesterPartsTest extends TestCase
{
    private function parts(string $name): array
    {
        return (new Semester(['name' => $name]))->parts();
    }

    public function test_it_splits_the_term_and_the_year(): void
    {
        $this->assertSame(['term' => 'الفصل الأول', 'year' => '2022–23'], $this->parts('الفصل الدراسي الأول 2022\2023'));
        $this->assertSame(['term' => 'الفصل الصيفي', 'year' => '2024–25'], $this->parts('الفصل الصيفي 2024/2025'));
    }

    /** اسم بلا سنة يبقى كما كتبه الأدمن */
    public function test_a_name_without_a_year_is_kept_whole(): void
    {
        $this->assertSame(['term' => 'فصل تجريبي', 'year' => null], $this->parts('فصل تجريبي'));
    }
}
