<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AcademicYear extends BaseModel
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active academic years.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    public function active($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include upcoming academic years.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    public function upcoming($query): void
    {
        $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include past academic years.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    public function past($query): void
    {
        $query->where('end_date', '<', now());
    }

    /**
     * Scope a query to only include current academic years (based on dates).
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    public function current($query): void
    {
        $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Get the duration of the academic year in days.
     */
    protected function durationInDays(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->start_date->diffInDays($this->end_date),
        );
    }

    /**
     * Set this academic year as active and deactivate all others.
     */
    public function activate(): bool
    {
        return DB::transaction(function () {
            // Deactivate all other academic years
            static::where('id', '!=', $this->id)->update(['is_active' => false]);

            // Activate this academic year
            $this->is_active = true;
            $saved = $this->save();

            activity()
                ->performedOn($this)
                ->event('activated')
                ->withProperties(['name' => $this->name])
                ->log('activated');

            return $saved;
        });
    }

    /**
     * Validation rules for creating/updating academic years.
     */
    public static function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:academic_years,name,' . $id],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get validation messages.
     */
    public static function messages(): array
    {
        return [
            'name.required' => __('academic_year.name.required'),
            'name.unique' => __('academic_year.name.unique'),
            'start_date.required' => __('academic_year.start_date.required'),
            'start_date.date' => __('academic_year.start_date.date'),
            'end_date.required' => __('academic_year.end_date.required'),
            'end_date.date' => __('academic_year.end_date.date'),
            'end_date.after' => __('academic_year.end_date.after'),
        ];
    }
}
