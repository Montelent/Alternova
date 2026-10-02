<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageUsers() ?? false;
    }

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('role')
                ->options([
                    User::ROLE_ADMIN => 'Admin — full access (settings, team, system)',
                    User::ROLE_EDITOR => 'Editor — content only (tools & alternatives)',
                    User::ROLE_MEMBER => 'Member — frontend account only (no admin panel)',
                ])
                ->required()
                ->default(User::ROLE_EDITOR)
                ->helperText('Admins manage the whole site. Editors manage catalog content. Members can only use the public site.'),
            Forms\Components\TextInput::make('password')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create')
                ->helperText('Leave blank when editing to keep the current password.'),
        ];

        if (Schema::hasColumn('users', 'is_active')) {
            $schema[] = Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->helperText('Inactive users cannot sign in to the admin panel.');
        }

        return $form->schema([
            Forms\Components\Section::make('Account')->schema($schema)->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('role')->badge()
                ->color(fn (string $state) => match ($state) {
                    User::ROLE_ADMIN => 'danger',
                    User::ROLE_EDITOR => 'info',
                    User::ROLE_MEMBER => 'gray',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ];

        if (Schema::hasColumn('users', 'is_active')) {
            array_splice($columns, 3, 0, [
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ]);
        }

        return $table
            ->columns($columns)
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => Schema::hasColumn('users', 'is_active')
                        && ! $record->isActive()
                        && $record->id !== auth()->id())
                    ->action(function (User $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()->title('User activated')->success()->send();
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => Schema::hasColumn('users', 'is_active')
                        && $record->isActive()
                        && $record->id !== auth()->id())
                    ->action(function (User $record) {
                        $record->update(['is_active' => false]);
                        Notification::make()->title('User deactivated')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn () => Schema::hasColumn('users', 'is_active'))
                        ->action(function ($records) {
                            $records->each(fn (User $u) => $u->id !== auth()->id() && $u->update(['is_active' => true]));
                            Notification::make()->title('Users activated')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => Schema::hasColumn('users', 'is_active'))
                        ->action(function ($records) {
                            $records->each(fn (User $u) => $u->id !== auth()->id() && $u->update(['is_active' => false]));
                            Notification::make()->title('Users deactivated')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records->each(function (User $u) {
                                if ($u->id !== auth()->id()) {
                                    $u->delete();
                                }
                            });
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
