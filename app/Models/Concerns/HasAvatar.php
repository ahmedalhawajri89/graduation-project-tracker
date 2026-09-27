<?php

namespace App\Models\Concerns;

use App\Support\AvatarProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * الصورة الشخصية — للطالب والمشرف والمسؤول.
 *
 * الصورة اختيارية دائماً، والأحرف الأولى هي الاحتياطي. فلا حالة فارغة
 * جديدة ولا ينكسر شيء لمن لم يرفع.
 */
trait HasAvatar
{
    protected static string $avatarDisk = 'avatars';

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        // الملف قد يكون حُذف من القرص يدوياً بينما بقي الاسم في الصفّ
        if (! Storage::disk(static::$avatarDisk)->exists($this->avatar)) {
            return null;
        }

        return Storage::disk(static::$avatarDisk)->url($this->avatar);
    }

    /** الأحرف الأولى: «د.» و«أ.د.» بادئة على أغلب أسماء المشرفين فلا تميّز أحداً */
    public function getInitialsAttribute(): string
    {
        $bare = preg_replace('/^\s*(أ\.د\.|د\.|أ\.)\s*/u', '', (string) $this->name);

        return mb_substr(trim($bare) ?: '؟', 0, 2);
    }

    public function storeAvatar(UploadedFile $file): void
    {
        $bytes = AvatarProcessor::process($file);

        $name = Str::random(40) . '.jpg';

        Storage::disk(static::$avatarDisk)->put($name, $bytes);

        // القديمة تُحذف بعد نجاح الجديدة، وإلا تراكمت ملفات يتيمة
        $this->deleteAvatarFile();

        $this->avatar = $name;
        $this->save();
    }

    /**
     * حذف الحساب يحذف صورته. كان \u200Edestroy()\u200E عند الأدمن يحذف الصفّ ويترك
     * الملف يتيماً في \u200Epublic/uploads\u200E. هنا لا في كل متحكّم: أيّاً كان من حذف.
     */
    protected static function bootHasAvatar(): void
    {
        static::deleted(fn ($model) => $model->deleteAvatarFile());
    }

    public function deleteAvatar(): void
    {
        $this->deleteAvatarFile();

        $this->avatar = null;
        $this->save();
    }

    private function deleteAvatarFile(): void
    {
        if ($this->avatar) {
            Storage::disk(static::$avatarDisk)->delete($this->avatar);
        }
    }
}
