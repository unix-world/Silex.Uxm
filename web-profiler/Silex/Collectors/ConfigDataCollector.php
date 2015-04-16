<?php

namespace UXM\Silex\WebProfiler;


class ConfigDataCollector extends \Symfony\Component\HttpKernel\DataCollector\DataCollector {

	private $kernel;
	private $name;
	private $version;

	/**
	 * Constructor.
	 *
	 * @param string $name    The name of the application using the web profiler
	 * @param string $version The version of the application using the web profiler
	 */
	public function __construct($name = null, $version = null) {
		$this->name = $name;
		$this->version = $version;
	}

	/**
	 * Sets the Kernel associated with this Request.
	 *
	 * @param KernelInterface $kernel A KernelInterface instance
	 */
	public function setKernel(\Symfony\Component\HttpKernel\KernelInterface $kernel = null) {
		$this->kernel = $kernel;
	}

	/**
	 * {@inheritdoc}
	 */
	public function collect(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Exception $exception = null) {

		if(isset($_SERVER['PHP_AUTH_USER'])) {
			$the_auth_user = ''.$_SERVER['PHP_AUTH_USER'];
		} else {
			$the_auth_user = '';
		} //end if else

		$this->data = array(
			'app_name' => 'Silex.App',
			'app_version' => '1.2.3.smart', //$this->version,
			'token' => $response->headers->get('X-Debug-Token'),
			'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
			'name' => isset($this->kernel) ? $this->kernel->getName() : 'n/a',
			'env' => isset($this->kernel) ? $this->kernel->getEnvironment() : 'n/a',
			'debug' => isset($this->kernel) ? $this->kernel->isDebug() : 'n/a',
			'php_version' => PHP_VERSION,
			'xdebug_enabled' => extension_loaded('xdebug'),
			'eaccel_enabled' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'),
			'apc_enabled' => extension_loaded('apc') && ini_get('apc.enabled'),
			'xcache_enabled' => extension_loaded('xcache') && ini_get('xcache.cacher'),
			'wincache_enabled' => extension_loaded('wincache') && ini_get('wincache.ocenabled'),
			'zend_opcache_enabled' => extension_loaded('Zend OPcache') && ini_get('opcache.enable'),
			'bundles' => array(),
			'sapi_name' => php_sapi_name(),
			'http_auth_name' => $the_auth_user
		);

		if (isset($this->kernel)) {
			foreach ($this->kernel->getBundles() as $name => $bundle) {
				$this->data['bundles'][$name] = $bundle->getPath();
			}
		}

	}

	public function getHttpAuthName() {
		return $this->data['http_auth_name'];
	}

	public function getApplicationName() {
		return $this->data['app_name'];
	}

	public function getApplicationVersion() {
		return $this->data['app_version'];
	}

	/**
	 * Gets the token.
	 *
	 * @return string The token
	 */
	public function getToken() {
		return $this->data['token'];
	}

	/**
	 * Gets the Symfony version.
	 *
	 * @return string The Symfony version
	 */
	public function getSymfonyVersion() {
		return $this->data['symfony_version'];
	}

	/**
	 * Gets the PHP version.
	 *
	 * @return string The PHP version
	 */
	public function getPhpVersion() {
		return $this->data['php_version'];
	}

	/**
	 * Gets the application name.
	 *
	 * @return string The application name
	 */
	public function getAppName() {
		return $this->data['name'];
	}

	/**
	 * Gets the environment.
	 *
	 * @return string The environment
	 */
	public function getEnv() {
		return $this->data['env'];
	}

	/**
	 * Returns true if the debug is enabled.
	 *
	 * @return bool true if debug is enabled, false otherwise
	 */
	public function isDebug() {
		return $this->data['debug'];
	}

	/**
	 * Returns true if the XDebug is enabled.
	 *
	 * @return bool true if XDebug is enabled, false otherwise
	 */
	public function hasXDebug() {
		return $this->data['xdebug_enabled'];
	}

	/**
	 * Returns true if EAccelerator is enabled.
	 *
	 * @return bool true if EAccelerator is enabled, false otherwise
	 */
	public function hasEAccelerator() {
		return $this->data['eaccel_enabled'];
	}

	/**
	 * Returns true if APC is enabled.
	 *
	 * @return bool true if APC is enabled, false otherwise
	 */
	public function hasApc() {
		return $this->data['apc_enabled'];
	}

	/**
	 * Returns true if Zend OPcache is enabled.
	 *
	 * @return bool true if Zend OPcache is enabled, false otherwise
	 */
	public function hasZendOpcache() {
		return $this->data['zend_opcache_enabled'];
	}

	/**
	 * Returns true if XCache is enabled.
	 *
	 * @return bool true if XCache is enabled, false otherwise
	 */
	public function hasXCache() {
		return $this->data['xcache_enabled'];
	}

	/**
	 * Returns true if WinCache is enabled.
	 *
	 * @return bool true if WinCache is enabled, false otherwise
	 */
	public function hasWinCache() {
		return $this->data['wincache_enabled'];
	}

	/**
	 * Returns true if any accelerator is enabled.
	 *
	 * @return bool true if any accelerator is enabled, false otherwise
	 */
	public function hasAccelerator() {
		return $this->hasApc() || $this->hasZendOpcache() || $this->hasEAccelerator() || $this->hasXCache() || $this->hasWinCache();
	}

	public function getBundles() {
		return $this->data['bundles'];
	}

	/**
	 * Gets the PHP SAPI name.
	 *
	 * @return string The environment
	 */
	public function getSapiName() {
		return $this->data['sapi_name'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'config';
	}

} //END CLASS


//end of php code
?>