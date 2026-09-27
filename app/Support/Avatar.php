<?php

namespace App\Support;

/**
 * ترميز الأفاتار للمواضع التي تُبنى في PHP لا في Blade.
 *
 * خليّتا الهوية في جدولَي الطلاب والمشرفين تُبنيان داخل المتحكّم
 * (\u200EDataTables::addColumn\u200E)، فلا يصل إليهما المكوّن \u200E<x-avatar>\u200E.
 * الترميز هنا مطابق له حرفاً بحرف حتى لا ينحرف الشكلان.
 */
class Avatar
{
    /**
     * @param  object  $user  نموذج يستعمل سِمة HasAvatar
     * @param  string  $class  اسم الصنف في نظام التصميم (cell-avatar، ctx-avatar…)
     */
    public static function html($user, string $class = 'cell-avatar'): string
    {
        $url = $user->avatar_url;

        if ($url) {
            return '<span class="' . e($class) . ' has-photo">'
                . '<img src="' . e($url) . '" alt="" loading="lazy" decoding="async">'
                . '</span>';
        }

        return '<span class="' . e($class) . '">' . e($user->initials) . '</span>';
    }
}
