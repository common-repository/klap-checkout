<?php

// carga las dependencias de composer (log4php)
require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

// carga el sdk de Klap Checkout
require_once(plugin_dir_path(__FILE__) . '../sdk/src/init.php');

use \Multicaja\Payments\Utils\BasicLog;
use \Multicaja\Payments\Model\Error;

/**
 * Clase para escribir logs
 *
 * docs:
 * - http://logging.apache.org/log4php/
 */
class CustomLog {

  const DEFAULT_CONF = 'default';

  /**
   * Instancia del custom logger en caso que falle log4php
   */
  private $basicLogger = null;

  /**
   * Instancia del logger de log4php
   */
  private $logger = null;

  public function __construct() {
  }

  /**
   * Lugar donde se guarda el archivo de log del plugin
   */
  public function getLogDir() {
    return ABSPATH . 'logs/';
  }

  /**
   * Lugar donde se guarda el archivo de log del plugin
   */
  public function getLogFile() {
    return $this->getLogDir() . 'klap_checkout.log';
  }

  /**
   * Configura log4php
   */
  private function getLog4phpConfig() {
    return array(
      'rootLogger' => array(
        'level' => 'info',
        'appenders' => array(self::DEFAULT_CONF),
      ),
      'appenders' => array(
        self::DEFAULT_CONF => array(
          'class' => 'LoggerAppenderRollingFile',
          'layout' => array(
            'class' => 'LoggerLayoutPattern',
            'params' => array(
              'conversionPattern' => '[%date{Y-m-d H:i:s T}] [ %-5level] %msg%n',
            )
          ),
          'params' => array(
            'file' => $this->getLogFile(),
            'maxFileSize' => '1MB',
            'maxBackupIndex' => 2,
          )
        )
      )
    );
  }

  /**
   * Retorna el logger
   */
  public function getLogger() {
    if ($this->basicLogger == null) {
      $this->basicLogger = new BasicLog();
    }
    try {
      if ($this->logger == null) {
        if (!file_exists($this->getLogDir())) {
          mkdir($this->getLogDir(), 0777, true);
        }
        if (!file_exists($this->getLogFile()) && is_writable($this->getLogDir())) {
          touch($this->getLogFile());
        }
        if (is_writable($this->getLogFile())) {
          Logger::configure($this->getLog4phpConfig());
          $this->logger = Logger::getLogger(self::DEFAULT_CONF);
        }
      }
    } catch(Exception $ex) {
      return new Error('1', 'Error al obtener logger');
    }
    return $this->logger != null ? $this->logger : $this->basicLogger;
  }

  /**
   * Imprime un mensaje info en el logger
   */
  public function info($msg) {
    $this->getLogger()->info($msg);
  }

  /**
   * Imprime un mensaje error en el logger
   */
  public function warn($msg) {
    $this->getLogger()->warn($msg);
  }

  /**
   * Imprime un mensaje error en el logger
   */
  public function error($msg) {
    $this->getLogger()->error($msg);
  }
}
