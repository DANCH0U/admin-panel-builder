<?php

namespace App\AdminPanel\Notifications;

/**
 * Queue toast notifications into the session for Inertia shared data.
 *
 * Notify::success('Saved');
 * Notify::success('Saved')->action('View', admin_path('tests'));
 * Notify::danger('Failed')->title('Delete')->duration(8000);
 *
 * Legacy still works: return back()->with('success', 'Saved');
 */
class Notify
{
    protected string $type = 'info';

    protected string $message = '';

    protected ?string $title = null;

    protected ?array $action = null;

    protected ?int $duration = null;

    protected bool $queued = false;

    public function __construct(string $type, string $message)
    {
        $this->type = FlashBag::normalizeType($type);
        $this->message = $message;
    }

    public static function make(string $type, string $message): static
    {
        return new static($type, $message);
    }

    public static function success(string $message): static
    {
        return static::make('success', $message)->send();
    }

    public static function info(string $message): static
    {
        return static::make('info', $message)->send();
    }

    public static function warning(string $message): static
    {
        return static::make('warning', $message)->send();
    }

    public static function danger(string $message): static
    {
        return static::make('danger', $message)->send();
    }

    public static function error(string $message): static
    {
        return static::danger($message);
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this->send();
    }

    public function action(string $label, ?string $href = null): static
    {
        $this->action = array_filter([
            'label' => $label,
            'href' => $href,
        ], fn ($v) => $v !== null && $v !== '');

        return $this->send();
    }

    public function duration(int $ms): static
    {
        $this->duration = $ms;

        return $this->send();
    }

    public function send(): static
    {
        $bag = session()->get('notifications', []);
        $item = $this->toArray();

        if ($this->queued) {
            for ($i = count($bag) - 1; $i >= 0; $i--) {
                if (($bag[$i]['_uid'] ?? null) === ($item['_uid'] ?? null)) {
                    $bag[$i] = $item;
                    session()->put('notifications', $bag);
                    session()->flash('notifications', $bag);

                    return $this;
                }
            }
        }

        $bag[] = $item;
        session()->put('notifications', $bag);
        session()->flash('notifications', $bag);
        $this->queued = true;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            '_uid' => spl_object_id($this),
            'type' => $this->type,
            'message' => $this->message,
            'title' => $this->title,
            'action' => $this->action,
            'duration' => $this->duration,
        ], fn ($v) => $v !== null && $v !== []);
    }
}
