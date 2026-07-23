<?php

namespace App\AdminPanel\Pages;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\Button;
use App\AdminPanel\Schema\Card;
use App\AdminPanel\Schema\Flex;
use App\AdminPanel\Schema\Form;
use App\AdminPanel\Schema\Text;
use App\AdminPanel\Schema\TextInput;
use App\AdminPanel\Schema\Toggle;
use App\Models\PanelSetting;

class SettingsPage extends BasePage
{
    public function title(): ?string
    {
        return 'Panel settings';
    }

    public function schema(): array
    {
        return [
            Form::make()
                ->action(admin_path('settings'))
                ->method('POST')
                ->schema([
                    Card::make()->border()
                        ->label('Branding')
                        ->helpText('Controls the sidebar logo and navbar title for this panel.')
                        ->schema([
                            TextInput::make('app_name')
                                ->label('App name')
                                ->required()
                                ->helpText('Shown next to the logo and in the browser title context.'),
                            TextInput::make('navbar_title')
                                ->label('Navbar title')
                                ->placeholder('Optional short title for the top bar'),
                            TextInput::make('logo_url')
                                ->label('Logo URL')
                                ->placeholder('/storage/logo.png or https://…')
                                ->helpText('Path or absolute URL. Leave empty to use text-only branding.'),
                            Toggle::make('show_theme_toggle')
                                ->label('Show theme toggle'),
                            Text::make('Tip: put a PNG/SVG in public/ and use /your-logo.svg')
                                ->variant('caption'),
                        ]),
                    Flex::make()->justify('end')->schema([
                        Button::make('Save settings')->submit(),
                    ]),
                ]),
        ];
    }

    public function initialData(): array
    {
        $settings = PanelSetting::forPanel();

        return [
            'app_name' => $settings->app_name,
            'navbar_title' => $settings->navbar_title ?? '',
            'logo_url' => $settings->logo_url ?? '',
            'show_theme_toggle' => (bool) $settings->show_theme_toggle,
        ];
    }
}
