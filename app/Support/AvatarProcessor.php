<?php

namespace App\Support;

use App\Exceptions\AvatarException;
use Illuminate\Http\UploadedFile;

/**
 * معالجة الصورة الشخصية عند الرفع.
 *
 * بلا حزمة خارجية: لا \u200Eintervention/image\u200E في المشروع، و GD تكفي.
 *
 * والمعالجة عند الرفع لا عند العرض: ٥٠٠ ملف بحجم ٥ ميغا تُبطئ كل جدول
 * في اللوحة، وقصّها مرة واحدة أرخص من تصغيرها في المتصفّح ٢٥ مرة في
 * كل تحميل صفحة.
 *
 * والإخراج JPEG لا WebP: دعم WebP في GD غير مضمون على كل تثبيت، وJPEG
 * مضمون. والفرق في الحجم عند ٢٥٦ بكسل لا يُذكر.
 */
class AvatarProcessor
{
    public const SIZE = 256;
    public const QUALITY = 82;

    public static function available(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    /**
     * يقرأ الملف المرفوع، يقصّه مربّعاً من المركز، ويعيد بايتات JPEG.
     *
     * إعادة الترميز هي الحماية الحقيقية لا فحص الامتداد: ملفّ يتنكّر
     * في صورة لا ينجو منها.
     */
    public static function process(UploadedFile $file): string
    {
        if (! self::available()) {
            throw new AvatarException('إضافة GD غير مفعّلة على الخادم، فلا يمكن معالجة الصور.');
        }

        $source = self::read($file);

        $width = imagesx($source);
        $height = imagesy($source);

        // قصّ مركزي: الصورة المستطيلة تُقصّ ولا تُشوَّه
        $side = min($width, $height);
        $srcX = (int) (($width - $side) / 2);
        $srcY = (int) (($height - $side) / 2);

        $canvas = imagecreatetruecolor(self::SIZE, self::SIZE);

        // الشفافية في PNG تصير سوداء في JPEG — نملأ بالأبيض أولاً
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));

        imagecopyresampled(
            $canvas, $source,
            0, 0, $srcX, $srcY,
            self::SIZE, self::SIZE,
            $side, $side
        );

        imagedestroy($source);

        ob_start();
        imagejpeg($canvas, null, self::QUALITY);
        $bytes = ob_get_clean();

        imagedestroy($canvas);

        if ($bytes === false || $bytes === '') {
            throw new AvatarException('تعذّرت معالجة الصورة.');
        }

        return $bytes;
    }

    /** @return \GdImage */
    private static function read(UploadedFile $file)
    {
        $path = $file->getRealPath();

        // نوع الملف يُقرأ من محتواه لا من امتداده
        $info = @getimagesize($path);

        $image = match ($info[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => false,
        };

        if (! $image) {
            throw new AvatarException('الملف ليس صورة صالحة.');
        }

        return $image;
    }
}
