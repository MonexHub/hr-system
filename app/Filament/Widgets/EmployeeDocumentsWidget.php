<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeDocumentsWidget extends Widget
{
    protected static string $view = 'filament.widgets.employee-documents-widget';

//    protected static ?int $sort = 5;
    use HasWidgetShield;

    public function getDocuments()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee || !method_exists($employee, 'documents')) {
            return [
                'recent' => [],
                'count_by_type' => [],
                'expires_soon' => []
            ];
        }

        // Get recent documents
        $recentDocuments = $employee->documents()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get document count by type
        $documentsByType = $employee->documents()
            ->select('document_type', \DB::raw('count(*) as count'))
            ->groupBy('document_type')
            ->get()
            ->pluck('count', 'document_type')
            ->toArray();

        // Get documents expiring soon (if expiry_date field exists)
        $expiringSoon = [];
        if (in_array('expiry_date', $employee->documents()->getModel()->getFillable())) {
            $expiringSoon = $employee->documents()
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '>=', now())
                ->where('expiry_date', '<=', now()->addDays(90))
                ->orderBy('expiry_date', 'asc')
                ->limit(3)
                ->get();
        }

        // Format the recent documents for display
        $formattedRecent = $recentDocuments->map(function($document) {
            return [
                'id' => $document->id,
                'title' => $document->title ?? 'Untitled Document',
                'filename' => $document->filename ?? null,
                'document_type' => $document->document_type ?? 'Other',
                'file_path' => $document->file_path,
                'file_size' => $this->formatBytes($document->file_size ?? 0),
                'file_extension' => $this->getFileExtension($document->filename ?? ''),
                'created_at' => $document->created_at->diffForHumans(),
                'created_at_formatted' => $document->created_at->format('M d, Y'),
            ];
        });

        // Format expiring documents
        $formattedExpiring = collect($expiringSoon)->map(function($document) {
            return [
                'id' => $document->id,
                'title' => $document->title ?? 'Untitled Document',
                'document_type' => $document->document_type ?? 'Other',
                'expiry_date' => $document->expiry_date->format('M d, Y'),
                'days_remaining' => now()->diffInDays($document->expiry_date, false),
                'file_path' => $document->file_path,
            ];
        });

        return [
            'recent' => $formattedRecent,
            'count_by_type' => $documentsByType,
            'expires_soon' => $formattedExpiring,
            'total_count' => $employee->documents()->count(),
        ];
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function getFileExtension($filename)
    {
        return strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
    }

    protected function getFileIcon($extension)
    {
        $icons = [
            'PDF' => 'fas fa-file-pdf',
            'DOC' => 'fas fa-file-word',
            'DOCX' => 'fas fa-file-word',
            'XLS' => 'fas fa-file-excel',
            'XLSX' => 'fas fa-file-excel',
            'PPT' => 'fas fa-file-powerpoint',
            'PPTX' => 'fas fa-file-powerpoint',
            'JPG' => 'fas fa-file-image',
            'JPEG' => 'fas fa-file-image',
            'PNG' => 'fas fa-file-image',
            'GIF' => 'fas fa-file-image',
            'TXT' => 'fas fa-file-alt',
            'ZIP' => 'fas fa-file-archive',
            'RAR' => 'fas fa-file-archive',
        ];

        return $icons[$extension] ?? 'fas fa-file';
    }
}
