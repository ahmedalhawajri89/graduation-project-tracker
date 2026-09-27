<?php

namespace App\Exports;

use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * لا صيغ في التصدير.
 *
 * ‎DefaultValueBinder‎ يخزّن النصّ الذي يبدأ بـ «=» صيغةً، فعنوان مشروع يكتبه
 * طالب مثل ‎=HYPERLINK("http://…")‎ ينفَّذ حين يفتح الأدمن الملف. النصّ الذي
 * يبدأ بمحرف صيغة يُخزَّن نصّاً صريحاً — Excel لا يقيّم خليّة نصّية، والمحتوى
 * يبقى كما كُتب بلا محرف مضاف.
 */
class SafeValueBinder extends DefaultValueBinder
{
    private const FORMULA_LEADS = ['=', '+', '-', '@', "\t", "\r"];

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], self::FORMULA_LEADS, true)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
