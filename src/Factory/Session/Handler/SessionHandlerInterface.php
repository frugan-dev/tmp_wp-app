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

namespace WpApp\Factory\Session\Handler;

if (!\defined('WPINC')) {
    exit;
}

interface SessionHandlerInterface
{
    public function get(string $name, mixed $default = null): mixed;

    public function set(string $name, mixed $value, int $expiration = 0): void;

    public function has(string $name): bool;

    public function remove(string $name): void;
}
