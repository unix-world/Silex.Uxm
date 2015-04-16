<?php

//--
function autoload__UxmSilex($classname) {
	//--
	$classname = (string) ''.$classname;
	//--
	if(strpos($classname, '\\') !== false) { // libraries with name spaces
		//--
		$parts = explode('\\', $classname);
		//--
		$max = count($parts) - 1; // the last is the class
		//--
		if((string)$parts[0] == 'UXM') {
			if((string)$parts[1] == 'Utils') {
				$dir = __DIR__.'/libs/';
				$file = 'lib-uxm-utils.php';
			} elseif((string)$parts[1] == 'Db') {
				$dir = __DIR__.'/libs/';
				$file = 'lib-uxm-doctrine-dbal-handler.php';
			} elseif(((string)$parts[1] == 'Silex') AND ((string)$parts[2] == 'WebProfiler')) {
				$dir = __DIR__.'/web-profiler/Silex/';
				$file = $parts[2].'.php';
			} else {
				return;
			} //end if else
		} else {
			return;
		} //end if else
		//--
		$path = $dir.$file;
		//--
		if(!is_file($path)) {
			return;
		} //end if
		//--
		require_once($path);
		//--
	} //end if else
	//--
} //END FUNCTION
//--
spl_autoload_register('autoload__UxmSilex', true, false); // throw / append
//--

// end of php code
?>