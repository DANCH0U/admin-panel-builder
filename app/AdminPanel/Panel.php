<?php

namespace App\AdminPanel;

class Panel
{
    protected string $id;

    protected string $prefix = '';

    /** @var list<string> */
    protected array $middleware = [];

    protected string $name = 'Admin Panel';

    protected ?string $logo = null;

    protected ?string $navbarTitle = null;

    protected bool $showThemeToggle = true;

    protected ?string $menu = null;

    protected ?string $loginRoute = 'login';

    protected ?string $home = null;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->prefix = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = trim($prefix, '/');

        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix !== '' ? $this->prefix : $this->id;
    }

    /**
     * @param  list<string>  $middleware
     */
    public function middleware(array $middleware): static
    {
        $this->middleware = array_values($middleware);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function logo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function navbarTitle(?string $title): static
    {
        $this->navbarTitle = $title;

        return $this;
    }

    public function getNavbarTitle(): ?string
    {
        return $this->navbarTitle ?? $this->name;
    }

    public function showThemeToggle(bool $show = true): static
    {
        $this->showThemeToggle = $show;

        return $this;
    }

    public function getShowThemeToggle(): bool
    {
        return $this->showThemeToggle;
    }

    /**
     * @param  class-string  $menuClass
     */
    public function menu(string $menuClass): static
    {
        $this->menu = $menuClass;

        return $this;
    }

    public function getMenu(): ?string
    {
        return $this->menu;
    }

    public function loginRoute(?string $route): static
    {
        $this->loginRoute = $route;

        return $this;
    }

    public function getLoginRoute(): ?string
    {
        return $this->loginRoute;
    }

    public function home(?string $home): static
    {
        $this->home = $home;

        return $this;
    }

    public function getHome(): ?string
    {
        return $this->home;
    }

    /**
     * Array shape used by helpers that previously read config('admin.panels.*').
     *
     * @return array<string, mixed>
     */
    public function toConfig(): array
    {
        return [
            'name' => $this->getName(),
            'prefix' => $this->getPrefix(),
            'middleware' => $this->getMiddleware(),
            'menu' => $this->getMenu(),
            'auth' => [
                'login_route' => $this->getLoginRoute(),
                'home' => $this->getHome(),
            ],
            'ui' => [
                'logo_url' => $this->getLogo(),
                'navbar_title' => $this->getNavbarTitle(),
                'show_theme_toggle' => $this->getShowThemeToggle(),
            ],
        ];
    }
}
