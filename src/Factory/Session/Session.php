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

namespace WpApp\Factory\Session;

use WpApp\Factory\Session\Handler\TransientSessionHandler;
use WpApp\Factory\Session\Handler\WooCommerceSessionHandler;

if (!\defined('WPINC')) {
    exit;
}

class Session
{
    private TransientSessionHandler|WooCommerceSessionHandler|null $handler = null;

    public function create(): TransientSessionHandler|WooCommerceSessionHandler|null
    {
        if (null !== $this->handler) {
            return $this->handler;
        }

        // https://wp-kama.com/1588/wordpress-constants
        if (\defined('WP_CLI') && WP_CLI) {
            return null;
        }

        // Determined when an AJAX request is executed.
        /*if (\defined('DOING_AJAX') && DOING_AJAX) {
            return null;
        }*/

        // Determined when auto-saving a record.
        if (\defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return null;
        }

        // Determined if cron task (scheduled task) is executed.
        if (\defined('DOING_CRON') && DOING_CRON) {
            return null;
        }

        // Determined when an Atom Publishing Protocol request is made.
        if (\defined('APP_REQUEST') && APP_REQUEST) {
            return null;
        }

        // Determined if IFRAME request is executed.
        if (\defined('IFRAME_REQUEST') && IFRAME_REQUEST) {
            return null;
        }

        // Defined when performing a REST request.
        if (\defined('REST_REQUEST') && REST_REQUEST) {
            return null;
        }

        // Defined at any XML-RPC request.
        if (\defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            return null;
        }

        // if this class is initialized right away to use native PHP sessions (see `session_start()`),
        // it might be too early to use`function_exists('is_woocommerce')`
        if (wpapp_is_plugin_active('woocommerce/woocommerce.php')) {
            $this->handler = new WooCommerceSessionHandler();
        } else {
            $this->handler = new TransientSessionHandler();
        }

        return $this->handler;
    }

    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }
}
