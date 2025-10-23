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

namespace WpApp\Factory;

use DI\Container;
use WpApp\Vendor\Inpsyde\Wonolog\Channels;
use WpApp\Vendor\Inpsyde\Wonolog\Configurator;
use WpApp\Vendor\Inpsyde\Wonolog\DefaultHandler\LogsFolder;
use WpApp\Vendor\Inpsyde\Wonolog\HookListener\HttpApiListener;
use WpApp\Vendor\Inpsyde\Wonolog\LogLevel;
use WpApp\Vendor\Monolog\Formatter\HtmlFormatter;
use WpApp\Vendor\Monolog\Formatter\LineFormatter;
use WpApp\Vendor\Monolog\Handler\DeduplicationHandler;
use WpApp\Vendor\Monolog\Handler\ErrorLogHandler;
use WpApp\Vendor\Monolog\Handler\NativeMailerHandler;
use WpApp\Vendor\Monolog\Handler\RotatingFileHandler;
use WpApp\Vendor\Monolog\Processor\PsrLogMessageProcessor;
use WpSpaghetti\WpEnv\Environment;

if (!\defined('WPINC')) {
    exit;
}

class Wonolog
{
    public function __construct(private Container $container)
    {
        add_action(Configurator::ACTION_SETUP, [$this, 'setup']);
    }

    public function setup(Configurator $configurator): void
    {
        if (Environment::isDebug()) {
            $defaultHandler = new ErrorLogHandler(ErrorLogHandler::SAPI, LogLevel::defaultMinLevel());
        } else {
            $defaultHandler = new RotatingFileHandler(LogsFolder::determineFolder().'app.log', 10, LogLevel::defaultMinLevel(), true, 0777);
        }

        // The last "true" here tells monolog to remove empty []'s
        $defaultHandler->setFormatter(new LineFormatter(null, null, false, true));

        $emailHandler = new NativeMailerHandler(
            explode(',', Environment::get('EMAIL_DEBUG_TO')),
            \sprintf(__('Error reporting from %1$s - %2$s', WPAPP_TEXTDOMAIN), $this->container->get('http_host'), Environment::get('WP_ENV')),
            Environment::get('EMAIL_DEBUG_FROM'),
            LogLevel::ERROR
        );
        $emailHandler->setContentType('text/html');
        $emailHandler->setFormatter(new HtmlFormatter());

        if (Environment::isDebug()) {
            $configurator->logSilencedPhpErrors();

            if (\is_string(WP_DEBUG_LOG) || WP_DEBUG_LOG) {
                $errorTypes = E_ALL & ~E_WARNING & ~E_NOTICE & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED;
            }
        } else {
            $emailHandler = new DeduplicationHandler($emailHandler, \sprintf(LogsFolder::determineFolder().'dedup-%s.log', Environment::get('WP_ENV')), LogLevel::ERROR, 86400);

            $errorTypes = E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED;

            // https://maximivanov.github.io/php-error-reporting-calculator/
            // https://kau-boys.com/2619/wordpress/set-the-debug-level-using-error_reporting
            // https://discourse.roots.io/t/bedrock-cant-disable-php-notices-warnings/20511
            error_reporting($errorTypes);
        }

        if (!empty($errorTypes)) {
            $configurator->logPhpErrorsTypes($errorTypes);
        }

        $configurator->disableFallbackHandler()
            // Disable default HttpApiListener (logs at ERROR level)
            // See: https://github.com/inpsyde/Wonolog/issues/83
            ->disableDefaultHookListeners(
                HttpApiListener::class
            )
            // Add custom HttpApiListener with WARNING level instead of ERROR
            // Since HttpApiListener is final, we need to create a new instance
            // with the desired log level in the constructor
            ->addActionListener(
                $this->container->get(HttpApiListener::class)
            )
            ->pushHandler($defaultHandler)
            ->pushHandler($emailHandler)
            // for placeholder substitution
            ->pushProcessor('psr-log-message-processor', new PsrLogMessageProcessor())
            ->pushProcessor('extra-processor', function (array $record): array {
                $record['extra']['hostname'] = @\WpApp\Vendor\Safe\gethostname(); // php_uname('n')
                $record['extra']['hostbyaddr'] = @gethostbyaddr(wpapp_get_client_ip());

                if ($this->container->has('session')) {
                    $session = $this->container->get('session');
                    if (!empty($session) && $session->has('referer')) {
                        if (!empty($referer = $session->get('referer'))) {
                            // use 'session_referer' to distinguish it from the HTTP referer that some handlers might already log
                            $record['extra']['session_referer'] = $referer;
                        }
                    }
                }

                $record['extra']['_REQUEST'] = $_REQUEST;
                $record['extra']['_POST'] = $_POST;
                $record['extra']['_FILES'] = $_FILES;
                $record['extra']['_SERVER'] = str_contains(\ini_get('variables_order'), 'E') ? $_SERVER : array_diff_key($_SERVER, $_ENV);
                $record['extra']['_SESSION'] = $_SESSION ?? null;

                return $record;
            })
        ;

        // Apply dynamic ignore patterns
        $this->applyIgnorePatterns($configurator);
    }

