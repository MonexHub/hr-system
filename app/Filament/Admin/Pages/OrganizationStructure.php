<?php

namespace App\Filament\Admin\Pages;

use App\Models\OrganizationUnit;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class OrganizationStructure extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Organization Management';
//    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Organization Structure';
    protected static string $view = 'filament.admin.pages.organization-structure';


    // Properties
    public bool $readyToLoad = false;
    public string $searchQuery = '';
    public string $selectedUnitType = 'all';
    public bool $expandAll = true;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function getData(): array
    {
        if (!$this->readyToLoad) {
            return $this->getEmptyData();
        }

        try {
            $query = OrganizationUnit::query()
                ->withCount(['employees', 'children'])
                ->with(['children', 'headEmployee', 'parent', 'department']);

            if ($this->searchQuery) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('code', 'like', "%{$this->searchQuery}%");
                });
            }

            if ($this->selectedUnitType !== 'all') {
                $query->where('unit_type', $this->selectedUnitType);
            }

            $units = $query->whereNull('parent_id')
                ->orderBy('order_index')
                ->orderBy('name')
                ->get();

            // Calculate statistics
            $allUnits = OrganizationUnit::withCount('employees')->get();
            $departmentCount = OrganizationUnit::where('unit_type', 'department')->count();
            $totalEmployees = $allUnits->sum('employees_count');

            return [
                'units' => $units,
                'totalUnits' => $allUnits->count(),
                'totalEmployees' => $totalEmployees,
                'totalDepartments' => $departmentCount,
            ];

        } catch (\Exception $e) {
            Log::error('Error in Organization Structure:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getEmptyData();
        }
    }

    protected function getEmptyData(): array
    {
        return [
            'units' => collect(),
            'totalUnits' => 0,
            'totalEmployees' => 0,
            'totalDepartments' => 0,
        ];
    }

    public function getViewData(): array
    {
        return [
            'unitTypes' => [
                'company' => [
                    'icon' => 'heroicon-o-building-office-2',
                    'color' => 'primary',
                    'label' => 'Company'
                ],
                'division' => [
                    'icon' => 'heroicon-o-squares-2x2',
                    'color' => 'success',
                    'label' => 'Division'
                ],
                'department' => [
                    'icon' => 'heroicon-o-rectangle-stack',
                    'color' => 'warning',
                    'label' => 'Department'
                ],
                'team' => [
                    'icon' => 'heroicon-o-user-group',
                    'color' => 'info',
                    'label' => 'Team'
                ],
                'unit' => [
                    'icon' => 'heroicon-o-cube',
                    'color' => 'secondary',
                    'label' => 'Unit'
                ]
            ]
        ];
    }

    // Action handlers
    public function showViewModal(int $unitId): void
    {
        $this->dispatch('open-modal', [
            'name' => 'view-organization-unit',
            'arguments' => [
                'unitId' => $unitId
            ]
        ]);
    }

    public function showEditModal(int $unitId): void
    {
        $this->dispatch('open-modal', [
            'name' => 'edit-organization-unit',
            'arguments' => [
                'unitId' => $unitId
            ]
        ]);
    }

    public function showAddEmployeeModal(int $unitId): void
    {
        $this->dispatch('open-modal', [
            'name' => 'add-employee',
            'arguments' => [
                'unitId' => $unitId
            ]
        ]);
    }

    public function showHeadcountModal(int $unitId): void
    {
        $this->dispatch('open-modal', [
            'name' => 'manage-headcount',
            'arguments' => [
                'unitId' => $unitId
            ]
        ]);
    }

    public function showDepartmentModal(int $unitId): void
    {
        $this->dispatch('open-modal', [
            'name' => 'manage-department',
            'arguments' => [
                'unitId' => $unitId
            ]
        ]);
    }

    public function showDeleteModal(int $unitId): void
    {
        $this->dispatch('open-modal', [
            'name' => 'delete-organization-unit',
            'arguments' => [
                'unitId' => $unitId
            ]
        ]);
    }
}
