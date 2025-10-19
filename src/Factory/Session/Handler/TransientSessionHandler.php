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

class TransientSessionHandler implements SessionHandlerInterface
{
    private string $prefix;

    public function __construct()
    {
        add_action('init', [$this, 'setPrefix'], 0);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (($value = get_transient($this->prefix.$name)) !== false) {
            return $value;
        }

        return $default;
    }

    public function set(string $name, $value, int $expiration = 86400): void
    {
        set_transient($this->prefix.$name, $value, $expiration);
    }

    public function has(string $name): bool
    {
        return false !== get_transient($this->prefix.$name);
    }

    public function remove(string $name): void
    {
        delete_transient($this->prefix.$name);
    }

    public function setPrefix(): void
    {
        $this->prefix = !empty($value = wpapp_get_client_ip()) ? $value : 'unknown_ip';
        $this->prefix .= $_SERVER['HTTP_USER_AGENT'] ?? 'unknown_user_agent';

        if (is_user_logged_in()) {
            $this->prefix .= (string) get_current_user_id();
        }

        $this->prefix = 'sess_'.md5($this->prefix);
    }
}
