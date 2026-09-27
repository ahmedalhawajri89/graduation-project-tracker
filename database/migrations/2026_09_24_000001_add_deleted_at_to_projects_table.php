<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * حذف ناعم للمشاريع.
 *
 * الحذف كان نهائياً، ويمحو معه بالـ cascade كل المراحل والملفات
 * والتعليقات والدرجة النهائية وملاحظات المشرف — بلا أي سبيل للاسترجاع.
 *
 * والـ cascade في قاعدة البيانات لا يعمل إلا على حذف فعلي، فمع الحذف
 * الناعم تبقى هذه الجداول سليمة تلقائياً — وهذا ما يجعل الاسترجاع
 * ممكناً أصلاً.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes();
            // الاستعلامات كلها تُرشِّح على deleted_at بعد إضافة السِمة
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropSoftDeletes();
        });
    }
};
