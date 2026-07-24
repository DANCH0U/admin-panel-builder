<?php

namespace App\AdminPanel\Pages;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\Button;
use App\AdminPanel\Schema\Card;
use App\AdminPanel\Schema\Flex;
use App\AdminPanel\Schema\Form;
use App\AdminPanel\Schema\Grid;
use App\AdminPanel\Schema\Text;
use App\AdminPanel\Schema\TextInput;
use App\Models\User;

class ProfilePage extends BasePage
{
    public function __construct(protected User $user) {}

    public function title(): ?string
    {
        return 'Profile';
    }

    public function authorize(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            Form::make()
                ->action(admin_path('profile'))
                ->method('POST')
                ->schema([
                    Card::make()->border()
                        ->label('Account')
                        ->helpText('Update your name, email, and password.')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required(),
                            ]),
                            Text::make('Leave password blank to keep your current password.')
                                ->variant('caption'),
                            Grid::make(2)->schema([
                                TextInput::make('password')
                                    ->label('New password')
                                    ->password()
                                    ->placeholder('••••••••'),
                                TextInput::make('password_confirmation')
                                    ->label('Confirm password')
                                    ->password()
                                    ->placeholder('••••••••'),
                            ]),
                        ]),
                    Flex::make()->justify('end')->schema([
                        Button::make('Save profile')->submit(),
                    ]),
                ]),

            Form::make()
                ->action(admin_path('profile'))
                ->method('DELETE')
                ->schema([
                    Card::make()->border()
                        ->label('Delete account')
                        ->helpText('Permanently remove your account and all associated data. This cannot be undone.')
                        ->schema([
                            Text::make('Enter your current password to confirm account deletion.')
                                ->variant('subdued'),
                            TextInput::make('password')
                                ->label('Current password')
                                ->password()
                                ->required()
                                ->placeholder('••••••••'),
                            Flex::make()->justify('end')->schema([
                                Button::make('Delete account')
                                    ->variant('destructive')
                                    ->submit(),
                            ]),
                        ]),
                ]),
        ];
    }

    public function initialData(): array
    {
        return [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => '',
            'password_confirmation' => '',
        ];
    }
}
