<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana Cache Memcached-tags Driver
 * 
 * Memcached-tags extension provides native tagging support to
 * memcache.
 * 
 * @see http://code.google.com/p/memcached-tags/
 * 
 * @package    Kohana
 * @category   Cache
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Cache_MemcacheTag extends Kohana_Cache_Memcache implements Kohana_Cache_Tagging {

	/**
	 * Constructs the memcache object
	 *
	 * @throws  Kohana_Cache_Exception
	 */
	protected function __construct()
	{
		parent::__construct();

		if ( ! method_exists($this->_memcache, 'tag_add'))
			throw new Kohana_Cache_Exception('Memcached-tags PHP plugin not present. Please see http://code.google.com/p/memcached-tags/ for more information');
	}

	/**
	 * Set a value based on an id with tags
	 * 
	 * @param   string   id 
	 * @param   mixed    data 
	 * @param   integer  lifetime [Optional]
	 * @param   array    tags [Optional]
	 * @return  boolean
	 */
	public function set_with_tags($id, $data, $lifetime = NULL, array $tags = NULL)
	{
		$result = $this->set($id, $data, $lifetime);

		if ($result and $tags)
		{
			foreach ($tags as $tag)
				$this->_memcache->tag_add($tag, $id);
		}

		return $result;
	}

	/**
	 * Delete cache entries based on a tag
	 *
	 * @param   string   tag 
	 * @return  boolean
	 */
	public function delete_tag($tag)
	{
		return $this->_memcache->tag_delete($tag);
	}

	/**
	 * Find cache entries based on a tag
	 *
	 * @param   string   tag 
	 * @return  void
	 * @throws  Kohana_Cache_Exception
	 */
	public function find($tag)
	{
		throw new Kohana_Cache_Exception('Memcached-tags does not support finding by tag');
	}	
}