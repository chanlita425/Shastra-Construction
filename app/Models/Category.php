<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name_en',
        'name_km',
        'name_ch',
        'slug',
        'order'
    ];

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->id)) {
                if (method_exists(Str::class, 'cuid')) {
                    $category->id = (string) Str::cuid();
                } else {
                    $category->id = strtolower(Str::random(10));
                }
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
