<?php

namespace App\AdminPanel\Pages;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\Button;
use App\AdminPanel\Schema\Flex;
use App\AdminPanel\Schema\Form;
use App\AdminPanel\Schema\Grid;
use App\AdminPanel\Schema\Heading;
use App\AdminPanel\Schema\Select;
use App\AdminPanel\Schema\Text;
use App\AdminPanel\Schema\TextInput;
use App\AdminPanel\Schema\Toggle;

class DemoPageBuilder extends BasePage
{
    public function title(): ?string
    {
        return 'Page Builder Demo';
    }

    public function schema(): array
    {
        return [
            // Form is borderless by default. Add ->border() for a card surface.
            Form::make()
                ->action(admin_path('demo/save'))
                ->method('POST')
                ->schema([
                    Heading::make('Profile settings')->level(3),
                    Text::make('Built with App\\AdminPanel\\Schema + SchemaRenderer.')
                        ->variant('subdued'),
                    Grid::make(2)->schema([
                        TextInput::make('first_name')->label('First name')->required(),
                        TextInput::make('last_name')->label('Last name'),
                    ]),
                    TextInput::make('email')
                        ->label('Email')
                        ->props(['type' => 'email']),
                    Select::make('theme')
                        ->label('Theme')
                        ->options([
                            'light' => 'Light',
                            'dark' => 'Dark',
                            'system' => 'System',
                        ]),
                    Toggle::make('newsletter')->label('Subscribe to updates'),
                    Flex::make()->justify('start')->schema([
                        Button::make('Save configuration')->submit(),
                    ]),
                ]),
        ];
    }
}
