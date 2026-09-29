<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\Student;
use App\Models\Supervisor;
use App\Support\ContactTopic;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContactController extends Controller
{
    public function index()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        // كان يعلّم كل الرسائل مقروءة بمجرد فتح الصفحة، فيفقد الأدمن
        // تتبّع ما لم يعالجه بعد. الآن تُعلَّم الرسالة مقروءة حين تُفتح
        // وحدها — والفتح عبر ?open=ID فيعمل بلا جافاسكربت.
        // \u200E(int) request()\u200E لا \u200Erequest()->integer()\u200E: الأولى صريحة ولا
        // تعتمد على سلوك مُساعد قد يختلف بين إصدارات
        $openId = (int) request('open') ?: null;

        if ($openId) {
            Contact::where('id', $openId)->where('is_read', 0)->update(['is_read' => 1]);
        }

        $q = trim((string) request('q'));
        $onlyUnread = request()->boolean('unread');
        $topic = request('topic');
        $topic = array_key_exists((string) $topic, ContactTopic::TOPICS) || $topic === 'other' ? $topic : null;

        // الموضوع يُستنتج في PHP (ContactTopic::of) فتُصفّى الرسائل هنا لا في
        // SQL — هي قليلة بطبيعتها، وهكذا يتّفق العدّ والتصفية مع الشارة
        $scoped = Contact::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('subject', 'like', "%{$q}%")
                        ->orWhere('message', 'like', "%{$q}%");
                });
            })
            ->when($onlyUnread, fn ($query) => $query->where('is_read', 0))
            ->latest()->latest('id')
            ->get();

        $data['topicCounts'] = ContactTopic::counts($scoped);
        $filtered = $topic ? $scoped->filter(fn ($m) => ContactTopic::of($m) === $topic)->values() : $scoped;

        $page = max(1, (int) request('page', 1));
        $data['messages'] = new LengthAwarePaginator(
            $filtered->forPage($page, 20)->values(), $filtered->count(), 20, $page,
            ['path' => request()->url(), 'query' => request()->except('page')]
        );

        $data['unreadCount'] = Contact::where('is_read', 0)->count();
        $data['totalCount'] = Contact::count();
        $data['openId'] = $openId;
        $data['q'] = $q;
        $data['onlyUnread'] = $onlyUnread;
        $data['topic'] = $topic;

        // مؤشّرات الشريط العلوي
        $all = Contact::all(['id', 'subject', 'message']);
        // «أخرى» ليست موضوعاً يُذكر كأكثر ما يُسأل عنه
        $allCounts = array_diff_key(ContactTopic::counts($all), ['other' => 0]);
        arsort($allCounts);
        $topKey = array_key_first(array_filter($allCounts));
        $data['stats'] = [
            'week' => Contact::where('created_at', '>=', now()->subDays(7))->count(),
            'accounts' => Contact::whereIn('email', Student::select('email'))
                ->orWhereIn('email', Supervisor::select('email'))->count(),
            'top' => $topKey ? ['key' => $topKey, 'n' => $allCounts[$topKey]] : null,
        ];

        // الرسالة المفتوحة تُحمَّل بمفتاحها لا من صفحة القائمة: قد
        // تكون في صفحة أخرى، أو خارج التصفية الحالية — فتُفتح ويبقى
        // لوح القراءة فارغاً بلا تفسير
        $open = $openId ? Contact::find($openId) : null;
        $data['openMessage'] = $open;

        // السابقة والتالية ضمن القائمة كما تُعرض الآن (بحثاً وتبويباً وموضوعاً)
        $ids = $filtered->pluck('id')->all();
        $at = $open ? array_search($open->id, $ids, true) : false;
        $data['prevId'] = $at !== false && $at > 0 ? $ids[$at - 1] : null;
        $data['nextId'] = $at !== false && $at < count($ids) - 1 ? $ids[$at + 1] : null;

        // ما أرسله الشخص نفسه من قبل: هل سأل هذا مرّة؟
        $data['history'] = $open
            ? Contact::where('email', $open->email)->where('id', '!=', $open->id)->latest()->take(5)->get()
            : collect();

        $emails = $data['messages']->getCollection()->pluck('email');
        if ($open) {
            $emails->push($open->email);
        }
        $data['senders'] = $this->senders($emails);

        return view('dashboard.admin.messages.index', $data);
    }

    /**
     * من المُرسِل؟ طالب أو مشرف أو مسؤول بحساب في المنصّة — أو زائر.
     * استعلام واحد لكل دور للصفحة كلها، والمفتاح البريد بأحرف صغيرة.
     */
    private function senders(Collection $emails): array
    {
        $emails = $emails->filter()->map(fn ($e) => mb_strtolower($e))->unique()->values();
        if ($emails->isEmpty()) {
            return [];
        }

        $out = [];

        Student::whereIn('email', $emails)->with(['specialize', 'groups.project'])->get()
            ->each(function ($s) use (&$out) {
                $projects = $s->groups->pluck('project')->filter();
                $project = $projects->first(fn ($p) => $p->status !== 'reject') ?? $projects->first();
                $out[mb_strtolower($s->email)] = [
                    'role' => 'student',
                    'label' => 'طالب',
                    'meta' => array_values(array_filter([$s->university_id, $s->specialize->name])),
                    'link' => $project ? route('admin.groups.show', $project->id) : null,
                    'link_label' => $project ? $project->title : null,
                    'link_hint' => $project ? 'مشروعه' : 'لم ينضمّ إلى فريق بعد',
                ];
            });

        Supervisor::whereIn('email', $emails)->with('specialize')->withCount('projectsAccept')->get()
            ->each(function ($s) use (&$out) {
                $out[mb_strtolower($s->email)] = [
                    'role' => 'supervisor',
                    'label' => 'مشرف',
                    'meta' => array_values(array_filter([$s->specialize->name ?? null])),
                    'link' => route('admin.supervisors.groups', $s->id),
                    'link_label' => $s->projects_accept_count . ' ' . ($s->projects_accept_count === 1 ? 'مجموعة' : 'مجموعات'),
                    'link_hint' => 'مجموعاته',
                ];
            });

        Admin::whereIn('email', $emails)->get()->each(function ($a) use (&$out) {
            $out[mb_strtolower($a->email)] ??= ['role' => 'admin', 'label' => 'مسؤول', 'meta' => [], 'link' => null, 'link_label' => null, 'link_hint' => null];
        });

        return $out;
    }

    /** إعادة رسالة إلى «غير مقروءة» — لم يكن ممكناً إطلاقاً */
    public function markUnread($id)
    {
        Contact::where('id', $id)->update(['is_read' => 0]);

        return redirect()->back()->with('success', 'أُعيدت الرسالة إلى غير المقروءة.');
    }

    /** تعليم الكل كمقروء — فعل صريح بدل أن يقع تلقائياً */
    public function markAllRead()
    {
        $n = Contact::where('is_read', 0)->update(['is_read' => 1]);

        return redirect()->back()->with('success', "عُلِّمت {$n} رسالة كمقروءة.");
    }

    public function destroy($id)
    {
        // كان يقرأ request()->id ويتجاهل معامل المسار
        $message = Contact::find($id ?: request()->id);

        if (! $message) {
            return redirect()->back()->with('fail', 'الرسالة غير موجودة.');
        }

        try {
            $message->delete();

            return redirect()->back()->with('success', 'حُذفت الرسالة.');
        } catch (\Exception $ex) {
            report($ex);

            return redirect()->back()->with('fail', 'تعذّر حذف الرسالة. حاول مرة أخرى.');
        }
    }
}
