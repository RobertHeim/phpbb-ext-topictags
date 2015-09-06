<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\service;

/**
 * @ignore
 */
use robertheim\topictags\tables;
use robertheim\topictags\prefixes;
use robertheim\topictags\service\db_helper;

/**
* Handles all functionallity regarding tags.
* This class is basically a manager (functions for cleaning and validating tags)
* and a DAO (storing/retrieving tags to/from the DB).
*/
class tags_manager
{

	/** @var \phpbb\db\driver\driver_interface */
	private $db;

	/** @var \phpbb\config\config */
	private $config;

	/** @var \phpbb\config\db_text */
	private $config_text;

	/** @var \phpbb\auth\auth */
	private $auth;

	/** @var db_helper */
	private $db_helper;

	/** @var string */
	private $table_prefix;

	public function __construct(
					\phpbb\db\driver\driver_interface $db,
					\phpbb\config\config $config,
					\phpbb\config\db_text $config_text,
					\phpbb\auth\auth $auth,
					db_helper $db_helper,
					$table_prefix)
	{
		$this->db			= $db;
		$this->config		= $config;
		$this->config_text	= $config_text;
		$this->auth			= $auth;
		$this->db_helper	= $db_helper;
		$this->table_prefix	= $table_prefix;
	}

	/**
	 * Remove all tags from the given topic
	 *
	 * @param $topic_id
	 * @param $delete_unused_tags if set to true unused tags are removed from the db.
	 */
	public function remove_all_tags_from_topic($topic_id, $delete_unused_tags = true)
	{
		$this->remove_all_tags_from_topics(array($topic_id), $delete_unused_tags);
	}

	/**
	 * Remove tag assignments from the given topics
	 *
	 * @param $topic_ids array of topic ids
	 * @param $delete_unused_tags if set to true unused tags are removed from the db.
	 */
	public function remove_all_tags_from_topics(array $topic_ids, $delete_unused_tags = true)
	{
		// remove tags from topic
		$sql = 'DELETE FROM ' . $this->table_prefix . tables::TOPICTAGS. '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
		$this->db->sql_query($sql);
		if ($delete_unused_tags)
		{
			$this->delete_unused_tags();
		}
		$this->calc_count_tags();
	}

	/**
	 * Gets the ids of all tags, that are not assigned to a topic.
	 */
	private function get_unused_tag_ids()
	{
		$sql = 'SELECT t.id
			FROM ' . $this->table_prefix . tables::TAGS . ' t
			WHERE NOT EXISTS (
				SELECT 1
				FROM ' . $this->table_prefix . tables::TOPICTAGS . ' tt
					WHERE tt.tag_id = t.id
			)';
		return $this->db_helper->get_ids($sql);
	}

	/**
	 * Removes all tags that are not assigned to at least one topic (garbage collection).
	 *
	 * @return integer count of deleted tags
	 */
	public function delete_unused_tags()
	{
		$ids = $this->get_unused_tag_ids();
		if (empty($ids))
		{
			// nothing to do
			return 0;
		}
		$sql = 'DELETE FROM ' . $this->table_prefix . tables::TAGS . '
			WHERE ' . $this->db->sql_in_set('id', $ids);
		$this->db->sql_query($sql);
		return $this->db->sql_affectedrows();
	}

	/**
	 * Deletes all assignments of tags, that are no longer valid
	 *
	 * @return integer count of removed assignments
	 */
	public function delete_assignments_of_invalid_tags()
	{
		// get all tags to check them
		$tags = $this->get_existing_tags(null);

		$ids_of_invalid_tags = array();
		foreach ($tags as $tag)
		{
			if (!$this->is_valid_tag($tag['tag']))
			{
				$ids_of_invalid_tags[] = (int) $tag['id'];
			}
		}
		if (empty($ids_of_invalid_tags))
		{
			// nothing to do
			return 0;
		}

		// delete all tag-assignments where the tag is not valid
		$sql = 'DELETE FROM ' . $this->table_prefix . tables::TOPICTAGS . '
			WHERE ' . $this->db->sql_in_set('tag_id', $ids_of_invalid_tags);
		$this->db->sql_query($sql);
		$removed_count = $this->db->sql_affectedrows();

		$this->calc_count_tags();

		return $removed_count;
	}

