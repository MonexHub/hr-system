<?php

namespace App\Filament\Admin\Resources\ProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Employee Documents';
    protected static ?string $recordTitleAttribute = 'original_name';
    protected static ?int $navigationSort = 2;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->options([
                                'id_proof' => 'ID Proof',
                                'resume' => 'Resume/CV',
                                'certificate' => 'Certificate',
                                'other' => 'Other'
                            ])
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Document')
                            ->required()
                            ->directory('employee-documents')
                            ->preserveFilenames()
                            ->maxSize(5120)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) {
                                    $set('original_name', null);
                                    $set('mime_type', null);
                                    $set('file_size', null);
                                    return;
                                }

                                if ($state instanceof \Illuminate\Http\UploadedFile) {
                                    $set('original_name', $state->getClientOriginalName());
                                    $set('mime_type', $state->getMimeType());
                                    $set('file_size', $state->getSize());
                                } else {
                                    $path = $state;
                                    if (is_array($state)) {
                                        $path = $state[0];
                                    }
                                    $set('original_name', basename($path));
                                    $set('mime_type', Storage::disk('public')->mimeType($path));
                                    $set('file_size', Storage::disk('public')->size($path));
                                }
                            })
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/*',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                            ])
                            ->helperText('Accepted files: PDF, Images, Word documents. Max size: 5MB')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('original_name')
                            ->required(),

                        Forms\Components\Hidden::make('mime_type')
                            ->required(),

                        Forms\Components\Hidden::make('file_size')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText('Add any notes or description about this document'),
                    ])
                    ->columns(1)
                    ->collapsible()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->title()->replace('_', ' '))
                    ->color(fn (string $state): string => match ($state) {
                        'id_proof' => 'warning',
                        'resume' => 'success',
                        'certificate' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('original_name')
                    ->label('File Name')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        $limit = $column->getCharacterLimit(); // ✅ Correct method
                        if (strlen($state) <= $limit) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('formatted_file_size')
                    ->label('Size')
                    ->toggleable()
                    ->sortable('file_size'),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->multiple()
                    ->options([
                        'id_proof' => 'ID Proof',
                        'resume' => 'Resume/CV',
                        'certificate' => 'Certificate',
                        'other' => 'Other'
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function () {
                        Notification::make()
                            ->title('Document uploaded')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->label('Download')
                        ->url(fn ($record) => asset('storage/' . $record->file_path))
                        ->openUrlInNewTab()
                        ->color('success'),

                    Tables\Actions\EditAction::make()
                        ->after(function () {
                            Notification::make()
                                ->title('Document updated')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record) {
                            // Delete the physical file before deleting the record
                            if ($record->file_path && Storage::disk('public')->exists($record->file_path)) {
                                Storage::disk('public')->delete($record->file_path);
                            }
                        })
                        ->after(function () {
                            Notification::make()
                                ->title('Document deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Delete the physical files before deleting the records
                            foreach ($records as $record) {
                                if ($record->file_path && Storage::disk('public')->exists($record->file_path)) {
                                    Storage::disk('public')->delete($record->file_path);
                                }
                            }
                        })
                        ->after(function () {
                            Notification::make()
                                ->title('Documents deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-document')
            ->emptyStateHeading('No documents uploaded')
            ->emptyStateDescription('Upload your documents here.')
            ->paginationPageOptions([10, 25, 50]);
//            ->defaultPaginationPageSize(10);
    }
}
