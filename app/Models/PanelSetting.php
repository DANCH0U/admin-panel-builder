<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelSetting extends Model
{
    protected $fillable = [
        'panel',
        'app_name',
        'logo_url',
        'navbar_title',
        'show_theme_toggle',
    ];

    protected function casts(): array
    {
        return [
            'show_theme_toggle' => 'boolean',
        ];
    }

    public static function forPanel(?string $panel = null): self
    {
        $panel ??= admin_panel();
        $config = admin_panel_config($panel);

        return static::query()->firstOrCreate(
            ['panel' => $panel],
            [
                'app_name' => $config['name'] ?? config('admin.name', 'Admin Panel'),
                'logo_url' => $config['ui']['logo_url'] ?? null,
                'navbar_title' => $config['ui']['navbar_title'] ?? null,
                'show_theme_toggle' => (bool) ($config['ui']['show_theme_toggle'] ?? true),
            ],
        );
    }

    /**
     * @return array{app_name: ?string, logo_url: ?string, navbar_title: ?string, show_theme_toggle: bool}
     */
    public function toShared(): array
    {
        return [
            'app_name' => $this->app_name,
            'logo_url' => $this->logo_url,
            'navbar_title' => $this->navbar_title,
            'show_theme_toggle' => (bool) $this->show_theme_toggle,
        ];
    }
}
