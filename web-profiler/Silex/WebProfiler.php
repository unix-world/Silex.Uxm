<?php

namespace UXM\Silex\WebProfiler;


require(__DIR__.'/Collectors/ConfigDataCollector.php');
require(__DIR__.'/Collectors/AjaxDataCollector.php');
require(__DIR__.'/Collectors/DoctrineDataCollector.php');


use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;
use Silex\ServiceControllerResolver;


class WebProfilerServiceProvider implements ServiceProviderInterface, ControllerProviderInterface {

	public function register(Application $app) {

		$app['profiler.mount_prefix'] = '/_profiler';
		$app['dispatcher'] = $app->share($app->extend('dispatcher', function ($dispatcher, $app) {
			return new \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher($dispatcher, $app['stopwatch'], $app['logger']);
		}));

		$app['twig.loader.filesystem']->addPath(__DIR__.'/Resources/views', 'UxmWebProfiler'); // uxm

		$app['data_collector.templates'] = array(
			array('config',    '@UxmWebProfiler/config.html.twig'), // @uxm
			array('request',   '@WebProfiler/Collector/request.html.twig'),
			array('exception', '@WebProfiler/Collector/exception.html.twig'),
			array('events',    '@WebProfiler/Collector/events.html.twig'),
			array('logger',    '@WebProfiler/Collector/logger.html.twig'),
			array('time',      '@WebProfiler/Collector/time.html.twig'),
			array('router',    '@WebProfiler/Collector/router.html.twig'),
			array('memory',    '@WebProfiler/Collector/memory.html.twig'),
			array('twig',      '@WebProfiler/Collector/twig.html.twig'),
			array('db',        '@UxmWebProfiler/db.html.twig'), // uxm
			array('ajax',      '@UxmWebProfiler/ajax.html.twig') // uxm
		);

		$app['data_collectors'] = $app->share(function ($app) {
			return array(
				'config'    => $app->share(function ($app) { return new \UXM\Silex\WebProfiler\ConfigDataCollector(); }), // uxm
				'request'   => $app->share(function ($app) { return new \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector(); }),
				'exception' => $app->share(function ($app) { return new \Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector(); }),
				'events'    => $app->share(function ($app) { return new \Symfony\Component\HttpKernel\DataCollector\EventDataCollector($app['dispatcher']); }),
				'logger'    => $app->share(function ($app) { return new \Symfony\Component\HttpKernel\DataCollector\LoggerDataCollector($app['logger']); }),
				'time'      => $app->share(function ($app) { return new \Symfony\Component\HttpKernel\DataCollector\TimeDataCollector(null, $app['stopwatch']); }),
				'router'    => $app->share(function ($app) { return new \Symfony\Component\HttpKernel\DataCollector\RouterDataCollector(); }),
				'memory'    => $app->share(function ($app) { return new \Symfony\Component\HttpKernel\DataCollector\MemoryDataCollector(); }),
				'db'        => $app->share(function ($app) { return new \UXM\Silex\WebProfiler\DoctrineDataCollector($app['dbs'], $app['sqlLogger']); }), // uxm
				'ajax'      => $app->share(function ($app) { return new \UXM\Silex\WebProfiler\AjaxDataCollector(); }) // uxm
			);
		});

		if(class_exists('Symfony\Bridge\Twig\Extension\ProfilerExtension')) {
			$app['data_collectors'] = $app->share($app->extend('data_collectors', function ($collectors, $app) {
				$collectors['twig'] = $app->share(function ($app) {
					return new \Symfony\Bridge\Twig\DataCollector\TwigDataCollector($app['twig.profiler.profile']);
				});

				return $collectors;
			}));
			$app['twig.profiler.profile'] = $app->share(function () {
				return new \Twig_Profiler_Profile();
			});
		}

		$app['web_profiler.controller.profiler'] = $app->share(function ($app) {
			return new \Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController($app['url_generator'], $app['profiler'], $app['twig'], $app['data_collector.templates'], $app['web_profiler.debug_toolbar.position']);
		});

		$app['web_profiler.controller.router'] = $app->share(function ($app) {
			return new \Symfony\Bundle\WebProfilerBundle\Controller\RouterController($app['profiler'], $app['twig'], isset($app['url_matcher']) ? $app['url_matcher'] : null, $app['routes']);
		});

		$app['web_profiler.controller.exception'] = $app->share(function ($app) {
			return new \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController($app['profiler'], $app['twig'], $app['debug']);
		});

		$app['web_profiler.toolbar.listener'] = $app->share(function ($app) {
			return new \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener($app['twig'], $app['web_profiler.debug_toolbar.intercept_redirects'], $app['web_profiler.debug_toolbar.position'], $app['url_generator']);
		});

		$app['profiler'] = $app->share(function ($app) {
			$profiler = new \Symfony\Component\HttpKernel\Profiler\Profiler($app['profiler.storage'], $app['logger']);
			foreach ($app['data_collectors'] as $collector) {
				$profiler->add($collector($app));
			}
			return $profiler;
		});

		$app['profiler.storage'] = $app->share(function ($app) {
			return new \Symfony\Component\HttpKernel\Profiler\FileProfilerStorage('file:'.$app['profiler.cache_dir']);
		});

		$app['profiler.request_matcher'] = null;
		$app['profiler.only_exceptions'] = false;
		$app['profiler.only_master_requests'] = false;
		$app['web_profiler.debug_toolbar.enable'] = true;
		$app['web_profiler.debug_toolbar.position'] = 'bottom';
		$app['web_profiler.debug_toolbar.intercept_redirects'] = false;

		$app['profiler.listener'] = $app->share(function ($app) {
			return new \Symfony\Component\HttpKernel\EventListener\ProfilerListener(
				$app['profiler'],
				$app['profiler.request_matcher'],
				$app['profiler.only_exceptions'],
				$app['profiler.only_master_requests']
			);
		});

		$app['stopwatch'] = $app->share(function () {
			return new \Symfony\Component\Stopwatch\Stopwatch();
		});

		$app['code.file_link_format'] = null;

		$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
			$twig->addExtension(new \Symfony\Bridge\Twig\Extension\CodeExtension($app['code.file_link_format'], '', $app['charset']));
			if(class_exists('\Symfony\Bundle\WebProfilerBundle\Twig\WebProfilerExtension')) {
				$twig->addExtension(new \Symfony\Bundle\WebProfilerBundle\Twig\WebProfilerExtension());
			}
			if(class_exists('Symfony\Bridge\Twig\Extension\ProfilerExtension')) {
				$twig->addExtension(new \Symfony\Bridge\Twig\Extension\ProfilerExtension($app['twig.profiler.profile'], $app['stopwatch']));
			}
			return $twig;
		}));

		$app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function ($loader, $app) {
			$loader->addPath($app['profiler.templates_path'], 'WebProfiler');
			return $loader;
		}));

		$app['profiler.templates_path'] = function () {
			$r = new \ReflectionClass('Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener');
			return dirname(dirname($r->getFileName())).'/Resources/views';
		};
	}

	public function connect(Application $app) {
		if(!$app['resolver'] instanceof ServiceControllerResolver) {
			// using RuntimeException crashes PHP?!
			throw new \LogicException('You must enable the ServiceController service provider to be able to use the WebProfiler.');
		}
		$controllers = $app['controllers_factory'];
		$controllers->get('/router/{token}', 'web_profiler.controller.router:panelAction')->bind('_profiler_router');
		$controllers->get('/exception/{token}.css', 'web_profiler.controller.exception:cssAction')->bind('_profiler_exception_css');
		$controllers->get('/exception/{token}', 'web_profiler.controller.exception:showAction')->bind('_profiler_exception');
		$controllers->get('/search', 'web_profiler.controller.profiler:searchAction')->bind('_profiler_search');
		$controllers->get('/search_bar', 'web_profiler.controller.profiler:searchBarAction')->bind('_profiler_search_bar');
		$controllers->get('/purge', 'web_profiler.controller.profiler:purgeAction')->bind('_profiler_purge');
		$controllers->get('/info/{about}', 'web_profiler.controller.profiler:infoAction')->bind('_profiler_info');
		$controllers->get('/phpinfo', 'web_profiler.controller.profiler:phpinfoAction')->bind('_profiler_phpinfo');
		$controllers->get('/{token}/search/results', 'web_profiler.controller.profiler:searchResultsAction')->bind('_profiler_search_results');
		$controllers->get('/{token}', 'web_profiler.controller.profiler:panelAction')->bind('_profiler');
		$controllers->get('/wdt/{token}', 'web_profiler.controller.profiler:toolbarAction')->bind('_wdt');
		$controllers->get('/', 'web_profiler.controller.profiler:homeAction')->bind('_profiler_home');
		return $controllers;
	}

	public function boot(Application $app) {
		$dispatcher = $app['dispatcher'];
		$dispatcher->addSubscriber($app['profiler.listener']);
		if($app['web_profiler.debug_toolbar.enable']) {
			$dispatcher->addSubscriber($app['web_profiler.toolbar.listener']);
		}
		$dispatcher->addSubscriber($app['profiler']->get('request'));
		$app->mount($app['profiler.mount_prefix'], $this->connect($app));
	}

} //END CLASS


//end of php code
?>