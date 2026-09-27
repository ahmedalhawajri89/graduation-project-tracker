<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdministratorsRequest;
use App\Models\Admin;
use App\Support\Audit;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{

    public function index()
    {
        $data['countAll'] = Admin::count();
        $data['currentId'] = (int) auth('admin')->id();

        return view('dashboard.admin.adminData.index', $data);
    }

    public function getData()
    {
        // كان يستثني \u200Eauth()->id()\u200E، وفي قاعدة فيها أدمن واحد يعني ذلك
        // جدولاً فارغاً تماماً يبدو معطوباً. وحتى مع حسابين، لا ترى
        // حسابك أنت فلا تعرف ببريد أيّها أنت داخل. يظهر الآن موسوماً.
        $currentId = (int) auth('admin')->id();
        $total = Admin::count();

        $admins = Admin::select('id', 'name', 'email', 'phone', 'gender', 'created_at');

        return DataTables::of($admins)
            ->addIndexColumn()

            ->editColumn('gender', function ($row) {
                return __('site.' . $row->gender);
            })

            ->addColumn('identity', function ($row) use ($currentId) {
                $you = (int) $row->id === $currentId
                    ? '<span class="cell-you">أنت</span>'
                    : '';

                return '<div class="cell-identity">'
                    . '<span class="cell-avatar">' . e(mb_substr($row->name, 0, 2)) . '</span>'
                    . '<span class="cell-identity-body">'
                    . '<span class="cell-name">' . e($row->name) . $you . '</span>'
                    . '<span class="cell-sub" dir="ltr">' . e($row->email) . '</span>'
                    . '</span>'
                    . '</div>';
            })

            ->editColumn('phone', function ($row) {
                return $row->phone
                    ? '<span class="cell-num" dir="ltr">' . e($row->phone) . '</span>'
                    : '<span class="text-secondary small">—</span>';
            })

            // \u200Ecreated_at\u200E موجود في الجدول منذ البداية ولا يُعرض في أي
            // مكان — وهو ما يميّز الحساب المؤسِّس من حساب أُضيف أمس
            ->editColumn('created_at', function ($row) {
                if (! $row->created_at) {
                    return '<span class="text-secondary small">—</span>';
                }

                return '<span class="cell-when" title="' . e($row->created_at->format('Y-m-d H:i')) . '">'
                    . e($row->created_at->translatedFormat('j F Y'))
                    . '</span>';
            })

            ->addColumn('actions', function ($row) use ($currentId, $total) {

                $editBtn = "<a class='btn-action btn-edit' data-bs-toggle='modal' data-bs-target='#editModal'
                              data-id='" . e($row->id) . "' data-name='" . e($row->name) . "' data-email='" . e($row->email) . "'
                              data-phone='" . e($row->phone) . "' data-gender='" . e($row->gender) . "' title='تعديل'>
                              <i class='ti ti-pencil'></i>
                          </a>";

                // لا زرّ حذف على صفّك ولا على آخر حساب — والخادم يحرس
                // الحالتين أيضاً في \u200Edestroy()\u200E، فالواجهة تريح لا تحمي
                $deletable = (int) $row->id !== $currentId && $total > 1;

                $deleteBtn = $deletable
                    ? "<button type='button' class='btn-action btn-action--danger btn-delete' data-bs-toggle='modal' data-bs-target='#deleteModal'
                              data-id='" . e($row->id) . "' data-name='" . e($row->name) . "' title='حذف'>
                              <i class='ti ti-trash'></i>
                          </button>"
                    : '';

                return '<div class="btn-group">' . $deleteBtn . $editBtn . '</div>';
            })
            ->rawColumns(['identity', 'phone', 'created_at', 'actions'])
            ->make(true);
    }

    public function store(AdministratorsRequest $request)
    {
        try {
            // dd($request->all());
            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            $created = Admin::create($data);

            // إنشاء حساب بصلاحية كاملة على النظام أثرٌ يجب أن يُعرف
            Audit::record('admin.created', $created);

            return redirect()->route("admin.administrators.index")->with('success', "تم اضافة السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

    }

    public function update(AdministratorsRequest $request)
    {
        try {
            $admin = Admin::where('id', $request->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            $admin->update($data);
            return redirect()->back()->with('success', "تم تعديل السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    public function destroy($id)
    {
        try {
            $admin = Admin::where('id', request()->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            // حارسان على الخادم لا في الواجهة وحدها: الطلب يصل بـ
            // \u200Erequest()->id\u200E أيّاً كان، فإخفاء الزرّ لا يمنع شيئاً.

            // حذف حسابك يُخرجك من نظامك أثناء استعماله
            if ((int) $admin->id === (int) auth('admin')->id()) {
                return redirect()->back()->with('fail',
                    'لا يمكنك حذف حسابك أنت. اطلب من مسؤول آخر ذلك.');
            }

            // وحذف الأخير يقفل اللوحة على الجميع بلا طريق للتراجع —
            // لا استرجاع ولا «نسيت كلمة المرور» لحساب لم يعد موجوداً
            if (Admin::count() <= 1) {
                return redirect()->back()->with('fail',
                    'هذا آخر حساب مسؤول — حذفه يقفل لوحة التحكم على الجميع بلا رجعة.');
            }

            // قبل الحذف: بعده يفقد الحساب اسمه ومفتاحه
            Audit::record('admin.deleted', $admin);

            $admin->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }
}
