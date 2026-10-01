<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

/**
 * ما يُحفظ نصّاً ليقرأه غيرك — الإشعارات وبريدها وسجلّ التدقيق — يُكتب
 * بالعربية مهما كانت لغة من أنشأه: المستلم لم يختر لغة المرسل.
 */
class Arabic
{
    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function run(callable $callback): mixed
    {
        $previous = App::getLocale();
        if ($previous === 'ar') {
            return $callback();
        }

        App::setLocale('ar');
        try {
            return $callback();
        } finally {
            App::setLocale($previous);
        }
    }
}
