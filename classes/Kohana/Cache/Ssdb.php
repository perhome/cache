<?php defined('SYSPATH') or die('No direct script access.');
/**
 * [Kohana Cache](api/Kohana_Cache) ssdb driver,
 *
 * @package    Kohana/Cache
 * @category   Base
 * @version    2.0
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Cache_Ssdb extends Cache {

	/**
	 * ssdb resource
	 *
	 * @var ssdb
	 */
	protected $_ssdb;
  

	/**
	 * Constructs the ssdb Kohana_Cache object
	 *
	 * @param   array  $config  configuration
	 * @throws  Cache_Exception
	 */
	protected function __construct(array $config)
	{
		parent::__construct($config);

    if ( ! isset($config['group']))
    {
      // Use the default group
      $config['group'] = Ssdb_Client::$default;
    }
    
    // Setup ssdb
		$this->_ssdb = Ssdb_Client::instance($config['group']);
	}

	/**
	 * Retrieve a cached value entry by id.
	 *
	 *     // Retrieve cache entry from ssdb group
	 *     $data = Cache::instance('ssdb')->get('foo');
	 *
	 *     // Retrieve cache entry from ssdb group and return 'bar' if miss
	 *     $data = Cache::instance('ssdb')->get('foo', 'bar');
	 *
	 * @param   string  $id       id of cache to entry
	 * @param   string  $default  default value to return if cache miss
	 * @return  mixed
	 * @throws  Cache_Exception
	 */
	public function get($id, $default = NULL)
	{
		// Get the value from ssdb
		$value = $this->_ssdb->get($this->_sanitize_id($id));

		// If the value wasn't found, normalise it
		if ($value === FALSE)
		{
			$value = (NULL === $default) ? NULL : $default;
                        return $value;
		}

		// Return the value
		return igbinary_unserialize($value);
	}

	/**
	 * Set a value to cache with id and lifetime
	 *
	 *     $data = 'bar';
	 *
	 *     // Set 'bar' to 'foo' in ssdb group for 10 minutes
	 *     if (Cache::instance('ssdb')->set('foo', $data, 600))
	 *     {
	 *          // Cache was set successfully
	 *          return
	 *     }
	 *
	 * @param   string   $id        id of cache entry
	 * @param   mixed    $data      data to set to cache
	 * @param   integer  $lifetime  lifetime in seconds, maximum value 2592000
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = 3600)
	{
		// Set the data to ssdb
		return $this->_ssdb->setx($this->_sanitize_id($id), igbinary_serialize($data), $lifetime);
	}

	/**
	 * Delete a cache entry based on id
	 *
	 *     // Delete the 'foo' cache entry immediately
	 *     Cache::instance('ssdb')->delete('foo');
	 *
	 *     // Delete the 'bar' cache entry after 30 seconds
	 *     Cache::instance('ssdb')->delete('bar', 30);
	 *
	 * @param   string   $id       id of entry to delete
	 * @return  boolean
	 */
	public function delete($id)
	{
		// Delete the id
		return $this->_ssdb->del($this->_sanitize_id($id));
	}

	/**
	 * Delete all cache entries.
	 *
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 *
	 *     // Delete all cache entries in the default group
	 *     Cache::instance('ssdb')->delete_all();
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
	}
        
}
