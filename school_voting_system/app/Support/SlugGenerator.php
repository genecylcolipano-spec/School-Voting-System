<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SlugGenerator
{
    public static function unique(string $title, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'item';
        $slug = $base;
        $counter = 1;

        while (self::exists($modelClass, $slug, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected static function exists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        /** @var class-string<Model> $modelClass */
        $query = $modelClass::query()->where('slug', $slug);

        // Soft-deleted rows still occupy unique DB indexes — include them.
        if (self::usesSoftDeletes($modelClass)) {
            $query->withTrashed();
        }

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected static function usesSoftDeletes(string $modelClass): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);
    }
}
