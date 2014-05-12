<?php defined('SYSPATH') or die('No direct script access.');
/**
 * [Kohana Cache](api/Kohana_Cache) Redis driver,
 *
 * @package    Kohana/Cache
 * @category   Base
 * @version    2.0
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Cache_Redis extends Cache {

	/**
	 * Redis resource
	 *
	 * @var Redis
	 */
	protected $_redis;
  
	protected $_db = 8;

	/**
	 * Constructs the Redis Kohana_Cache object
	 *
	 * @param   array  $config  configuration
	 * @throws  Cache_Exception
	 */
	protected function __construct(array $config)
	{
		// Check for the Redis extention
		if ( ! extension_loaded('redis'))
		{
			throw new Cache_Exception('Redis PHP extention not loaded');
		}

		parent::__construct($config);

                if ( ! isset($config['group']))
                {
                // Use the default group
                $config['group'] = Redis_Client::$default;
                }

                if (isset($config['db'])) {
                $this->_db = $config['db'];
                }
    
                // Setup Redis
		$this->_redis = Redis_Client::instance($config['group'])->getDB($this->_db);
	}

	/**
	 * Retrieve a cached value entry by id.
	 *
	 *     // Retrieve cache entry from Redis group
	 *     $data = Cache::instance('Redis')->get('foo');
	 *
	 *     // Retrieve cache entry from Redis group and return 'bar' if miss
	 *     $data = Cache::instance('Redis')->get('foo', 'bar');
	 *
	 * @param   string  $id       id of cache to entry
	 * @param   string  $default  default value to return if cache miss
	 * @return  mixed
	 * @throws  Cache_Exception
	 */
	public function get($id, $default = NULL)
	{
		// Get the value from Redis
		$value = $this->_redis->get($this->_sanitize_id($id));

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
	 *     // Set 'bar' to 'foo' in Redis group for 10 minutes
	 *     if (Cache::instance('Redis')->set('foo', $data, 600))
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
		// Set the data to Redis
		return $this->_redis->setex($this->_sanitize_id($id), $lifetime, igbinary_serialize($data));
	}

	/**
	 * Delete a cache entry based on id
	 *
	 *     // Delete the 'foo' cache entry immediately
	 *     Cache::instance('Redis')->delete('foo');
	 *
	 *     // Delete the 'bar' cache entry after 30 seconds
	 *     Cache::instance('Redis')->delete('bar', 30);
	 *
	 * @param   string   $id       id of entry to delete
	 * @return  boolean
	 */
	public function delete($id)
	{
		// Delete the id
		return $this->_redis->delete($this->_sanitize_id($id));
	}

	/**
	 * Delete all cache entries.
	 *
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 *
	 *     // Delete all cache entries in the default group
	 *     Cache::instance('Redis')->delete_all();
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		$result = $this->_redis->flushDB();
		return $result;
	}
        
  public static function get_request_key(Request $request)
  {
    $uri     = $request->uri();
    $query   = $request->query();
    return sha1($uri.'?'.http_build_query($query, NULL, '&'));
  }

}