    /**
     * Apply ignore patterns from .env configuration or use defaults.
     */
    private function applyIgnorePatterns(Configurator $configurator): void
    {
        $ignorePatterns = $this->getIgnorePatternsFromEnv();

        foreach ($ignorePatterns as $ignorePattern) {
            $configurator->withIgnorePattern(
                $ignorePattern['pattern'],
                $ignorePattern['level'],
                $ignorePattern['channel']
            );
        }
    }

    /**
     * Get ignore patterns from environment variables with fallback to defaults.
     */
    private function getIgnorePatternsFromEnv(): array
    {
        // 1. Check if we want to replace all patterns (highest priority)
        $replacePatterns = Environment::get('WPAPP_WONOLOG_IGNORE_PATTERNS');
        if (!empty($replacePatterns)) {
            $decoded = json_decode($replacePatterns, true);
            if (JSON_ERROR_NONE === json_last_error() && \is_array($decoded)) {
                $patterns = $this->validatePatterns($decoded);

                // Apply WordPress filter to allow modification
                return apply_filters('wpapp_logger_ignore_patterns', $patterns);
            }
        }

        // 2. Check if we want to add patterns to defaults
        $additionalPatterns = Environment::get('WPAPP_WONOLOG_IGNORE_PATTERNS_ADDITIONAL');
        if (!empty($additionalPatterns)) {
            $decoded = json_decode($additionalPatterns, true);
            if (JSON_ERROR_NONE === json_last_error() && \is_array($decoded)) {
                $defaultPatterns = $this->getDefaultIgnorePatterns();
                $extraPatterns = $this->validatePatterns($decoded);
                $patterns = array_merge($defaultPatterns, $extraPatterns);

                // Apply WordPress filter to allow modification
                return apply_filters('wpapp_logger_ignore_patterns', $patterns);
            }
        }

        // 3. Use defaults and apply WordPress filter
        $defaultPatterns = $this->getDefaultIgnorePatterns();
        $filterPatterns = apply_filters('wpapp_logger_ignore_patterns', $defaultPatterns);

        // If filter modified the patterns, validate and return them
        if ($filterPatterns !== $defaultPatterns && \is_array($filterPatterns)) {
            return $this->validatePatterns($filterPatterns);
        }

        // 4. Fallback to defaults
        return $defaultPatterns;
    }

    /**
     * Get default ignore patterns (fallback).
     */
    private function getDefaultIgnorePatterns(): array
    {
        return [
            [
                'pattern' => "^Can't DROP '.+'; check that column/key exists$",
                'level' => null,
                'channel' => Channels::DB,
            ],
            [
                'pattern' => '^Deadlock found when trying to get lock; try restarting transaction$',
                'level' => null,
                'channel' => Channels::DB,
            ],
            [
                // https://wordpress.org/support/topic/database-error-duplicate-entry-lastnotificationid-for-key-primary/
                'pattern' => "^Duplicate entry '.+' for key",
                'level' => null,
                'channel' => Channels::DB,
            ],
            [
                'pattern' => "^Table '.+' doesn't exist$",
                'level' => null,
                'channel' => Channels::DB,
            ],
        ];
    }

    /**
     * Validate and normalize pattern configuration.
     */
    private function validatePatterns(array $patterns): array
    {
        $validated = [];

        foreach ($patterns as $pattern) {
            if (!\is_array($pattern)) {
                continue;
            }

            if (empty($pattern['pattern'])) {
                continue;
            }

            // Validate regex pattern
            if (false === @preg_match('/'.$pattern['pattern'].'/', '')) {
                error_log('WpApp Logger: Invalid regex pattern: '.$pattern['pattern']);

                continue;
            }

            $validated[] = [
                'pattern' => $pattern['pattern'],
                'level' => $pattern['level'] ?? null,
                'channel' => $this->convertChannelStringToConstant($pattern['channel'] ?? null),
            ];
        }

        return $validated;
    }

    /**
     * Convert channel string to Channels constant dynamically.
     */
    private function convertChannelStringToConstant(?string $channel): ?string
    {
        if (empty($channel)) {
            return null;
        }

        // Build the full constant name
        $constantName = Channels::class.'::'.strtoupper($channel);

        // Check if the constant exists and get its value
        if (\defined($constantName)) {
            return \constant($constantName);
        }

        // If constant doesn't exist, log warning and return null (applies to all channels)
        error_log(\sprintf("WpApp Logger: Unknown channel '%s'. Available channels can be found in Wonolog\\Channels class.", $channel));

        return null;
    }
}