	private function get_assignment_ids_where_topic_does_not_exist()
	{
		$sql = 'SELECT tt.id
			FROM ' . $this->table_prefix . tables::TOPICTAGS . ' tt
			WHERE NOT EXISTS (
				SELECT 1
				FROM ' . TOPICS_TABLE . ' topics
					WHERE topics.topic_id = tt.topic_id
			)';
		return $this->db_helper->get_ids($sql);
	}

	/**
	 * Removes all topic-tag-assignments where the topic does not exist anymore.
	 *
	 * @return integer count of deleted assignments
	 */
	public function delete_assignments_where_topic_does_not_exist()
	{
		$ids = $this->get_assignment_ids_where_topic_does_not_exist();
		if (empty($ids))
		{
			// nothing to do
			return 0;
		}
		// delete all tag-assignments where the topic does not exist anymore
		$sql = 'DELETE FROM ' . $this->table_prefix . tables::TOPICTAGS . '
			WHERE ' . $this->db->sql_in_set('id', $ids);
		$this->db->sql_query($sql);
		$removed_count = $this->db->sql_affectedrows();

		$this->calc_count_tags();

		return $removed_count;
	}

	/**
	 * Deletes all topic-tag-assignments where the topic resides in a forum with tagging disabled.
	 *
	 * @param $forum_ids array of forum-ids that should be checked (if null, all are checked).
	 * @return integer count of deleted assignments
	 */
	public function delete_tags_from_tagdisabled_forums($forum_ids = null)
	{
		$forums_sql_where = '';

		if (is_array($forum_ids))
		{
			if (empty($forum_ids))
			{
				// performance improvement because we already know the result of querying the db.
				return 0;
			}
			$forums_sql_where = ' AND ' . $this->db->sql_in_set('f.forum_id', $forum_ids);
		}

		// get ids of all topic-assignments to topics that reside in a forum with tagging disabled.
		$sql = 'SELECT tt.id
			FROM ' . $this->table_prefix . tables::TOPICTAGS . ' tt
			WHERE EXISTS (
				SELECT 1
				FROM ' . TOPICS_TABLE . ' topics,
					' . FORUMS_TABLE . " f
				WHERE topics.topic_id = tt.topic_id
					AND f.forum_id = topics.forum_id
					AND f.rh_topictags_enabled = 0
					$forums_sql_where
			)";
		$delete_ids = $this->db_helper->get_ids($sql);

		if (empty($delete_ids))
		{
			// nothing to do
			return 0;
		}
		// delete these assignments
		$sql = 'DELETE FROM ' . $this->table_prefix . tables::TOPICTAGS . '
			WHERE ' . $this->db->sql_in_set('id', $delete_ids);
		$this->db->sql_query($sql);
		$removed_count = $this->db->sql_affectedrows();

		$this->calc_count_tags();

		return $removed_count;
	}


	/**
	 * Gets all assigned tags
	 *
	 * @param $topic_id
	 * @return array of tag names
	 */
	public function get_assigned_tags($topic_id)
	{
		$topic_id = (int) $topic_id;
		$sql = 'SELECT t.tag
			FROM
				' . $this->table_prefix . tables::TAGS . ' AS t,
				' . $this->table_prefix . tables::TOPICTAGS . " AS tt
			WHERE tt.topic_id = $topic_id
				AND t.id = tt.tag_id";
		return $this->db_helper->get_array_by_fieldname($sql, 'tag');
	}

