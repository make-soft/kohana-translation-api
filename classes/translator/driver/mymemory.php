<?php defined('SYSPATH') or die('No direct script access.');

class Translator_Driver_MyMemory
{
    
    /**
     * URL of Google translate
     * @var string
     */
    private $_mymemoryTranslateUrl = 'http://mymemory.translated.net/api/get';

    /**
     * Language to translate from
     * @var string
     */
    private $_fromLang = '';
    
    /**
     * Language to translate to
     * @var string
     */
    private $_toLang = '';
    
    /**
     * API version
     * @var string
     */
    private $_version = '1.0';
    
    /**
     * Text to translate
     * @var string
     */
    private $_text = '';
    
    /**
     * Site url using the code
     * @var string
     */
    private $_siteUrl = '';
    
    /**
     * MyMemory user
     * @var string
     */
    private $_apiUser = '';
    /**
     * MyMemory key
     * @var string
     */
    private $_apiKey = '';
    
    /**
     * Host IP address
     * @var string
     */
    private $_ip = '';
    
    /**
     * POST fields
     * @var string
     */
    private $_postFields;
    
    /**
     * Translated Text
     * @var string
     */
    private $_translatedText;
    
    /**
     * Service Error
     * @var string
     */
    private $_serviceError = "";
    
    /**
     * Translation success
     * @var boolean
     */
    private $_success = false;
    
    /**
     * Translation character limit.
     * Currently the limit set by MyMemory is 1000
     * @var integer
     */
    private $_stringLimit = 1000;
    
    /**
     * Chunk array
     * @var array
     */
    private $_chunks = 0;
    
    /**
     * Current data chunk
     * @var string
     */
    private $_currentChunk = 0;
    
    /**
     * Total chunks
     * @var integer
     */
    private $_totalChunks = 0;
    
    /**
     * Detected source language
     * @var string
     */
    private $_detectedSourceLanguage = "";
    
    const DETECT = 1;
    const TRANSLATE = 2;

    public function reset()
    {
        $this->_chunks = 0;
        $this->_totalChunks = 0;
        $this->_currentChunk = 0;
        $this->_translatedText = '';
        return $this;
    }
    
    /**
     * Build a POST url to query MyMemory
     *
     */
    private function _composeUrl($type) 
    {
        if($type == self::TRANSLATE)
        {
            $fields = array('q'         => $this->_text,
                            'of'        => 'json',
                            'langpair'  => $this->_fromLang . "|" . $this->_toLang);
        }
        elseif($type == self::DETECT)
        {
            $fields = array('v'         => $this->_version,
                        'q'         => $this->_text);
        }
        
        if($this->_apiUser != "") $fields['user'] = $this->_apiUser;
        if($this->_apiKey != "") $fields['key'] = $this->_apiKey;
        if($this->_ip != "") $fields['ip'] = $this->_ip;

        $this->_postFields = URL::query($fields);
    }
    
    /**
     * Process the built query using cURL and POST
     *
     * @param string POST fields
     * @return string response
     */
    private function _remoteQuery($query)
    {
        if(!function_exists('curl_init'))
        {
            return "";
        }
        
        /* Setup CURL and its options*/
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->_mymemoryTranslateUrl.$query);
        //curl_setopt($ch, CURLOPT_REFERER, $this->_siteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

        $response = curl_exec($ch); 

        return $response;
    }
    
    /**
     * Check if the last translation was a success
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->_success;
    }
    
    /**
     * Get the last generated service error
     *
     * @return String
     */
    public function getLastError()
    {
        return $this->_serviceError;
    }
    
    /**
     * Set credentials (optional) when accessing MyMemory translation services
     *
     * @param string $apiKey your mymemory api user
     * @param string $apiKey your mymemory api key
     * @param string $ip your ip address
     */
    public function setCredentials($apiUser, $apiKey, $ip = '')
    {
        $this->_apiUser = $apiUser;
        $this->_apiKey = $apiKey;
        $this->_ip = $ip;
    }
    
    public function translate($text, $to, $from = '')
    {
        $this->_success = false;
        
        if($text == '' || $to == '')
        {
            return false;
        }
        else
        {
            if($this->_chunks == 0)
            {
                $this->_chunks = str_split($text, $this->_stringLimit);
                $this->_totalChunks = count($this->_chunks);
                $this->_currentChunk = 0;
             
                $this->_text = $this->_chunks[$this->_currentChunk];
                $this->_toLang = $to;
                $this->_fromLang = $from;
            }
            else
            {
                $this->_text = $text;
                $this->_toLang = $to;
                $this->_fromLang = $from;
            }
        }
        
        $this->_composeUrl(self::TRANSLATE);
        
        if($this->_text != '' && $this->_postFields != '')
        {
            $contents = $this->_remoteQuery($this->_postFields);
            $json = json_decode($contents, true);
            
            if($json['responseStatus'] == 200)
            {   
                $this->_translatedText .= $json['responseData']['translatedText'];
                if(isset($json['responseData']['detectedSourceLanguage']))
                {
                    $this->_detectedSourceLanguage = $json['responseData']['detectedSourceLanguage'];   
                }
                
                $this->_currentChunk++;

                if($this->_currentChunk >= $this->_totalChunks) {
                    $this->_success = true;
                    return $this->_translatedText;
                }
                else {
                    return $this->translate($this->_chunks[$this->_currentChunk], $to, $from);
                }
                
            }
            else
            { 
                $this->_serviceError = 	$json['responseDetails'];
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}