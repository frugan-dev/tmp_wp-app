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

// These aliases bridge Mozart-prefixed PSR Log classes to standard PSR namespace

if (!interface_exists(\Psr\Log\LoggerInterface::class) && interface_exists(\WpApp\Dependencies\Psr\Log\LoggerInterface::class)) {
    class_alias(\WpApp\Dependencies\Psr\Log\LoggerInterface::class, \Psr\Log\LoggerInterface::class);
    class_alias(\WpApp\Dependencies\Psr\Log\LogLevel::class, \Psr\Log\LogLevel::class);
    class_alias(\WpApp\Dependencies\Psr\Log\InvalidArgumentException::class, \Psr\Log\InvalidArgumentException::class);
}
