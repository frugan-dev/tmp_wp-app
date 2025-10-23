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

use DI\Container;
use WpApp\Vendor\Inpsyde\Wonolog\Channels;
use WpSpaghetti\WpLogger\Logger;

if (!defined('WPINC')) {
    exit;
}

return [
    [
        'callback' => static function (Container $container, string $username, WP_Error $wpError): void {
            // Skip logging if security plugins are already handling this
            $plugins = [
                'wordfence/wordfence.php',
                'better-wp-security/better-wp-security.php', // iThemes Security
                'all-in-one-wp-security-and-firewall/wp-security.php', // AIOWPS
                'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
                'loginizer/loginizer.php',
            ];

            foreach ($plugins as $plugin) {
                if (wpapp_is_plugin_active($plugin)) {
                    return; // Exit early if any security plugin is active
                }
            }

            $context = [
                'channel' => Channels::SECURITY,
                'username' => $username,
                'error_code' => $wpError->get_error_code(),
                'error_message' => $wpError->get_error_message(),
            ];

            // Check if user exists to determine the type of failed login
            $user = get_user_by('login', $username);
            if ($user) {
                $context['user_id'] = $user->ID;
                $context['user_email'] = $user->user_email;
                $context['attempt_type'] = 'wrong_password';
            } else {
                $context['attempt_type'] = 'non_existent_user';
            }

            $container->get(Logger::class)->warning(
                'Failed login attempt for username "{username}": {error_message}',
                $context
            );
        },
        'accepted_args' => 2,
    ],
];
