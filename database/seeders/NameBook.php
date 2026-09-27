<?php

namespace Database\Seeders;

/**
 * مصدر واحد للأسماء وأرقام الجوال والبريد في بيانات التجربة،
 * يستخدمه StudentSeeder و SupervisorSeeder حتى تبدو البيانات متنوّعة وواقعية.
 */
class NameBook
{
    /** الاسم الأول — ذكور: [عربي, لاتيني] */
    public const MALE = [
        ['أحمد', 'ahmad'], ['محمد', 'mohammed'], ['حسن', 'hasan'], ['حسين', 'hussein'],
        ['علاء', 'alaa'], ['محمود', 'mahmoud'], ['يوسف', 'yousef'], ['يونس', 'younis'],
        ['عبد الله', 'abdullah'], ['منير', 'muneer'], ['تيسير', 'tayseer'], ['حامد', 'hamed'],
        ['عمر', 'omar'], ['عامر', 'amer'], ['أشرف', 'ashraf'], ['كرم', 'karam'],
        ['خالد', 'khaled'], ['سامي', 'sami'], ['رامي', 'rami'], ['وسيم', 'waseem'],
        ['باسل', 'basel'], ['مهنّد', 'muhannad'], ['أنس', 'anas'], ['زياد', 'ziad'],
        ['طارق', 'tareq'], ['عماد', 'emad'], ['فادي', 'fadi'], ['نبيل', 'nabil'],
    ];

    /** الاسم الأول — إناث: [عربي, لاتيني] */
    public const FEMALE = [
        ['علا', 'ola'], ['عبير', 'abeer'], ['أسماء', 'asmaa'], ['سالي', 'sally'],
        ['ميساء', 'maysaa'], ['إيمان', 'eman'], ['هنادي', 'hanadi'], ['رانيا', 'rania'],
        ['دعاء', 'duaa'], ['رغد', 'raghad'], ['لينا', 'lina'], ['مريم', 'mariam'],
        ['نورا', 'nora'], ['سارة', 'sara'], ['هبة', 'heba'], ['شيماء', 'shaimaa'],
        ['آية', 'aya'], ['رهام', 'reham'], ['تالا', 'tala'], ['ملك', 'malak'],
        ['جنى', 'jana'], ['ريم', 'reem'], ['نغم', 'nagham'], ['بيان', 'bayan'],
    ];

    /** اسم العائلة: [عربي, لاتيني] */
    public const FAMILY = [
        ['النجار', 'alnajjar'], ['أبو ندى', 'abunada'], ['الشوا', 'alshawa'], ['حمودة', 'hammouda'],
        ['المصري', 'almasri'], ['الخطيب', 'alkhateeb'], ['عاشور', 'ashour'], ['صيام', 'siam'],
        ['الأغا', 'alagha'], ['شعث', 'shaath'], ['مقداد', 'miqdad'], ['أبو شرخ', 'abusharkh'],
        ['السقا', 'alsaqqa'], ['الهمص', 'alhams'], ['قديح', 'qudaih'], ['الفرا', 'alfarra'],
        ['سكيك', 'skaik'], ['زقوت', 'zaqout'], ['الطهراوي', 'altahrawi'], ['اللوح', 'alloh'],
        ['مطر', 'matar'], ['صبح', 'sobh'], ['بركة', 'baraka'], ['عودة', 'odeh'],
        ['حجازي', 'hijazi'], ['الزاملي', 'alzamli'], ['البرديني', 'albardini'], ['دحلان', 'dahlan'],
    ];

    /**
     * اسم كامل عشوائي (الأول + العائلة) مع مقابله اللاتيني للبريد.
     *
     * @return array{0:string,1:string} [الاسم بالعربية, البريد بدون اللاحقة]
     */
    public static function person(string $gender): array
    {
        [$firstAr, $firstEn] = \Arr::random($gender === 'male' ? self::MALE : self::FEMALE);
        [$familyAr, $familyEn] = \Arr::random(self::FAMILY);

        return ["{$firstAr} {$familyAr}", "{$firstEn}.{$familyEn}"];
    }

    /** رقم جوال فلسطيني بصيغة 059/056 متبوعة بسبعة أرقام. */
    public static function phone(): string
    {
        return \Arr::random(['059', '056']) . random_int(1000000, 9999999);
    }
}