	/**
	 * Gets $count tags that start with $query, ordered by their usage count (desc).
	 * Note: that $query needs to be at least 3 characters long.
	 *
	 * @param $query prefix of tags to search
	 * @param $exclude array of tags that should be ignored
	 * @param $count count of tags to return
	 * @return array (array('text' => '...'), array('text' => '...'))
	 */
	public function get_tag_suggestions($query, $exclude, $count)
	{
		if (utf8_strlen($query) < 3)
		{
			return array();
		}
		$exclude_sql = '';
		if (!empty($exclude))
		{
			$exclude_sql = ' AND ' . $this->db->sql_in_set('t.tag', $exclude, true, true);
		}
		$sql_array = array(
			// we must fetch count, because postgres needs the context for ordering
			'SELECT'	=> 't.tag, t.count',
			'FROM'		=> array(
				$this->table_prefix . tables::TAGS		=> 't',
			),
			'WHERE'		=> 't.tag ' . $this->db->sql_like_expression($query . $this->db->get_any_char()) . "
							$exclude_sql",
			'ORDER_BY'	=> 't.count DESC',
		);
		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $count);
		$tags = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$tags[] = array('text' => $row['tag']);
		}
		$this->db->sql_freeresult($result);
		return $tags;
	}

	/**
	 * Assigns exactly the given valid tags to the topic (all other tags are removed from the topic and if a tag does not exist yet, it will be created).
	 *
	 * @param $topic_id
	 * @param $valid_tags			array containing valid tag-names
	 */
	public function assign_tags_to_topic($topic_id, $valid_tags)
	{
		$topic_id = (int) $topic_id;

		$this->remove_all_tags_from_topic($topic_id, false);
		$this->create_missing_tags($valid_tags);

		// get ids of tags
		$ids = $this->get_existing_tags($valid_tags, true);

		// create topic_id <-> tag_id link in TOPICTAGS_TABLE
		$sql_ary = array();
		foreach ($ids as $id)
		{
			$sql_ary[] = array(
				'topic_id'	=> $topic_id,
				'tag_id'	=> $id
			);
		}
		$this->db->sql_multi_insert($this->table_prefix . tables::TOPICTAGS, $sql_ary);

		// garbage collection
		$this->delete_unused_tags();

		$this->calc_count_tags();
	}

	/**
	 * Finds whether the given tags already exist and if not creates them in the db.
	 */
	private function create_missing_tags($tags)
	{
		// we will get all existing tags of $tags
		// and then substract these from $tags
		// result contains the tags that needs to be created
		// to_create = $tags - exting

		// ensure that there isn't a tag twice in the array
		$tags = array_unique($tags);

		$existing_tags = $this->get_existing_tags($tags);

		// find all tags that are not in $existing_tags and add them to $sql_ary_new_tags
		$sql_ary_new_tags = array();
		foreach ($tags as $tag)
		{
			if (!$this->in_array_r($tag, $existing_tags))
			{
				// tag needs to be created
				$sql_ary_new_tags[] = array(
					'tag'			=> $tag,
					'tag_lowercase'	=> utf8_strtolower($tag),
				);
			}
		}

		// create the new tags
		$this->db->sql_multi_insert($this->table_prefix . tables::TAGS, $sql_ary_new_tags);
	}

	/**
	 * Recursive in_array to check if the given (eventually multidimensional) array $haystack contains $needle.
	 */
	private function in_array_r($needle, $haystack, $strict = false)
	{
		foreach ($haystack as $item)
		{
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict)))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Gets the existing tags of the given tags or all existing tags if $tags == null.
	 * If $only_ids is set to true, an array containing only the ids of the tags will be returned: array(1,2,3,..)
	 *
	 * @param $tags array of tag-names; might be null to get all existing tags
	 * @param $only_ids whether to return only the tag-ids (true) or tag names as well (false, default)
	 * @return array an array of the form array(array('id'=>.. , 'tag'=> ..), array('id'=>.. , 'tag'=> ..), ...) or array(1,2,3,..) if $only_ids==true
	 */
	public function get_existing_tags($tags = null, $only_ids = false)
	{
		$where = '';
		if (!is_null($tags))
		{
			if (empty($tags))
			{
				// ensure that empty input array results in empty output array.
				// note that this case is different from $tags == null where we want to get ALL existing tags.
				return array();
			}
			$where = 'WHERE ' . $this->db->sql_in_set('tag', $tags);
		}
		$sql = 'SELECT id, tag
			FROM ' . $this->table_prefix . tables::TAGS . "
			$where";
		if ($only_ids)
		{
			return $this->db_helper->get_ids($sql);
		}
		return $this->db_helper->get_multiarray_by_fieldnames($sql, array(
				'id',
				'tag'
			));
	}

	/**
	 * Gets the topics which are tagged with any or all of the given $tags from all forums, where tagging is enabled and only those which the user is allowed to read.
	 *
	 * @param $start start for sql query
	 * @param $limit limit for sql query
	 * @param $tags array of tags to find the topics for
	 * @param $mode AND=all tags must be assigned, OR=at least one tag needs to be assigned
	 * @param $casesensitive wether the search should be casesensitive (true) or not (false).
	 * @return array of topics, each containing all fields from TOPICS_TABLE
	 */
	public function get_topics_by_tags(array $tags, $start, $limit, $mode = 'AND', $casesensitive = false)
	{
		$sql = $this->get_topics_build_query($tags, $mode, $casesensitive);
		$order_by = ' ORDER BY topics.topic_last_post_time DESC';
		$sql .= $order_by;
		return $this->db_helper->get_array($sql, $limit, $start);
	}

	/**
	 * Counts the topics which are tagged with any or all of the given $tags from all forums, where tagging is enabled and only those which the user is allowed to read.
	 *
	 * @param array $tags the tags to find the topics for
	 * @param $mode AND(default)=all tags must be assigned, OR=at least one tag needs to be assigned
	 * @param $casesensitive search case-sensitive if true, insensitive otherwise (default).
	 * @return int count of topics found
	 */
	public function count_topics_by_tags(array $tags, $mode = 'AND', $casesensitive = false)
	{
		if (empty($tags))
		{
			return 0;
		}
		$sql = $this->get_topics_build_query($tags, $mode, $casesensitive);
		$sql = "SELECT COUNT(*) as total_results
			FROM ($sql) a";
		return (int) $this->db_helper->get_field($sql, 'total_results');
	}

	/**
	 * Generates a sql_in_set depending on $casesensitive using tag or tag_lowercase.
	 *
	 * @param array $tags the tags to build the sql for
	 * @param boolean $casesensitive whether to let the tags in place (true) or make them lower case (false)
	 * @return string the sql in string depending on $casesensitive using tag or tag_lowercase
	 */
	private function sql_in_casesensitive_tag(array $tags, $casesensitive)
	{
		$tags_copy = $tags;
		if (!$casesensitive)
		{
			$tag_count = sizeof($tags_copy);
			for ($i = 0; $i < $tag_count; $i++)
			{
				$tags_copy[$i] = utf8_strtolower($tags_copy[$i]);
			}
		}
		return $this->db->sql_in_set($casesensitive ? ' t.tag' : 't.tag_lowercase', $tags_copy);
	}

	/**
	 * Gets the forum ids that the user is allowed to read.
	 *
	 * @return array forum ids that the user is allowed to read
	 */
	private function get_readable_forums()
	{
		$forum_ary = array();
		$forum_read_ary = $this->auth->acl_getf('f_read');
		foreach ($forum_read_ary as $forum_id => $allowed)
		{
			if ($allowed['f_read'])
			{
				$forum_ary[] = (int) $forum_id;
			}
		}

		// Remove double entries
		$forum_ary = array_unique($forum_ary);
		return $forum_ary;
	}

	/**
	 * Get sql-source for the topics that reside in forums that the user can read and which are approved.
	 *
	 * @return string the generated sql
	 */
	private function sql_where_topic_access()
	{
		$forum_ary = $this->get_readable_forums();
		$sql_where_topic_access = '';
		if (empty($forum_ary))
		{
			$sql_where_topic_access = ' 1=0 ';
		}
		else
		{
			$sql_where_topic_access = $this->db->sql_in_set('topics.forum_id', $forum_ary, false, true);
		}
		$sql_where_topic_access .= ' AND topics.topic_visibility = ' . ITEM_APPROVED;
		return $sql_where_topic_access;
	}

	/**
	 * Builds an sql query that selects all topics assigned with the tags depending on $mode and $casesensitive
	 *
	 * @param $tags array of tags
	 * @param $mode AND or OR
	 * @param $casesensitive false or true
	 * @return string 'SELECT topics.* FROM ' . TOPICS_TABLE . ' topics WHERE ' . [calculated where]
	 */
	public function get_topics_build_query(array $tags, $mode = 'AND', $casesensitive = false)
	{
		if (empty($tags))
		{
			return 'SELECT topics.* FROM ' . TOPICS_TABLE . ' topics WHERE 0=1';
		}

		// validate mode
		$mode = ($mode == 'OR' ? 'OR' : 'AND');

		$sql_where_tag_in = $this->sql_in_casesensitive_tag($tags, $casesensitive);
		$sql_where_topic_access = $this->sql_where_topic_access();
		$sql = '';
		if ('AND' == $mode)
		{
			$tag_count = sizeof($tags);
			// http://stackoverflow.com/questions/26038114/sql-select-distinct-where-exist-row-for-each-id-in-other-table
			$sql = 'SELECT topics.*
				FROM 	' . TOPICS_TABLE								. ' topics
					JOIN ' . $this->table_prefix . tables::TOPICTAGS	. ' tt ON tt.topic_id = topics.topic_id
					JOIN ' . $this->table_prefix . tables::TAGS			. ' t  ON tt.tag_id = t.id
					JOIN ' . FORUMS_TABLE								. " f  ON f.forum_id = topics.forum_id
				WHERE
					$sql_where_tag_in
					AND f.rh_topictags_enabled = 1
					AND $sql_where_topic_access
				GROUP BY topics.topic_id
				HAVING count(t.id) = $tag_count";
		}
		else
		{
			// OR mode, we produce: AND t.tag IN ('tag1', 'tag2', ...)
			$sql_array = array(
				'SELECT'	=> 'topics.*',
				'FROM'		=> array(
					TOPICS_TABLE							=> 'topics',
					$this->table_prefix . tables::TOPICTAGS	=> 'tt',
					$this->table_prefix . tables::TAGS		=> 't',
					FORUMS_TABLE							=> 'f',
				),
				'WHERE'		=> " $sql_where_tag_in
					AND topics.topic_id = tt.topic_id
					AND f.rh_topictags_enabled = 1
					AND f.forum_id = topics.forum_id
					AND $sql_where_topic_access
					AND t.id = tt.tag_id
				");
			$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);
		}
		return $sql;
	}

	/**
	 * Checks whether the given tag is blacklisted.
	 *
	 * @param string $tag
	 * @return boolean true, if the tag is on the blacklist, false otherwise
	 */
	private function is_on_blacklist($tag)
	{
		$blacklist = json_decode($this->config_text->get(prefixes::CONFIG.'_blacklist'), true);
		foreach ($blacklist as $entry)
		{
			if ($tag === $this->clean_tag($entry))
			{
				return true;
			}
		}

	}

	/**
	 * Checks whether the given tag is whitelisted.
	 *
	 * @param string $tag
	 * @return boolean true, if the tag is on the whitelist, false otherwise
	 */
	private function is_on_whitelist($tag)
	{
		$whitelist = $this->get_whitelist_tags();
		foreach ($whitelist as $entry)
		{
			if ($tag === $this->clean_tag($entry))
			{
				return true;
			}
		}
	}

	/**
	 * Gets all tags from the whitelist
	 */
	public function get_whitelist_tags()
	{
		return json_decode($this->config_text->get(prefixes::CONFIG . '_whitelist'), true);
	}

	/**
	 * Checks if the given tag matches the configured regex for valid tags, Note that the tag is trimmed to 30 characters before the check!
	 * This method also checks if the tag is whitelisted and/or blacklisted if the lists are enabled.
	 *
	 * @param $tag the tag to check
	 * @param $is_clean wether the tag has already been cleaned or not.
	 * @return true if the tag matches, false otherwise
	 */
	public function is_valid_tag($tag, $is_clean = false)
	{
		if (!$is_clean)
		{
			$tag = $this->clean_tag($tag);
		}

		$pattern = $this->config[prefixes::CONFIG.'_allowed_tags_regex'];
		$tag_is_valid = preg_match($pattern, $tag);

		if (!$tag_is_valid)
		{
			// non conform to regex is always invalid.
			return false;
		}

		// from here on: tag is regex conform

		// check blacklist
		if ($this->config[prefixes::CONFIG.'_blacklist_enabled'])
		{
			if ($this->is_on_blacklist($tag))
			{
				// tag is regex-conform, but blacklisted => invalid
				return false;
			}
			// regex conform and not blacklisted. => do nothing here
		}

		// here we know: tag is regex conform and not blacklisted or it's regex conform and the blacklist is disabled.

		// check whitelist
		if ($this->config[prefixes::CONFIG.'_whitelist_enabled'])
		{
			if ($this->is_on_whitelist($tag))
			{
				// tag is regex-conform not blacklisted and in the whitelist => valid
				return true;
			}
			// not on whitelist, but whitelist enabled => invalid
			return false;
		}

		// tag is regex conform, not blacklisted and the the whitelist is disabled => valid
		return true;
	}

	/**
	 * Splits the given tags into valid and invalid ones.
	 *
	 * @param $tags an array of potential tags
	 * @return array array('valid'=> array(), 'invalid' => array())
	 */
	public function split_valid_tags($tags)
	{
		$re = array(
			'valid' => array(),
			'invalid' => array()
		);
		foreach ($tags as $tag)
		{
			$tag = $this->clean_tag($tag);
			$type = $this->is_valid_tag($tag, true) ? 'valid' : 'invalid';
			$re[$type][] = $tag;
		}
		return $re;
	}

	/**
	 * Trims the tag to 30 characters and replaced spaces to "-" if configured.
	 *
	 * @param the tag to clean
	 * @return cleaned tag
	 */
	public function clean_tag($tag)
	{
		$tag = trim($tag);

		// db-field is max 30 characters!
		$tag = utf8_substr($tag, 0, 30);

		// might have a space at the end now, so trim again
		$tag = trim($tag);

		if ($this->config[prefixes::CONFIG.'_convert_space_to_minus'])
		{
			$tag = str_replace(' ', '-', $tag);
		}

		return $tag;
	}

	/**
	 * Checks if tagging is enabled in the given forum.
	 *
	 * @param $forum_id the id of the forum
	 * @return true if tagging is enabled in the given forum, false if not
	 */
	public function is_tagging_enabled_in_forum($forum_id)
	{
		$field = 'rh_topictags_enabled';
		$sql = "SELECT $field
			FROM " . FORUMS_TABLE . '
			WHERE ' . $this->db->sql_build_array('SELECT', array('forum_id' => (int) $forum_id));
		$status = (int) $this->db_helper->get_field($sql, $field);
		return $status > 0;
	}

	/**
	 * Enables tagging engine in all forums (not categories and links).
	 *
	 * @return number of affected forums (should be the count of all forums (type FORUM_POST ))
	 */
	public function enable_tags_in_all_forums()
	{
		return $this->set_tags_enabled_in_all_forums(true);
	}

	/**
	 * en/disables tagging engine in all forums (not categories and links).
	 *
	 * @param boolean $enable true to enable and false to disabl the engine
	 * @return number of affected forums (should be the count of all forums (type FORUM_POST ))
	 */
	private function set_tags_enabled_in_all_forums($enable)
	{
		$sql_ary =  array(
			'rh_topictags_enabled'	=> $enable ? 1 : 0
		);
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE forum_type = ' . FORUM_POST . '
				AND rh_topictags_enabled = ' . ($enable ? '0' : '1');
		$this->db->sql_query($sql);
		$affected_rows = $this->db->sql_affectedrows();
		$this->calc_count_tags();
		return (int) $affected_rows;
	}

	/**
	 * Disables tagging engine in all forums (not categories and links).
	 *
	 * @return number of affected forums (should be the count of all forums (type FORUM_POST ))
	 */
	public function disable_tags_in_all_forums()
	{
		return $this->set_tags_enabled_in_all_forums(false);
	}

	/**
	 * Checks if all forums have the given status of the tagging engine (enabled/disabled)
	 *
	 * @param boolean $status true to check for enabled, false to check for disabled engine
	 * @return boolean true if for all forums tagging is in state $status
	 */
	private function is_status_in_all_forums($status)
	{
		// there exist any which are disabled => is_enabled_in_all_forums == false
		$sql_array = array(
			'SELECT'	=> 'COUNT(*) as all_not_in_status',
			'FROM'		=> array(
				FORUMS_TABLE => 'f',
			),
			'WHERE'		=> 'f.rh_topictags_enabled = ' . ($status? '0' : '1') . '
				AND forum_type = ' . FORUM_POST,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$all_not_in_status = (int) $this->db_helper->get_field($sql, 'all_not_in_status');
		return $all_not_in_status == 0;
	}

	/**
	 * Checks if tagging is enabled or for all forums (not categories and links).
	 *
	 * @return true if for all forums tagging is enabled (type FORUM_POST ))
	 */
	public function is_enabled_in_all_forums()
	{
		return $this->is_status_in_all_forums(true);
	}

	/**
	 * Checks if tagging is disabled or for all forums (not categories and links).
	 *
	 * @return true if for all forums tagging is disabled (type FORUM_POST ))
	 */
	public function is_disabled_in_all_forums()
	{
		return $this->is_status_in_all_forums(false);
	}

	/**
	 * Count how often each tag is used (skipping the usage in tagging-disabled forums) and store it for each tag.
	 */
	public function calc_count_tags()
	{
		$sql_array = array(
			'SELECT'	=> 'id',
			'FROM'		=> array(
				$this->table_prefix . tables::TAGS		=> 't',
			),
		);
		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);
		$tag_ids = $this->db->sql_query($sql);

		while ($tag = $this->db->sql_fetchrow($tag_ids))
		{
			$tag_id = $tag['id'];
			$sql = 'SELECT COUNT(tt.id) as count
				FROM ' . TOPICS_TABLE . ' topics,
					' . FORUMS_TABLE . ' f,
					' . $this->table_prefix . tables::TOPICTAGS . ' tt
				WHERE tt.tag_id = ' . $tag_id . '
					AND topics.topic_id = tt.topic_id
					AND f.forum_id = topics.forum_id
					AND f.rh_topictags_enabled = 1';
			$this->db->sql_query($sql);
			$count = $this->db->sql_fetchfield('count');

			$sql = 'UPDATE ' . $this->table_prefix . tables::TAGS . '
				SET count = ' . $count . '
				WHERE id = ' . $tag_id;
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Gets the topic-ids that the given tag-id is assigned to.
	 *
	 * @param int $tag_id the id of the tag
	 * @return array array of ints (the topic-ids)
	 */
	private function get_topic_ids_by_tag_id($tag_id)
	{
		$sql_array = array(
			'SELECT'	=> 'tt.topic_id',
			'FROM'		=> array(
				$this->table_prefix . tables::TOPICTAGS	=> 'tt',
			),
			'WHERE'		=> 'tt.tag_id = ' . ((int) $tag_id),
		);
		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);
		return $this->db_helper->get_ids($sql, 'topic_id');
	}

	/**
	 * Merges two tags, by assigning all topics of tag_to_delete_id to the tag_to_keep_id and then deletes the tag_to_delete_id.
	 * NOTE: Both tags must exist and this is not checked again!
	 *
	 * @param int $tag_to_delete_id the id of the tag to delete
	 * @param string $tag_to_keep must be valid
	 * @param int $tag_to_keep_id the id of the tag to keep
	 * @return the new count of assignments of the kept tag
	 */
	public function merge($tag_to_delete_id, $tag_to_keep, $tag_to_keep_id)
	{
		$tag_to_delete_id = (int) $tag_to_delete_id;
		$tag_to_keep_id = (int) $tag_to_keep_id;

		// delete assignments where the new tag is already assigned
		$topic_ids_already_assigned = $this->get_topic_ids_by_tag_id($tag_to_keep_id);
		if (!empty($topic_ids_already_assigned))
		{
			$sql = 'DELETE FROM ' . $this->table_prefix . tables::TOPICTAGS. '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids_already_assigned) . '
					AND tag_id = ' . (int) $tag_to_delete_id;
			$this->db->sql_query($sql);
		}
		// renew assignments where the new tag is not assigned, yet
		$sql_ary = array(
			'tag_id'	=> $tag_to_keep_id,
		);
		$sql = 'UPDATE ' . $this->table_prefix . tables::TOPICTAGS . '
			SET  ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE tag_id = ' . (int) $tag_to_delete_id;
		$this->db->sql_query($sql);

		$this->delete_tag($tag_to_delete_id);
		$this->calc_count_tags();
		return $this->count_topics_by_tags(array($tag_to_keep), 'AND', true);
	}

	/**
	 * Deletes the given tag and all its assignments.
	 *
	 * @param int $tag_id
	 */
	public function delete_tag($tag_id)
	{
		$sql = 'DELETE FROM ' . $this->table_prefix . tables::TOPICTAGS . '
			WHERE tag_id = ' . ((int) $tag_id);
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->table_prefix . tables::TAGS . '
			WHERE id = ' . ((int) $tag_id);
		$this->db->sql_query($sql);
	}

	/**
	 * Renames the tag
	 *
	 * @param int $tag_id the id of the tag
	 * @param string $new_name_clean the new name of the tag already cleaned
	 * @return int the count of topics that are assigned to the tag
	 */
	public function rename($tag_id, $new_name_clean)
	{
		$sql_ary = array(
			'tag'			=> $new_name_clean,
			'tag_lowercase'	=> utf8_strtolower($new_name_clean),
		);
		$sql = 'UPDATE ' . $this->table_prefix . tables::TAGS . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE id = ' . ((int) $tag_id);
		$this->db->sql_query($sql);
		return $this->count_topics_by_tags(array($new_name_clean), 'AND', true);
	}

	/**
	 * Gets the corresponding tag by its id
	 *
	 * @param int $tag_id the id of the tag
	 * @return string the tag name
	 */
	public function get_tag_by_id($tag_id)
	{
		$sql_array = array(
			'SELECT'	=> 't.tag',
			'FROM'		=> array(
				$this->table_prefix . tables::TAGS		=> 't',
			),
			'WHERE'		=> 't.id = ' . ((int) $tag_id),
		);
		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);
		return $this->db_helper->get_field($sql, 'tag', 1);
	}

	/**
	 * Gets all tags.
	 *
	 * @param $start start for sql query
	 * @param $limit limit for sql query
	 * @param $sort_field the db field to order by
	 * @param $asc order direction (true == asc, false == desc)
	 * @return array array of tags
	 */
	public function get_all_tags($start, $limit, $sort_field = 'tag', $asc = true)
	{
		switch ($sort_field)
		{
			case 'count':
				$sort_field = 'count';
				break;
			case 'tag':
				// no break
			default:
				$sort_field = 'tag';
		}
		$direction = $asc ? 'ASC' : 'DESC';
		$sql = 'SELECT * FROM ' . $this->table_prefix . tables::TAGS . '
			ORDER BY ' . $sort_field . ' ' . $direction;
		$field_names = array(
			'id',
			'tag',
			'tag_lowercase',
			'count'
		);
		return $this->db_helper->get_multiarray_by_fieldnames($sql, $field_names, $limit, $start);
	}

	/**
	 * Gets the count of all tags.
	 *
	 * @return int the count of all tags
	 */
	public function count_tags()
	{
		$sql = 'SELECT COUNT(*) as count_tags FROM ' . $this->table_prefix . tables::TAGS;
		return (int) $this->db_helper->get_field($sql, 'count_tags');
	}
}
