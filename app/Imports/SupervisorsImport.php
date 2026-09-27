<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesImportedPerson;
use App\Models\Specialize;
use App\Models\Supervisor;
use App\Imports\Concerns\ValidatesImportedRows;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * متزامن لا في الطابور: كشف القسم بالمئات، والأدمن يحتاج جواباً فورياً
 * بما أُضيف وما تُخطّي — كان يرى «بدأت عملية الرفع» ثم صمتاً.
 */
class SupervisorsImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnFailure, SkipsOnError, SkipsEmptyRows
{
    use NormalizesImportedPerson;
    use ValidatesImportedRows;

    private $specializes;
    private $admin_id;

    public function __construct($admin_id)
    {
        $this->admin_id = $admin_id;
        // النشطة فقط: اسم تخصص موقوف يُعامَل كاسم مجهول، فلا يدخل
        // الاستيراد أحداً إلى تخصص أُوقف التسجيل عليه
        $this->specializes = Specialize::active()->select('id', 'name')->get();
    }

    public function model(array $row)
    {
        $this->countAdded();
        $specialize = $this->specializes->firstWhere('name', $this->text($row['specialization'] ?? null));

        return new Supervisor([
            'name' => $this->text($row['name'] ?? null),
            'university_id' => $this->text($row['university_id'] ?? null),
            'email' => $this->text($row['email'] ?? null),
            'phone' => $this->normalizePhone($row['phone'] ?? null),
            'gender' => strtolower(trim((string) ($row['gender'] ?? ''))) ?: 'male',
            // كان تخصصٌ مجهول يُسند \u200E''\u200E إلى مفتاح \u200Ebigint unsigned\u200E:
            // صفرٌ يشير إلى لا شيء في وضع MySQL المتساهل، وانفجارٌ في
            // الوضع الصارم. \u200Enull\u200E يقول «غير محدَّد» وهو ما نعنيه.
            'specialize_id' => $specialize?->id,
            'password' => bcrypt($this->passwordFor($row['password'] ?? null)),
            'admin_id' => $this->admin_id,
            // حدّ المجموعات لم يكن يُستورَد إطلاقاً، فكل مشرف يأتي من
            // ملف يحمل قيمة العمود الافتراضية مهما كتب الملف
            'max_group' => $this->groupLimit($row['max_group'] ?? null),
        ]);
    }

    private function groupLimit($value): int
    {
        $limit = (int) preg_replace('/\D/', '', (string) $value);

        return $limit > 0 ? $limit : 1;
    }

    /** نفس قواعد نموذج الإضافة — وتفرّد داخل الملف نفسه أيضاً */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:70',
            'university_id' => ['required', 'digits:9', Rule::unique('supervisors', 'university_id'), $this->notRepeatedInFile('university_id')],
            'email' => ['required', 'email', 'max:100', Rule::unique('supervisors', 'email'), $this->notRepeatedInFile('email')],
            // Excel يُسقط الصفر البادئ فيصل الجوال ٩ أرقام — normalizePhone يعيده
            'phone' => 'nullable|digits_between:9,10',
            'gender' => 'in:male,female',
            // فارغة تصير كلمة عشوائية (passwordFor) — وإن كُتبت فبالحدود نفسها
            'password' => 'nullable|min:8|max:60',
            // التخصصات النشطة وحدها — كان الاسم المجهول يُحفظ تخصصاً فارغاً
            'specialization' => ['required', Rule::in($this->specializes->pluck('name')->all())],
            'max_group' => 'nullable|integer|min:1',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
