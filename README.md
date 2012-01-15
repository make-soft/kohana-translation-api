Kohana MyMemory Translation API
====================

To use Kohana Translation API, download and extract the latest stable release of Kohana Translation API
from [Github](https://github.com/make-soft/kohana-translation-api). Place the module into your Kohana instances modules 
folder. Finally enable the module within the application bootstrap within the section entitled _modules_.

Kohana Translation API provides translation services

* MyMemory 

Quick test
----------

To test if your Kohana Translation API works properly try this small test in your controller:

	Translator::selfTest();

Quick example
-------------

The following is a quick example of how to use Kohana Translation API.

	$sampleText = "Bonjour de cette partie du monde";
	 
	/* translate(string, to_language, from_language) */
	echo Translator::translate($sampleText , "pl", "fr");

