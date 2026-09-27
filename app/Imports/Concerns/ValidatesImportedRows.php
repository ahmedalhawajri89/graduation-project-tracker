<?php

namespace App\Imports\Concerns;

use Maatwebsite\Excel\Validators\Failure;

/**
 * تحقّق صفّاً صفّاً وتقرير بما تُخطّي.
 *
 * كان الاستيراد بلا تحقّق وفي الطابور: صفّ فارغ ثانٍ يخالف تفرّد الرقم
 * الجامعي فيُرجع الدفعة كلّها، ورقم لا يبدأ بـ 130/230 يُحفظ فلا يدخل صاحبه
 * فريقاً أبداً، والأدمن يرى «بدأت عملية الرفع بنجاح» ثم صمت. الآن: الصفوف
 * السليمة تُضاف، والمعطوبة تُتخطّى ويُقال لماذا.
 *
 * يستعمله صنف ينفّذ ‎WithValidation‎ و‎SkipsOnFailure‎ و‎SkipsOnError‎.
 */
trait ValidatesImportedRows
{
    private int $added = 0;

    /** @var array<int, string> رقم الصف => السبب */
    private array $skipped = [];

    /** أخطاء حفظ غير متوقّعة — بلا رقم صفّ يُعرف */
    private int $errored = 0;

    /** أرقام وبريدات رآها هذا الملف — التفرّد في القاعدة لا يرى تكرار الملف نفسه */
    private array $seen = ['university_id' => [], 'email' => []];

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->skipped[$failure->row()] ??= $failure->errors()[0] ?? 'بيانات غير صالحة';
        }
    }

    public function onError(\Throwable $e)
    {
        // خطأ قاعدة غير متوقّع في صفّ — يُسجَّل ولا يوقف الملف
        \Illuminate\Support\Facades\Log::warning('تعذّر استيراد صفّ', ['exception' => $e]);
        $this->errored++;
    }

    /** خلايا Excel الرقمية تصل أعداداً: الرقم الجامعي والجوال نصوص، والجنس بصيغة واحدة */
    public function prepareForValidation($data, $index)
    {
        foreach (['university_id', 'phone', 'email', 'name', 'specialization'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $data[$key] = trim((string) $data[$key]);
            }
        }

        $data['gender'] = strtolower(trim((string) ($data['gender'] ?? ''))) ?: 'male';

        return $data;
    }

    /** قاعدة: لم يتكرّر في هذا الملف */
    protected function notRepeatedInFile(string $key): \Closure
    {
        return function ($attribute, $value, $fail) use ($key) {
            $value = mb_strtolower((string) $value);

            if (in_array($value, $this->seen[$key], true)) {
                $fail('مكرّر في الملف نفسه');

                return;
            }

            $this->seen[$key][] = $value;
        };
    }

    public function customValidationAttributes()
    {
        return [
            'name' => 'الاسم',
            'university_id' => 'الرقم الجامعي',
            'email' => 'البريد',
            'phone' => 'الجوال',
            'gender' => 'الجنس',
            'specialization' => 'التخصص',
            'max_group' => 'حدّ المجموعات',
        ];
    }

    protected function countAdded(): void
    {
        $this->added++;
    }

    /** @return array{added: int, skipped: array<int, string>, errored: int} */
    public function report(): array
    {
        ksort($this->skipped);

        // model() يُعدّ قبل الحفظ، فما فشل حفظه يُطرح
        return ['added' => max(0, $this->added - $this->errored), 'skipped' => $this->skipped, 'errored' => $this->errored];
    }
}
