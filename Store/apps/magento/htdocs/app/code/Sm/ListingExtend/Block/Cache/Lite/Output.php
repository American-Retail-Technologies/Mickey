<?php

/**
* This class extends Lite and uses output buffering to get the data to cache.
*
* There are some examples in the 'docs/examples' file
* Technical choices are described in the 'docs/technical' file
*
* @package Lite
* @author Fabien MARTY <fab@php.net>
*/

namespace Sm\ListingExtend\Block\Cache\Lite;
use Sm\ListingExtend\Block\Cache\Lite;
class Output extends Lite
{

    // --- Public methods ---

    /**
    * Constructor
    *
    * $options is an assoc. To have a look at availables options,
    * see the constructor of the Lite class in 'Lite.php'
    *
    * @param array $options options
    * @access public
    */
	
	function __construct($options){
		parent::__construct($options);
	}

    /**
    * Start the cache
    *
    * @param string $id cache id
    * @param string $group name of the cache group
    * @param boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
    * @return boolean true if the cache is hit (false else)
    * @access public
    */
    function start($id, $group = 'default', $doNotTestCacheValidity = false)
    {
        $data = $this->get($id, $group, $doNotTestCacheValidity);
        if ($data !== false) {
            echo($data);
            return true;
        }
        ob_start();
        ob_implicit_flush(false);
        return false;
    }

    /**
    * Stop the cache
    *
    * @access public
    */
    function end()
    {
        $data = ob_get_contents();
        ob_end_clean();
        $this->save($data, $this->_id, $this->_group);
        echo($data);
    }

}
