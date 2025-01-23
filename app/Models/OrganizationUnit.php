<?php

namespace App\Models;

use App\Traits\HasHierarchy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class OrganizationUnit extends Model
{
    use SoftDeletes, HasHierarchy;

    public const TYPE_COMPANY = 'company';
    public const TYPE_DIVISION = 'division';
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_TEAM = 'team';
    public const TYPE_UNIT = 'unit';

    protected const CHUNK_SIZE = 1000;
    protected const MAX_RETRY_ATTEMPTS = 5;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'description',
        'head_employee_id',
        'unit_type',
        'level',
        'is_active',
        'order_index',
        'annual_budget',
        'current_headcount',
        'max_headcount',
        'phone',
        'email',
        'location'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'order_index' => 'integer',
        'annual_budget' => 'decimal:2',
        'current_headcount' => 'integer',
        'max_headcount' => 'integer'
    ];

    protected $appends = ['full_hierarchy'];

    // Relationships
    public function department(): HasOne
    {
        return $this->hasOne(Department::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')
            ->withDefault(['name' => '']);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('order_index')
            ->orderBy('name');
    }

    public function headEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'head_employee_id')
            ->withDefault(['name' => '']);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'unit_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootUnits($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrderedByHierarchy($query)
    {
        return $query->orderBy('level')
            ->orderBy('order_index')
            ->orderBy('name');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('unit_type', $type);
    }

    public function scopeWithoutDepartment($query)
    {
        return $query->doesntHave('department');
    }

    public function scopeWithDepartment($query)
    {
        return $query->has('department');
    }

    // Helper Methods
    public function isOfType(string $type): bool
    {
        return $this->unit_type === $type;
    }

    public function canHaveChildren(): bool
    {
        return !$this->isOfType(self::TYPE_TEAM);
    }

    public function getEmployeeCount(): int
    {
        return $this->employees()->count();
    }

    public function hasDepartment(): bool
    {
        return $this->department()->exists();
    }

    public static function getUnitTypes(): array
    {
        return [
            self::TYPE_COMPANY => 'Company',
            self::TYPE_DIVISION => 'Division',
            self::TYPE_DEPARTMENT => 'Department',
            self::TYPE_TEAM => 'Team',
            self::TYPE_UNIT => 'Unit',
        ];
    }

    // Boot Method
    protected static function boot()
    {
        parent::boot();

        static::created(function ($unit) {
            try {
                $unit->updateHierarchyLevels();
            } catch (\Exception $e) {
                Log::error("Error in created event: " . $e->getMessage());
            }
        });

        static::updated(function ($unit) {
            if ($unit->isDirty('parent_id')) {
                try {
                    $unit->updateHierarchyLevels();
                } catch (\Exception $e) {
                    Log::error("Error in updated event: " . $e->getMessage());
                }
            }
        });
    }
}
