<?php defined('SYSPATH') or die('No direct script access.');

abstract class Translator_Core
{
    protected static $instance = NULL;
    
    protected $config;
    
    protected $from_lang;
    
    public static function instance()
    {
        if (Translator::$instance === NULL)
        {
            $driver = Kohana::$config->load('translator.default.driver');
            $class = 'Translator_Driver_'.$driver;
            Translator::$instance = new $class(Kohana::$config->load('translator.drivers.'.$driver));
        }
        return Translator::$instance;
    }
    
    protected function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public static function translate($text, $to, $from = '')
    {
        return Translator::instance()->reset()->translate($text, $to, $from);
    }
    
    public static function selfTest()
    {
        if(!function_exists('curl_init'))
        {
            echo "cURL not installed.";
            return false;
        }
        else
        {
            $testText = self::translate("hello", "fr", "en");
            if ($testText == "bonjour")
            {
                echo "Test Ok.";
                return true;
            }
            else
            {
                echo "Test Failed.";
                return false;
            };
        }
    }
    
}