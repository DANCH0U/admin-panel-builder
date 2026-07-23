<?php

namespace App\AdminPanel\Engine\Search;

use App\AdminPanel\Engine\Contracts\SearchDriverContract;
use App\AdminPanel\Engine\Search\Drivers\ExactDriver;
use App\AdminPanel\Engine\Search\Drivers\FullTextDriver;
use App\AdminPanel\Engine\Search\Drivers\LikeDriver;
use App\AdminPanel\Engine\Search\Drivers\RelationDriver;

/**
 * Resolves the appropriate SearchDriver for a given strategy name.
 * New drivers can be registered without modifying this class.
 */
class SearchPipeline
{
    /** @var SearchDriverContract[] */
    private array $drivers;

    public function __construct(array $drivers = [])
    {
        // Default drivers — can be overridden or extended
        $this->drivers = empty($drivers) ? [
            new LikeDriver(),
            new ExactDriver(),
            new FullTextDriver(),
            new RelationDriver(),
        ] : $drivers;
    }

    public function register(SearchDriverContract $driver): void
    {
        $this->drivers[] = $driver;
    }

    public function resolveDriver(string $strategy): SearchDriverContract
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($strategy)) {
                return $driver;
            }
        }

        // Default fallback
        return new LikeDriver();
    }
}
