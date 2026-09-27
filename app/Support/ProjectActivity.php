<?php

namespace App\Support;

use App\Models\FileNote;
use App\Models\MilestoneSubmission;
use App\Models\ProjectComment;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\Supervisor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * آخر النشاط في مشروع أو أكثر — للوحتي المشرف والطالب.
 *
 * ملفات رُفعت، وتسليمات وردود المشرف عليها، ومراحل أُنجزت، ورسائل.
 * استعلام محدود لكل مصدر مهما كثرت المشاريع، ثم دمج وترتيب.
 */
class ProjectActivity
{
    /**
     * @param  Collection<int, int>  $projectIds
     * @return Collection<int, array{at: \Carbon\Carbon, icon: string, tone: string, text: string, project: \App\Models\Project, href: string}>
     */
    public static function recent(Collection $projectIds, Model $viewer, int $limit = 8): Collection
    {
        if ($projectIds->isEmpty()) {
            return collect();
        }

        $isSupervisor = $viewer instanceof Supervisor;

        $who = fn ($person) => $person && $person->is($viewer)
            ? 'أنت'
            : ($person?->name ?? 'عضو سابق');

        // الفعل يتبع الفاعل: «رفعتَ» لا «أنت رفع»
        $did = fn ($person, string $he, string $you) => $person && $person->is($viewer)
            ? $you
            : ($person?->name ?? 'عضو سابق') . ' ' . $he;

        // الروابط بحسب من يرى: المشرف إلى صفحة المشروع، والطالب إلى لوحته
        $projectUrl = fn (int $id, string $anchor = '') => ($isSupervisor
            ? route('supervisor.projects.show', $id)
            : route('student.dashboard')) . $anchor;
        $chatUrl = fn (int $id) => $isSupervisor ? route('supervisor.discussion', $id) : route('student.discussion');

        $files = ProjectFile::whereIn('project_id', $projectIds)->with(['project:id,title', 'uploader'])
            ->latest('id')->limit($limit)->get()
            ->map(fn ($f) => [
                'at' => $f->created_at,
                'icon' => 'ti-file-upload',
                'tone' => '',
                'text' => $did($f->uploader, 'رفع', 'رفعتَ') . ' «' . $f->title . '»',
                'project' => $f->project,
                'href' => $projectUrl($f->project_id, '#files'),
            ]);

        $submissions = MilestoneSubmission::whereHas('milestone', fn ($q) => $q->whereIn('project_id', $projectIds))
            ->with(['milestone.project:id,title', 'student', 'reviewer'])
            ->latest('id')->limit($limit)->get();

        $submitted = $submissions->map(fn ($s) => [
            'at' => $s->created_at,
            'icon' => 'ti-upload',
            'tone' => 'is-brand',
            'text' => ($s->round > 1 ? $did($s->student, 'أعاد تسليم', 'أعدتَ تسليم') : $did($s->student, 'سلّم', 'سلّمتَ')) . ' «' . $s->milestone->title . '»',
            'project' => $s->milestone->project,
            'href' => $projectUrl($s->milestone->project_id, '#milestone-' . $s->milestone_id),
        ]);

        // ردّ المشرف: حدث مستقلّ بوقته — الاعتماد والتعديل أهمّ ما يقرؤه الفريق
        $reviewed = $submissions->filter(fn ($s) => $s->decision && $s->reviewed_at)->map(fn ($s) => [
            'at' => $s->reviewed_at,
            'icon' => $s->decision === ProjectMilestone::APPROVED ? 'ti-circle-check' : 'ti-pencil',
            'tone' => $s->decision === ProjectMilestone::APPROVED ? 'is-success' : 'is-warn',
            'text' => ($s->decision === ProjectMilestone::APPROVED
                ? ($isSupervisor ? 'اعتمدتَ' : 'اعتمد المشرف') . ' «' . $s->milestone->title . '»'
                : ($isSupervisor ? 'طلبتَ' : 'طلب المشرف') . ' تعديلاً في «' . $s->milestone->title . '»: ' . Str::limit((string) $s->feedback, 60)),
            'project' => $s->milestone->project,
            'href' => $projectUrl($s->milestone->project_id, '#milestone-' . $s->milestone_id),
        ]);

        // المنجز بلا تسليم وحده: المسلَّم يظهر حدثُ اعتماده أعلاه
        $done = ProjectMilestone::whereIn('project_id', $projectIds)->whereNotNull('done_at')
            ->whereDoesntHave('submissions')->with('project:id,title')
            ->latest('done_at')->limit($limit)->get()
            ->map(fn ($m) => [
                'at' => $m->done_at,
                'icon' => 'ti-circle-check',
                'tone' => 'is-success',
                'text' => 'أُنجزت مرحلة «' . $m->title . '»',
                'project' => $m->project,
                'href' => $projectUrl($m->project_id, '#milestone-' . $m->id),
            ]);

        $comments = ProjectComment::whereIn('project_id', $projectIds)->with(['project:id,title', 'author'])
            ->latest('id')->limit($limit)->get()
            ->map(fn ($c) => [
                'at' => $c->created_at,
                'icon' => 'ti-message',
                'tone' => '',
                'text' => $who($c->author) . ': ' . Str::limit($c->body, 70),
                'project' => $c->project,
                'href' => $chatUrl($c->project_id),
            ]);

        // ملاحظات على الملفات: «ترك ملاحظة على…» — وحدث المعالجة بوقته
        $notes = FileNote::whereHas('file', fn ($q) => $q->whereIn('project_id', $projectIds))
            ->with(['file.project:id,title', 'author', 'resolver'])
            ->latest('id')->limit($limit)->get();

        $noted = $notes->map(fn ($n) => [
            'at' => $n->created_at,
            'icon' => 'ti-message-2-exclamation',
            'tone' => 'is-warn',
            'text' => $did($n->author, 'ترك ملاحظة على', 'تركتَ ملاحظة على') . ' «' . $n->file->title . '»',
            'project' => $n->file->project,
            'href' => $projectUrl($n->file->project_id, '#file-' . $n->project_file_id),
        ]);

        $resolvedNotes = $notes->filter(fn ($n) => $n->resolved_at)->map(fn ($n) => [
            'at' => $n->resolved_at,
            'icon' => 'ti-circle-check',
            'tone' => 'is-success',
            'text' => $did($n->resolver, 'عالج ملاحظة على', 'عالجتَ ملاحظة على') . ' «' . $n->file->title . '»',
            'project' => $n->file->project,
            'href' => $projectUrl($n->file->project_id, '#file-' . $n->project_file_id),
        ]);

        return $files->concat($noted)->concat($resolvedNotes)->concat($submitted)->concat($reviewed)->concat($done)->concat($comments)
            ->filter(fn ($a) => $a['at'] && $a['project'])
            ->sortByDesc(fn ($a) => $a['at']->timestamp)
            ->values()
            ->take($limit);
    }
}
