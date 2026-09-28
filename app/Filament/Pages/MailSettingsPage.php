<?php

namespace App\Filament\Pages;

use App\Mail\TestMail;
use App\Models\SiteSetting;
use App\Support\MailSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class MailSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Email settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 15;

    protected static string $view = 'filament.pages.mail-settings';

    protected static ?string $title = 'Email settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'mail_mailer' => SiteSetting::get('mail_mailer', 'log'),
            'mail_from_address' => SiteSetting::get('mail_from_address', 'hello@example.com'),
            'mail_from_name' => SiteSetting::get('mail_from_name', config('app.name', 'Alternova')),
            'mail_admin_email' => SiteSetting::get('mail_admin_email', ''),
            'mail_alert_contact' => SiteSetting::getBool('mail_alert_contact', true),
            'mail_alert_submission' => SiteSetting::getBool('mail_alert_submission', true),
            'mail_contact_autoreply' => SiteSetting::getBool('mail_contact_autoreply', false),
            'mail_smtp_host' => SiteSetting::get('mail_smtp_host', ''),
            'mail_smtp_port' => SiteSetting::get('mail_smtp_port', '587'),
            'mail_smtp_username' => SiteSetting::get('mail_smtp_username', ''),
            'mail_smtp_password' => SiteSetting::get('mail_smtp_password', ''),
            'mail_smtp_encryption' => SiteSetting::get('mail_smtp_encryption', 'tls'),
            'mail_resend_key' => SiteSetting::get('mail_resend_key', ''),
            'test_to' => SiteSetting::get('mail_admin_email', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Delivery')
                    ->description('Choose how Alternova sends email. Use Log while testing, then switch to Resend or SMTP.')
                    ->schema([
                        Select::make('mail_mailer')
                            ->label('Mail driver')
                            ->options([
                                'log' => 'Log (writes to storage/logs — no real email)',
                                'resend' => 'Resend',
                                'smtp' => 'SMTP (custom server)',
                                'array' => 'Array (testing — keeps mail in memory)',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('mail_from_address')->label('From address')->email()->required(),
                        TextInput::make('mail_from_name')->label('From name')->required(),
                        TextInput::make('mail_admin_email')
                            ->label('Admin alert inbox')
                            ->email()
                            ->helperText('Receives contact form and submission alerts.')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Alerts')
                    ->schema([
                        Toggle::make('mail_alert_contact')
                            ->label('Email me on new contact messages')
                            ->inline(false),
                        Toggle::make('mail_alert_submission')
                            ->label('Email me on new alternative submissions')
                            ->inline(false),
                        Toggle::make('mail_contact_autoreply')
                            ->label('Send auto-reply to contact form submitters')
                            ->helperText('Visitor receives a short “we got your message” email.')
                            ->inline(false),
                    ])
                    ->columns(1),

                Section::make('Resend')
                    ->description('Create an API key at resend.com. Verify your domain, then paste the key below.')
                    ->visible(fn (Get $get) => $get('mail_mailer') === 'resend')
                    ->schema([
                        TextInput::make('mail_resend_key')
                            ->label('Resend API key')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),
                    ]),

                Section::make('SMTP')
                    ->description('Hostinger, Gmail app password, Mailgun SMTP, etc.')
                    ->visible(fn (Get $get) => $get('mail_mailer') === 'smtp')
                    ->schema([
                        TextInput::make('mail_smtp_host')->label('Host'),
                        TextInput::make('mail_smtp_port')->label('Port'),
                        TextInput::make('mail_smtp_username')->label('Username'),
                        TextInput::make('mail_smtp_password')->label('Password')->password()->revealable(),
                        Select::make('mail_smtp_encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS (port 587)',
                                'ssl' => 'SSL (port 465)',
                                '' => 'None',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Send test email')
                    ->schema([
                        TextInput::make('test_to')->label('Send test to')->email(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::setMany([
            'mail_mailer' => $state['mail_mailer'] ?? 'log',
            'mail_from_address' => trim((string) ($state['mail_from_address'] ?? '')),
            'mail_from_name' => trim((string) ($state['mail_from_name'] ?? '')),
            'mail_admin_email' => trim((string) ($state['mail_admin_email'] ?? '')),
            'mail_alert_contact' => ! empty($state['mail_alert_contact']),
            'mail_alert_submission' => ! empty($state['mail_alert_submission']),
            'mail_contact_autoreply' => ! empty($state['mail_contact_autoreply']),
            'mail_smtp_host' => trim((string) ($state['mail_smtp_host'] ?? '')),
            'mail_smtp_port' => trim((string) ($state['mail_smtp_port'] ?? '587')),
            'mail_smtp_username' => trim((string) ($state['mail_smtp_username'] ?? '')),
            'mail_smtp_password' => (string) ($state['mail_smtp_password'] ?? ''),
            'mail_smtp_encryption' => (string) ($state['mail_smtp_encryption'] ?? 'tls'),
            'mail_resend_key' => trim((string) ($state['mail_resend_key'] ?? '')),
        ]);

        MailSettings::apply();

        Notification::make()->title('Email settings saved')->success()->send();
    }

    public function sendTest(): void
    {
        $this->save();

        $to = trim((string) ($this->form->getState()['test_to'] ?? ''));
        if ($to === '') {
            Notification::make()->title('Enter a test recipient')->warning()->send();

            return;
        }

        try {
            MailSettings::apply();
            Mail::to($to)->send(new TestMail('If you received this, delivery is configured correctly.'));
            Notification::make()
                ->title('Test email sent')
                ->body('Check inbox (and spam) for '.$to.'. Driver: '.config('mail.default'))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Test email failed')->body($e->getMessage())->danger()->send();
        }
    }
}
