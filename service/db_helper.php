<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/
namespace robertheim\topictags\service;

/**
* Helper for executing db queries.
*/
class db_helper
{

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	private $db;

	public function __construct(\phpbb\db\driver\driver_interface $db)
	{
		$this->db = $db;
	}

	/**
	 * Executes the sql query and gets the results ids.
	 *
	 * @param string $sql
	 *        	a sql query that fetches ids.
	 * @param string $field_name
	 *        	the name of the field
	 * @return array int array of ids
	 */
	public function get_ids($sql, $field_name = 'id')
	{
		$result = $this->db->sql_query($sql);
		$ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row[$field_name];
		}
		$this->db->sql_freeresult($result);
		return $ids;
	}

	/**
	 * Executes the given sql and creates an array from the result using the $field_name column.
	 *
	 * @param string $sql
	 *        	the sql string whose result contains a column named $field_name
	 * @param string $field_name
	 *        	the name of the field
	 * @return array array of $field_name
	 */
	public function get_array_by_fieldname($sql, $field_name)
	{
		$result = $this->db->sql_query($sql);
		$re = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$re[] = $row[$field_name];
		}
		$this->db->sql_freeresult($result);
		return $re;
	}

	/**
	 * Executes the sql and fetches the rows as array.
	 *
	 * @param string $sql
	 *        	the sql string
	 * @param int $limit
	 *        	optional limit
	 * @param int $start
	 *        	otional start
	 * @return array the resulting array
	 */
	public function get_array($sql, $limit = 0, $start = 0)
	{
		$result = $limit > 0
			? $this->db->sql_query_limit($sql, $limit, $start)
			: $this->db->sql_query($sql);
		$re = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$re[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $re;
	}

	/**
	 * Executes the given sql and creates an array of arrays from the result using the $field_names columns.
	 *
	 * @param string $sql
	 *        	the sql string whose result contains a column named $field_name
	 * @param array $field_names
	 *        	the name of the columns to use
	 * @param int $limit
	 *        	optional limit
	 * @param int $start
	 *        	otional start
	 * @return array array of arrays, e.g. [['a'=> ..., 'b' => ...], [...], ...]]
	 */
	public function get_multiarray_by_fieldnames($sql, array $field_names, $limit = 0, $start = 0)
	{
		$result = $limit > 0
			? $this->db->sql_query_limit($sql, $limit, $start)
			: $this->db->sql_query($sql);
		$re = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$data = array();
			foreach ($field_names as $field_name)
			{
				$data[$field_name] = $row[$field_name];
			}
			$re[] = $data;
		}
		$this->db->sql_freeresult($result);
		return $re;
	}

	/**
	 * Executes the given $sql and fetches the field $field_name
	 *
	 * @param string $sql
	 *        	the sql query
	 * @param string $field_name
	 *        	the name of the field to fetch
	 * @param int $limit
	 *        	optional limit
	 * @param int $start
	 *        	otional start
	 * @return the value of the field
	 */
	public function get_field($sql, $field_name, $limit = 0, $start = 0)
	{
		$result = $limit > 0
			? $this->db->sql_query_limit($sql, $limit, $start)
			: $this->db->sql_query($sql);
		$re = $this->db->sql_fetchfield($field_name);
		$this->db->sql_freeresult($result);
		return $re;
	}
}
