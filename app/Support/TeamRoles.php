<?php

namespace App\Support;

use App\Models\Project;

/**
 * الأدوار الجاهزة للفريق — بحسب نوع المشروع، انظر \u200Econfig/team_roles.php\u200E.
 */
class TeamRoles
{
    /** @return array{key: string, label: string, icon: string, hue: int}|null */
    public static function find(string $key): ?array
    {
        $role = config("team_roles.roles.{$key}");

        return $role ? ['key' => $key] + $role : null;
    }

    /** دور حرّ كتبه القائد — وسم عامّ بلون محايد */
    public static function custom(string $label): array
    {
        return ['key' => 'custom', 'label' => $label, 'icon' => 'ti-tag', 'hue' => 220];
    }

    /**
     * الأدوار المقترحة لمشروع: مجموعة نوعه ثم المشتركة.
     *
     * @return array<int, array{key: string, label: string, icon: string, hue: int}>
     */
    public static function presetsFor(Project $project): array
    {
        $type = (string) $project->project_type?->name;

        $keys = [];
        foreach (config('team_roles.sets') as $word => $set) {
            if ($type !== '' && str_contains($type, $word)) {
                $keys = $set;
                break;
            }
        }

        return collect(array_unique(array_merge($keys, config('team_roles.common'))))
            ->map(fn ($key) => self::find($key))
            ->filter()
            ->values()
            ->all();
    }

    public static function max(): int
    {
        return (int) config('team_roles.max_per_member', 4);
    }
}
