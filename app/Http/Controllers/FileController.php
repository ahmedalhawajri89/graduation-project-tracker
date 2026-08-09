<?php

namespace App\Http\Controllers;

use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /** تنزيل ملف مشروع — متاح للأدمن، ومشرف المشروع، وأعضاء فريقه فقط */
    public function download(ProjectFile $file)
    {
        $allowed = false;

        if (auth('admin')->check()) {
            $allowed = true;
        } elseif (auth('supervisor')->check()) {
            $allowed = (int) $file->project->supervisor_id === (int) auth('supervisor')->id();
        } elseif (auth('student')->check()) {
            $allowed = $file->project->group()
                ->where('student_id', auth('student')->id())
                ->exists();
        }

        abort_unless($allowed, 403);

        $extension = pathinfo($file->path, PATHINFO_EXTENSION);
        $downloadName = $file->title . '.' . $extension;

        // الملفات الجديدة على القرص الخاص (غير متاح عبر الويب مباشرة)،
        // مع دعم الملفات القديمة المخزنة سابقاً على القرص العام.
        if (Storage::disk('local')->exists($file->path)) {
            return Storage::disk('local')->download($file->path, $downloadName);
        }
        if (Storage::disk('public')->exists($file->path)) {
            return Storage::disk('public')->download($file->path, $downloadName);
        }

        abort(404);
    }
}
