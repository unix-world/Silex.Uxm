<?php

namespace UXM\Silex\WebProfiler;


class AjaxDataCollector extends \Symfony\Component\HttpKernel\DataCollector\DataCollector {

	public function collect(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Exception $exception = null) {
		// all collecting is done client side
	}

	public function getName() {
		return 'ajax';
	}

} //END CLASS


//end of php code
?>