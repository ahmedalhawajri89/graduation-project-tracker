<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الصورة الشخصية.
 *
 * الغرض ليس التزيين: القوائم طويلة (٥٠٠ طالب و٣٠٠ مشرف)، وفي البيانات
 * عائلات متكرّرة وأسماء أولى متكرّرة — فيتحقّق الأدمن من الرقم الجامعي
 * ليتأكّد أنه على الصفّ الصحيح. والمشرف يلتقي في المناقشة أفراد فرق لا
 * يعرف وجوههم.
 *
 * العمود يحمل اسم الملف فقط لا مساره: القرص يُعرَّف في
 * \u200Econfig/filesystems.php\u200E، فنقله لا يتطلّب تعديل ٨٠٠ صفّ.
 */
return new class extends Migration
{
    private array $tables = ['students', 'supervisors', 'admins'];

    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('avatar', 120)->nullable()->after('email');
            });
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('avatar');
            });
        }
    }
};
