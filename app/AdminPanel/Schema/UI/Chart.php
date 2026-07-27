<?php

namespace App\AdminPanel\Schema\UI;

use App\AdminPanel\Schema\Component;

/**
 * ApexCharts chart node for schema pages (dashboards, reports).
 *
 * Chart::make()
 *     ->type('area') // line | area | bar | column | pie | donut | radialBar
 *     ->height(320)
 *     ->series([['name' => 'Users', 'data' => [10, 20, 15]]])
 *     ->categories(['Mon', 'Tue', 'Wed'])
 *     ->label('Signups')
 *     ->border();
 *
 * Pie / donut use flat series + labels:
 * Chart::make()->type('donut')->series([12, 88])->labels(['A', 'B']);
 *
 * Or load series from an API (JSON: series, categories?, labels?):
 * Chart::make()->type('area')->api('/admin/api/signups');
 *
 * Notes:
 * - `bar` and `column` both use ApexCharts' bar engine:
 *   `bar` → horizontal, `column` → vertical (plotOptions.bar.horizontal = false).
 * - Prefer ->options() only for extras; the frontend always supplies safe plotOptions defaults.
 */
class Chart extends Component
{
    protected string $chartType = 'area';

    protected int $height = 320;

    /** @var list<array<string, mixed>>|list<int|float> */
    protected array $series = [];

    /** @var list<string> */
    protected array $categories = [];

    /** @var list<string> */
    protected array $labels = [];

    /** @var list<string> */
    protected array $colors = [];

    /** @var array<string, mixed> */
    protected array $options = [];

    protected ?string $api = null;

    protected bool $sparkline = false;

    protected bool $bordered = false;

    protected bool $toolbar = false;

    protected function getType(): string
    {
        return 'chart';
    }

    public function type(string $type): static
    {
        $this->chartType = $type;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = max(80, $height);

        return $this;
    }

    /**
     * @param  list<array<string, mixed>>|list<int|float>  $series
     */
    public function series(array $series): static
    {
        $this->series = $series;

        return $this;
    }

    /**
     * @param  list<string>  $categories
     */
    public function categories(array $categories): static
    {
        $this->categories = array_values($categories);

        return $this;
    }

    /**
     * Labels for pie / donut / radial charts.
     *
     * @param  list<string>  $labels
     */
    public function labels(array $labels): static
    {
        $this->labels = array_values($labels);

        return $this;
    }

    /**
     * @param  list<string>  $colors
     */
    public function colors(array $colors): static
    {
        $this->colors = array_values($colors);

        return $this;
    }

    /**
     * Merge raw ApexCharts options (deep-merged on the frontend).
     *
     * @param  array<string, mixed>  $options
     */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Fetch chart data from a JSON endpoint instead of (or in addition to) static series.
     *
     * Expected JSON shape:
     * { "series": [...], "categories"?: [...], "labels"?: [...], "colors"?: [...], "options"?: {...} }
     * Also accepts the same payload nested under a "data" key.
     */
    public function api(string $url): static
    {
        $this->api = $url;

        return $this;
    }

    public function sparkline(bool $sparkline = true): static
    {
        $this->sparkline = $sparkline;

        return $this;
    }

    public function toolbar(bool $toolbar = true): static
    {
        $this->toolbar = $toolbar;

        return $this;
    }

    public function border(bool $bordered = true): static
    {
        $this->bordered = $bordered;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'chartType' => $this->chartType,
            'height' => $this->height,
            'series' => $this->series,
            'categories' => $this->categories,
            'labels' => $this->labels,
            'colors' => $this->colors,
            'options' => $this->options,
            'api' => $this->api,
            'sparkline' => $this->sparkline,
            'bordered' => $this->bordered,
            'toolbar' => $this->toolbar,
        ]);
    }
}
