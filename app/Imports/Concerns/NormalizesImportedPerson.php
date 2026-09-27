<?php

namespace App\Imports\Concerns;

use Illuminate\Support\Str;

/**
 * تنقية الخلايا القادمة من Excel.
 *
 * كشفا الطلاب والمشرفين يأتيان بنفس الأعمدة ونفس المزالق، وكان
 * المنطق مكرَّراً في المستورِدَين حرفياً — وبنفس العطلين.
 */
trait NormalizesImportedPerson
{
    /**
     * كان \u200E"0" . trim($row['phone'])\u200E يلصق صفراً دائماً.
     *
     * السبب الأصلي وجيه: Excel يقرأ \u200E0592050879\u200E رقماً فيسقط الصفر
     * البادئ ويصير \u200E592050879\u200E. لكن حين يكون العمود نصّاً في الملف
     * يبقى الصفر، فينتج \u200E00592050879\u200E — رقم لا يُطابق أي تحقّق ولا
     * يعمل في أي اتصال، ويُحفظ بلا شكوى لأن العمود \u200Evarchar NULL\u200E.
     * هنا نُجرّد كل ما ليس رقماً ثم نضع صفراً واحداً.
     */
    protected function normalizePhone($value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return $digits === '' ? null : '0' . ltrim($digits, '0');
    }

    /**
     * خليّة كلمة مرور فارغة كانت تصل \u200Enull\u200E إلى \u200Etrim()\u200E — تحذير إهمال
     * في PHP 8.1+، ثم \u200Ebcrypt('')\u200E أي حساب بكلمة مرور فارغة صالحة
     * يدخل به أيّ أحد.
     */
    protected function passwordFor($value): string
    {
        $password = trim((string) $value);

        return $password !== '' ? $password : Str::random(16);
    }

    protected function text($value): string
    {
        return trim((string) $value);
    }
}
