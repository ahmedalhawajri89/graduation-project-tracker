<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إيقاف التخصص بدل حذفه.
 *
 * الحذف كان ممنوعاً على أي تخصص عليه طلاب أو مشرفون — لسبب وجيه:
 * \u200Estudents.specialize_id\u200E و\u200Esupervisors.specialize_id\u200E كلاهما
 * \u200EnullOnDelete\u200E، و\u200Especialize_projects\u200E \u200EcascadeOnDelete\u200E. فحذف تخصص
 * واحد كان يترك مئات الأشخاص بلا تصنيف ويمحو أنواع مشاريعه نهائياً.
 *
 * لكن المنع وحده ترك الأدمن بلا مخرج. والتخصص الذي درس فيه طلاب
 * تاريخٌ لا يُمحى بل يُوقَف: لا يُسجَّل عليه أحد جديد، وكل ما فيه
 * يبقى سليماً ويعمل كما كان.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('specializes', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('name');
            // كل قائمة إضافة واستيراد تُرشِّح على هذا العمود
            $table->index('archived_at');
        });
    }

    public function down()
    {
        Schema::table('specializes', function (Blueprint $table) {
            $table->dropIndex(['archived_at']);
            $table->dropColumn('archived_at');
        });
    }
};
