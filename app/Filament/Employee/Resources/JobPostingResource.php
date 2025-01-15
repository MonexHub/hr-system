<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\JobPostingResource\Pages;
use App\Models\JobPosting;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model; // Add this import

class JobPostingResource extends Resource
{
    protected static ?string $model = JobPosting::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 1;
    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()->hasRole('employee') ||
            auth()->user()->employee->employment_status !== 'terminated';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->where('closing_date', '>=', now())
                    ->orWhereNull('closing_date');
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->description(fn (JobPosting $record): string =>
                    "Department: {$record->department->name}")
                    ->wrap(),

                Tables\Columns\TextColumn::make('location')
                    ->icon('heroicon-m-map-pin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('employment_type')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_remote')
                    ->boolean()
                    ->label('Remote Available')
                    ->trueColor('success'),

                Tables\Columns\TextColumn::make('closing_date')
                    ->date()
                    ->label('Apply Before')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'internship' => 'Internship',
                    ]),

                Tables\Filters\TernaryFilter::make('is_remote')
                    ->label('Remote Work')
                    ->trueLabel('Remote Available')
                    ->falseLabel('On-site Only'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (JobPosting $record): string =>
                    route('filament.employee.resources.job-postings.view', ['record' => $record]))
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->label('View Details'),

                Tables\Actions\Action::make('apply')
                    ->url(fn (JobPosting $record): string =>
                    route('filament.employee.resources.job-postings.apply', ['record' => $record]))
                    ->icon('heroicon-m-paper-airplane')
                    ->button()
                    ->color('primary')
                    ->label('Apply Now')
                    ->visible(fn (JobPosting $record): bool =>
                    !$record->hasApplied(auth()->user()?->employee)),
            ])
            ->emptyStateHeading('No Open Positions')
            ->emptyStateDescription('Check back later for new career opportunities.')
            ->emptyStateIcon('heroicon-o-briefcase');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobPostings::route('/'),
            'view' => Pages\ViewJobPosting::route('/{record}'),
            'apply' => Pages\ApplyJobPosting::route('/{record}/apply'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool  // Fixed type-hint
    {
        return false;
    }

    public static function canDelete(Model $record): bool  // Fixed type-hint
    {
        return false;
    }
}
