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
                ->label('Account active')
                ->default(true)
                ->helperText('Off = user cannot sign in (admin or frontend).');
        }

        if (Schema::hasColumn('users', 'api_enabled')) {
            $schema[] = Forms\Components\Toggle::make('api_enabled')
                ->label('Allow API access')
                ->default(true)
                ->helperText('Off = this user’s API keys are rejected even if not revoked. Global API must also be on (System → API access).');
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
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->label('Updated')->toggleable(),
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

        if (Schema::hasColumn('users', 'api_enabled')) {
            $columns[] = Tables\Columns\IconColumn::make('api_enabled')
                ->label('API')
                ->boolean()
                ->sortable();
        }

        return $table
            ->columns($columns)
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('enableApi')
                    ->label('Enable API')
                    ->icon('heroicon-o-signal')
                    ->color('success')
                    ->visible(fn (User $record) => Schema::hasColumn('users', 'api_enabled')
                        && ! ($record->api_enabled ?? true))
                    ->action(function (User $record) {
                        $record->forceFill(['api_enabled' => true])->save();
                        Notification::make()->title('API enabled for '.$record->name)->success()->send();
                    }),
                Tables\Actions\Action::make('disableApi')
                    ->label('Disable API')
                    ->icon('heroicon-o-signal-slash')
                    ->color('warning')
                    ->visible(fn (User $record) => Schema::hasColumn('users', 'api_enabled')
                        && ($record->api_enabled ?? true))
                    ->action(function (User $record) {
                        $record->forceFill(['api_enabled' => false])->save();
                        Notification::make()->title('API disabled for '.$record->name)->success()->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
