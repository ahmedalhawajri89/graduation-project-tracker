<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * خطأ في معالجة الصورة الشخصية — رسالته مكتوبة للمستخدم.
 *
 * وجوده ضروري لا تنظيمي: \u200EQueryException\u200E في Laravel يرث من
 * \u200EPDOException\u200E الذي يرث من \u200ERuntimeException\u200E. فـ\u200Ecatch (RuntimeException)\u200E
 * كان يلتقط أخطاء قاعدة البيانات ويعرض نصّ الاستعلام كاملاً للمستخدم —
 * اسم الجدول والأعمدة والمضيف والمنفذ.
 *
 * هذا الصنف يفصل «خطأ نقوله للمستخدم» عن «عطل نسجّله ولا نعرضه».
 */
class AvatarException extends RuntimeException
{
}
