<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * كل نصّ عربي مغلَّف بـ __() أو trans_choice() أو t() له ترجمة إنجليزية.
 *
 * العربية هي المفتاح، فنصّ بلا ترجمة لا يكسر شيئاً — يظهر عربياً وسط
 * الواجهة الإنجليزية بصمت. هذا الاختبار يجعل النسيان يفشل بدل أن يمرّ.
 */
class TranslationKeysTest extends TestCase
{
    private const ARABIC = '/\p{Arabic}/u';

    /** @return array<string, string> */
    private function english(): array
    {
        $all = [];
        foreach (glob(resource_path('lang/json/*/en.json')) as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            $this->assertIsArray($data, "JSON غير صالح: {$file}");
            $all += $data;
        }

        return $all;
    }

    /** @return array<string, string> نصّ ← أول موضع له */
    private function used(): array
    {
        $files = array_merge(
            $this->files(resource_path('views'), '.blade.php'),
            $this->files(app_path(), '.php'),
            $this->files(public_path('js'), '.js'),
        );

        // __('…') و trans_choice('…') و t('…') — بعلامتَي تنصيص مفردة أو مزدوجة
        $pattern = '/(?<![\w$>])(?:__|trans_choice|t)\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")/u';
        $used = [];

        foreach ($files as $file) {
            // التعليقات تذكر أمثلة مثل __('…') — تُفرَّغ مع حفظ أرقام الأسطر
            $blank = fn ($m) => str_repeat("\n", substr_count($m[0], "\n"));
            $source = (string) file_get_contents($file);
            $source = preg_replace_callback('/\{\{--.*?--\}\}|\/\*.*?\*\//s', $blank, $source);
            $source = preg_replace('/(?<![:\'"])\/\/[^\n]*/', '', $source);
            preg_match_all($pattern, $source, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($m as $match) {
                $text = ($match[1][0] ?? '') !== '' ? stripslashes($match[1][0]) : stripslashes($match[2][0] ?? '');
                if ($text === '' || ! preg_match(self::ARABIC, $text)) {
                    continue;
                }
                $line = substr_count(substr($source, 0, $match[0][1]), "\n") + 1;
                $used[$text] ??= str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file) . ':' . $line;
            }
        }

        return $used;
    }

    /** @return string[] */
    private function files(string $dir, string $suffix): array
    {
        $out = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (str_ends_with($file->getFilename(), $suffix)) {
                $out[] = $file->getPathname();
            }
        }

        return $out;
    }

    public function test_every_wrapped_arabic_string_has_an_english_translation(): void
    {
        $english = $this->english();
        $missing = array_diff_key($this->used(), $english);

        $this->assertSame([], $missing, "نصوص بلا ترجمة إنجليزية (أضفها إلى resources/lang/json/<الجزء>/en.json):\n"
            . collect($missing)->map(fn ($where, $text) => "  {$where}  {$text}")->implode("\n"));
    }

    public function test_translations_keep_their_placeholders(): void
    {
        $broken = [];
        foreach ($this->english() as $ar => $en) {
            preg_match_all('/:[a-z_]+/', $ar, $a);
            preg_match_all('/:[a-z_]+/', (string) $en, $b);
            sort($a[0]);
            sort($b[0]);
            if ($a[0] !== $b[0]) {
                $broken[] = "{$ar}  →  {$en}";
            }
        }

        $this->assertSame([], $broken, "متغيّرات :name لا تتطابق بين النصّ وترجمته:\n" . implode("\n", $broken));
    }
}
