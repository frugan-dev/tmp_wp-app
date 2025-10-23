<?php

declare(strict_types=1);

/*
 * This file is part of the Wp-App WordPress plugin.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 or later license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpApp;

use DI\Container;
use WpApp\Factory\Wonolog;

if (!\defined('WPINC')) {
    exit;
}

class WpApp
{
    private array $actions = [];

    private array $filters = [];

    private array $map = [];

    /**
     * Classes that implement BootableInterface.
     */
    private array $bootableClasses = [
        Wonolog::class,
    ];

    /**
     * Note on managing dynamic dependencies.
     * Instead of using Container::set(), consider these alternatives:
     *
     * 1. Factory functions in container.php
     * 2. Lazy loading with Closure
     * 3. Configurable services (create a service that can be configured post-creation)
     * 4. Registry pattern for dynamic values
     * 5. Provider classes for creating dynamic objects
     * 6. Extended container wrapper (if set()-like functionality is absolutely necessary)
     *
     * These approaches maintain better separation of concerns, improve testability,
     * and adhere more closely to dependency injection principles.
     */
    public function __construct(private Container $container) {}

    public function run(): void
    {
        // Initialize all bootable services, bypassing DI lazy loading
        $this->bootClasses();

        // Initialize other essential services
        $this->container->get('session');

        $this->map = require WPAPP_PATH.'hook/map.php';
        $this->loadHooks(WPAPP_PATH.'hook/action', 'action');
        $this->loadHooks(WPAPP_PATH.'hook/filter', 'filter');
        $this->registerHooks();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Boot all classes that implement BootableInterface.
     */
    private function bootClasses(): void
    {
        foreach ($this->bootableClasses as $bootableClass) {
            if ($this->container->has($bootableClass)) {
                $this->container->get($bootableClass);
            }
        }
    }

    private function loadHooks(string $directory, string $type): void
    {
        foreach (\Safe\glob($directory.'/*.php') as $filename) {
            $name = basename($filename, '.php');
            $hookName = $this->map[$name] ?? $name;
            $result = require $filename;

            if ('action' === $type) {
                $target = &$this->actions;
            } else {
                $target = &$this->filters;
            }

            if (!isset($target[$hookName])) {
                $target[$hookName] = [];
            }

            if (\is_array($result)) {
                $target[$hookName] = array_merge($target[$hookName], $result);
            } elseif (\is_callable($result)) {
                $target[$hookName][] = ['callback' => $result];
            }
        }
    }

    private function registerHooks(): void
    {
        $this->processHooks($this->actions, 'action');
        $this->processHooks($this->filters, 'filter');
    }

    private function processHooks(array $hooks, string $type): void
    {
        foreach ($hooks as $hook => $items) {
            foreach ($items as $item) {
                $originalCallback = $item['callback'];
                $priority = $item['priority'] ?? (int) 10;
                $accepted_args = $item['accepted_args'] ?? 1;
                $remove = $item['remove'] ?? false;

                if (\is_string($originalCallback) || $remove) {
                    $callback = $originalCallback;
                } else {
                    $callback = fn (...$args) => $originalCallback($this->container, ...$args);
                }

                if ($remove) {
                    'action' === $type
                        ? remove_action($hook, $callback, $priority)
                        : remove_filter($hook, $callback, $priority);
                } else {
                    'action' === $type
                        ? add_action($hook, $callback, $priority, $accepted_args)
                        : add_filter($hook, $callback, $priority, $accepted_args);
                }
            }
        }
    }
}
