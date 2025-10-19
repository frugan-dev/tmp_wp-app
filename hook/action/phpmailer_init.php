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

if (!defined('WPINC')) {
    exit;
}

return [
    [
        // https://mailtrap.io/blog/phpmailer/#PHPMailer-debugging
        // https://wordpress.stackexchange.com/a/423850/99214
        'callback' => static function (Container $container, $phpmailer): void {
            if (str_contains(ini_get('sendmail_path'), '--read-envelope-from')) {
                // Prevent errors "msmtp: cannot use both --from and --read-envelope-from"
                // and "Could not instantiate mail function"
                $phpmailer->Sender = '';
            }
        },
        'priority' => 999,
    ],
];
