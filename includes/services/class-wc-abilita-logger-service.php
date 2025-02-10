<?php declare(strict_types=1);

namespace abilita\payment\services;

use abilita\payment\helper\WC_Abilita_Helper;

defined('ABSPATH') || exit;

class WC_Abilita_Logger_Service
{
    private $abilitaHelper;
    private $debug;

    public function __construct()
    {
        $this->abilitaHelper = new WC_Abilita_Helper();
        $this->debug = get_option('ABILITA_PAYMENT_DEBUG_LOGGER');
    }

    public function log($type, $class, $function, $params)
    {
		global $wp_filesystem;

        if (!$this->debug) {
            return;
        }

        $class = $this->abilitaHelper->get_clean_class_name($class);

        $datetime = new \DateTime();
        $logFile  = WP_CONTENT_DIR.'/uploads/wc-logs/'.$class.'-'.$function.'-'.gmdate('Y-m-d_H').'.log';
        $logTime  = $datetime->format(\DateTime::ATOM);
        $message  = $logTime.' '.$type.' '. $this->getMessage($class, $function, $params);

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();

        if (!$wp_filesystem->exists($logFile)) {
            $wp_filesystem->put_contents($logFile, $message, FS_CHMOD_FILE);
        } else {
            $existing_content = $wp_filesystem->get_contents($logFile);
            $new_content = $existing_content.$message;
            $wp_filesystem->put_contents($logFile, $new_content, FS_CHMOD_FILE);
        }
    }

    private function getMessage($class, $function, $params)
    {
		// $params comes over external API without NONCE
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        return
            $class . '::' .
            $function . PHP_EOL .
            print_r($params, true);
    }
}