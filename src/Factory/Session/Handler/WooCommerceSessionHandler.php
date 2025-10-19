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

class WooCommerceSessionHandler implements SessionHandlerInterface
{
    private $session;

    private array $expirations = [];

    public function __construct()
    {
        add_action('woocommerce_init', [$this, 'init']);
        add_action('shutdown', [$this, 'saveExpirations']);
        add_action('wp_logout', [$this, 'clearExpirations']);
    }

    public function init(): void
    {
        if ($this->session) {
            return;
        }

        // https://stackoverflow.com/a/53834418/3929620
        // https://stackoverflow.com/a/62549148/3929620
        // https://stackoverflow.com/a/56119479/3929620
        if (\function_exists('WC')) {
            if (!WC()->session) {
                wc()->initialize_session();
            }

            $this->session = WC()->session;
            $this->loadExpirations();
        }
    }

    public function get(string $name, mixed $default = null): mixed
    {
        $this->maybeInit();

        if ($this->session) {
            if (isset($this->expirations[$name]) && time() > $this->expirations[$name]) {
                $this->remove($name);

                return $default;
            }

            return $this->session->get($name, $default);
        }

        return $default;
    }

    public function set(string $name, $value, int $expiration = 86400): void
    {
        $this->maybeInit();

        if ($this->session) {
            $this->session->set($name, $value);
            if ($expiration > 0) {
                $this->expirations[$name] = time() + $expiration;
                // $this->saveExpirations();
            }
        }
    }

    public function has(string $name): bool
    {
        $this->maybeInit();

        if ($this->session) {
            if (isset($this->expirations[$name]) && time() > $this->expirations[$name]) {
                $this->remove($name);

                return false;
            }

            return null !== $this->session->get($name);
        }

        return false;
    }

    public function remove(string $name): void
    {
        $this->maybeInit();

        if ($this->session) {
            $this->session->set($name, null);
            if (isset($this->expirations[$name])) {
                unset($this->expirations[$name]);
            }
        }
    }

    public function saveExpirations(): void
    {
        if ($this->session) {
            $this->session->set('__session_expirations', $this->expirations);
        }
    }

    public function clearExpirations(): void
    {
        if ($this->session) {
            $this->expirations = [];
        }
    }

    private function maybeInit(): void
    {
        if (!$this->session) {
            $this->init();
        }
    }

    private function loadExpirations(): void
    {
        if ($this->session) {
            $this->expirations = $this->session->get('__session_expirations', []);
        }
    }
}
