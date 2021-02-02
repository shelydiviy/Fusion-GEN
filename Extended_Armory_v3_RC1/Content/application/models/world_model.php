<?php

/**
 * @package FusionCMS
 * @author Jesper Lindström
 * @author Xavier Geerinck
 * @author Elliott Robbins
 * @link http://fusion-hub.com
 */

class World_model
{
	private $db;
	private $config;
	private $CI;
	private $realmId;

	/**
	 * Initialize the realm
	 * @param Array $config Database config
	 */
	public function __construct($config)
	{
		$this->config = $config;
		$this->CI = &get_instance();
		$this->realmId = $this->config['id'];
	}

	/**
	 * Connect to the database if not already connected
	 */
	public function connect()
	{
		if(empty($this->db))
		{
			$this->db = $this->CI->load->database($this->config['world'], true);
		}
	}
	
	public function getConnection()
	{
		$this->connect();

		return $this->db;
	}

	/**
	 * Get a specific item row
	 * @param Int $realm
	 * @param Int $id
	 * @return Array
	 */
	public function getItem($id)
	{
		$cache = $this->CI->cache->get("items/item_".$this->realmId."_".$id);

		if($cache !== false)
		{
			return $cache;
		}
		else
		{
			$this->connect();

			$query = $this->db->query(query('get_item', $this->realmId), array($id));

			if($this->db->_error_message())
			{
				die($this->db->_error_message());
			}
			else 
			{
				//If its not available try to load from wowhead and cache it for 24 hours
				
				$xml = simplexml_load_string(file_get_contents("https://wowhead.com/?item=".$id."&xml"));
				$DisplayId = $xml->item->icon["displayId"];
				$iconId = str_replace("[0]","",$DisplayId);
				
				$this->CI->cache->save("items/item_".$this->realmId."_".$id, $iconId, 60*60*24);

				return false;	
			}

			if($query->num_rows() > 0)
			{
				$row = $query->result_array();

				// Cache it forever
				$this->CI->cache->save("items/item_".$this->realmId."_".$id, $row[0]);

				return $row[0];
			}
			else 
			{
				//If its not available try to load from wowhead and cache it for 24 hours
				
				$xml = simplexml_load_string(file_get_contents("https://wowhead.com/?item=".$id."&xml"));
				$DisplayId = $xml->item->icon["displayId"];
				$iconId = str_replace("[0]","",$DisplayId);
				
				$this->CI->cache->save("items/item_".$this->realmId."_".$id, $iconId, 60*60*24);

				return false;	
			}
		}
	}
}