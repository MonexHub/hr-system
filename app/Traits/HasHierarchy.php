<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasHierarchy
{
    protected function getParentHierarchyQuery(): string
    {
        return "
            WITH RECURSIVE parent_hierarchy AS (
                SELECT id, parent_id, name, 0 as depth
                FROM {$this->getTable()}
                WHERE id = ?
                UNION ALL
                SELECT p.id, p.parent_id, p.name, ph.depth + 1
                FROM {$this->getTable()} p
                INNER JOIN parent_hierarchy ph ON p.id = ph.parent_id
            )
            SELECT *
            FROM parent_hierarchy
            ORDER BY depth DESC
        ";
    }

    protected function getDescendantHierarchyQuery(): string
    {
        return "
            WITH RECURSIVE child_hierarchy AS (
                SELECT id, parent_id, level, 0 as depth
                FROM {$this->getTable()}
                WHERE id = ?
                UNION ALL
                SELECT c.id, c.parent_id, c.level, ch.depth + 1
                FROM {$this->getTable()} c
                INNER JOIN child_hierarchy ch ON c.parent_id = ch.id
            )
            SELECT *
            FROM child_hierarchy
        ";
    }

    public function getFullHierarchyAttribute(): string
    {
        try {
            $hierarchy = DB::select($this->getParentHierarchyQuery(), [$this->id]);
            return collect($hierarchy)->pluck('name')->join(' > ');
        } catch (\Exception $e) {
            Log::error("Error getting hierarchy for " . class_basename($this) . ": " . $e->getMessage());
            return $this->name;
        }
    }

    public function getAllDescendantIds(): array
    {
        try {
            $descendants = DB::select($this->getDescendantHierarchyQuery(), [$this->id]);
            return collect($descendants)->pluck('id')->toArray();
        } catch (\Exception $e) {
            Log::error("Error getting descendant IDs for " . class_basename($this) . ": " . $e->getMessage());
            return [];
        }
    }

    protected function calculateLevel(): int
    {
        try {
            $result = DB::select("
                WITH RECURSIVE parent_levels AS (
                    SELECT id, parent_id, 0 as level
                    FROM {$this->getTable()}
                    WHERE id = ?
                    UNION ALL
                    SELECT p.id, p.parent_id, pl.level + 1
                    FROM {$this->getTable()} p
                    INNER JOIN parent_levels pl ON p.id = pl.parent_id
                )
                SELECT MAX(level) as calculated_level
                FROM parent_levels
            ", [$this->id]);

            return $result[0]->calculated_level ?? 0;
        } catch (\Exception $e) {
            Log::error("Error calculating level for " . class_basename($this) . ": " . $e->getMessage());
            return 0;
        }
    }

    protected function updateHierarchyLevels(): void
    {
        try {
            DB::transaction(function () {
                $descendants = $this->getAllDescendantIds();
                $baseLevel = $this->calculateLevel();

                foreach (array_chunk($descendants, static::CHUNK_SIZE) as $chunk) {
                    DB::table($this->getTable())
                        ->whereIn('id', $chunk)
                        ->update([
                            'level' => DB::raw("(
                                SELECT base_level + depth
                                FROM (
                                    {$this->getDescendantHierarchyQuery()}
                                ) AS hierarchy
                                WHERE hierarchy.id = {$this->getTable()}.id
                            )"),
                        ]);
                }

                $this->level = $baseLevel;
                $this->save();
            }, static::MAX_RETRY_ATTEMPTS);
        } catch (\Exception $e) {
            Log::error("Error updating hierarchy levels for " . class_basename($this) . ": " . $e->getMessage());
        }
    }

    // Helper methods
    public function getParentPath(): array
    {
        try {
            $hierarchy = DB::select($this->getParentHierarchyQuery(), [$this->id]);
            return collect($hierarchy)->pluck('name')->toArray();
        } catch (\Exception $e) {
            Log::error("Error getting parent path for " . class_basename($this) . ": " . $e->getMessage());
            return [$this->name];
        }
    }

    public function getDepth(): int
    {
        return $this->calculateLevel();
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function isLeaf(): bool
    {
        return !$this->children()->exists();
    }

    public function getSiblings()
    {
        return static::where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    public function isAncestorOf($node): bool
    {
        return in_array($this->id, $node->getParentIds());
    }

    protected function getParentIds(): array
    {
        try {
            $hierarchy = DB::select($this->getParentHierarchyQuery(), [$this->id]);
            return collect($hierarchy)->pluck('id')->toArray();
        } catch (\Exception $e) {
            Log::error("Error getting parent IDs for " . class_basename($this) . ": " . $e->getMessage());
            return [];
        }
    }
}
