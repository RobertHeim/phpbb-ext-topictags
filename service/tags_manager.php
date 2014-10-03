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
use robertheim\topictags\TABLES;
use robertheim\topictags\PREFIXES;

class tags_manager
{

	private $db;
	private $config;
	private $auth;
	private $table_prefix;

	public function __construct(
					\phpbb\db\driver\driver_interface $db,
					\phpbb\config\config $config,
					\phpbb\auth\auth $auth,
					$table_prefix)
	{
		$this->db			= $db;
		$this->config		= $config;
		$this->auth			= $auth;
		$this->table_prefix	= $table_prefix;
	}

    /**
     * Remove all tags from the given topic
	 *
	 * @param $topic_id
	 * @param $delete_unused_tags if set to true unsued tags are removed from the db.
	 */
	public function remove_all_tags_from_topic($topic_id, $delete_unused_tags = true)
	{
		// remove tags from topic
		$sql = 'DELETE FROM ' . $this->table_prefix . TABLES::TOPICTAGS. '
				WHERE topic_id = ' . (int) $topic_id;
		$this->db->sql_query($sql);
		if ($delete_unused_tags) {
			$this->delete_unused_tags();
		}
		$this->calc_count_tags();
	}

	/**
	 * Removes all tags that are not assigned to at least one topic (garbage collection).
	 *
	 * @return count of deleted tags
	 */
	public function delete_unused_tags()
	{
		// TODO maybe we are not allowed to use subqueries, because some DBALS supported by phpBB do not support them.
		// https://www.phpbb.com/community/viewtopic.php?f=461&t=2263646
		// so we would need 2 queries, but this is slow... so we use subqueries and hope - yeah! :D

		$sql = 'DELETE t FROM ' . $this->table_prefix . TABLES::TAGS . ' t
				WHERE NOT EXISTS (
					SELECT 1
					FROM ' . $this->table_prefix . TABLES::TOPICTAGS . ' tt
					WHERE tt.tag_id = t.id
				)';

		$this->db->sql_query($sql);
		return $this->db->sql_affectedrows();
	}

	/**
	 * Deletes all assignments of tags, that are no longer valid
	 *
	 * @return count of removed assignments
	 */
	public function delete_assignments_of_invalid_tags()
	{
		// get all tags to check them
		$tags =  $this->get_existing_tags(null);

		$ids_of_invalid_tags_ = array();
		foreach ($tags as $tag)
		{
			if (!$this->is_valid_tag($tag['tag']))
			{
				$ids_of_invalid_tags[] = (int) $tag['id'];
			}
		}
		// delete all tag-assignments where the tag is not valid
		$sql = 'DELETE tt FROM ' . $this->table_prefix . TABLES::TOPICTAGS . ' tt
				WHERE tt.tag_id IN (' . join(',', $ids_of_invalid_tags) . ')';
		$this->db->sql_query($sql);
		$removed_count = $this->db->sql_affectedrows();

		$this->calc_count_tags();

		return $removed_count;
	}

	/**
	 * Removes all topic-tag-assignments where the topic does not exist anymore.
	 *
	 * @return count of deleted assignments
	 */
	public function delete_assignments_where_topic_does_not_exist()
	{
		// delete all tag-assignments where the topic does not exist anymore
		$sql = 'DELETE tt FROM ' . $this->table_prefix . TABLES::TOPICTAGS . ' tt
				WHERE NOT EXISTS (
					SELECT 1 FROM ' . TOPICS_TABLE . ' topics
					WHERE topics.topic_id = tt.topic_id
				)';
		$this->db->sql_query($sql);
		$removed_count = $this->db->sql_affectedrows();

		$this->calc_count_tags();

		return $removed_count;
	}

	/**
	 * Deletes all topic-tag-assignments where the topic resides in a forum with tagging disabled.
	 *
	 * @param $forum_ids array of forum-ids that should be checked (if null, all are checked).
	 * @return count of deleted assignments
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
			// ensure forum_ids are ints before using them in sql
			$int_ids = array();
			foreach ($forum_ids as $id)
			{
				$int_ids[] = (int) $id;
			}
			$forums_sql_where = ' AND f.forum_id IN (' . join(',', $int_ids) . ')';
		}
		// Deletes all topic-assignments to topics that reside in a forum with tagging disabled.
		$sql = 'DELETE tt FROM ' . $this->table_prefix . TABLES::TOPICTAGS . ' tt
				WHERE EXISTS (
					SELECT 1
					FROM ' . TOPICS_TABLE . ' topics,
						' . FORUMS_TABLE . ' f
					WHERE topics.topic_id = tt.topic_id
						AND f.forum_id = topics.forum_id
						AND f.rh_topictags_enabled = 0
						' . $forums_sql_where . '
				)';
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
		$result = $this->db->sql_query('SELECT t.tag FROM
				' . $this->table_prefix . TABLES::TAGS . ' AS t, 
				' . $this->table_prefix . TABLES::TOPICTAGS . ' AS tt
			WHERE tt.topic_id = ' . (int) $topic_id.'
				AND t.id = tt.tag_id');
		$tags = array();
        while ($row = $this->db->sql_fetchrow($result))
		{
			$tags[] = $row['tag'];
		}
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
		
		// create topic_id <->tag_id link in TOPICTAGS_TABLE
		foreach ($ids as $id)
		{
			$sql_ary[] = array(
				'topic_id'	=> $topic_id,
				'tag_id'	=> $id
			);
		}
		$this->db->sql_multi_insert($this->table_prefix . TABLES::TOPICTAGS, $sql_ary);

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

		$existing_tags = $this->get_existing_tags($tags);

		// find all tags that are not in $existing_tags and add them to $sql_ary_new_tags
		$sql_ary_new_tags = array();
		foreach ($tags as $tag)
		{
			if (!$this->in_array_r($tag, $existing_tags)) {
				// tag needs to be created
				$sql_ary_new_tags[] = array('tag' => $tag);
			}
		}

		// create the new tags
		$this->db->sql_multi_insert($this->table_prefix . TABLES::TAGS, $sql_ary_new_tags);
	}

	/**
	 * Recursive in_array to check if the given (eventually multidimensional) array $haystack contains $needle.
	 */
	private function in_array_r($needle, $haystack, $strict = false)
	{
		foreach ($haystack as $item)
		{
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
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
	 * @return array(array('id'=>.. , 'tag'=> ..), array('id'=>.. , 'tag'=> ..), ...) or array(1,2,3,..) if $only_ids==true
	 */
	public function get_existing_tags($tags = null, $only_ids = false)
	{
		$where = "";
		if ($tags)
		{
			if (empty($tags))
			{
				// ensure that empty input array results in empty output array.
				// note that this case is different from $tags == null where we want to get ALL existing tags.
				return array();
			}
			// prepare tags for sql-where-in ('tag1', 'tag2', ...)
			$sql_tags = array();
			foreach ($tags as $tag) {
				$sql_tags[] = "'" . $this->db->sql_escape($tag) . "'";
			}
			$sql_tags = join(",", $sql_tags);
			$where = ' WHERE tag IN (' . $sql_tags . ')';
		}
		$result = $this->db->sql_query('SELECT id, tag FROM ' . $this->table_prefix . TABLES::TAGS . $where);

		$existing_tags = array();
		if ($only_ids)
		{
	        while ($row = $this->db->sql_fetchrow($result))
			{
				$existing_tags[] = $row['id'];
			}
		}
		else
		{
	        while ($row = $this->db->sql_fetchrow($result))
			{
				$existing_tags[] = array(
					'id'	=> $row['id'],
					'tag'	=> $row['tag']
				);
			}
		}
		return $existing_tags;
	}

	/**
	 * Gets the ids of all tags that are used.
	 *
	 * @return array of ids
	 */
	private function get_used_tag_ids()
	{
		$result = $this->db->sql_query('SELECT DISTINCT tag_id FROM ' . $this->table_prefix . TABLES::TOPICTAGS);
		$ids = array();
        while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = $row['tag_id'];
		}
		return $ids;
	}

	/**
	 * Gets the topics which are tagged with any or all of the given $tags from all forums, where tagging is enabled and only those which the user is allowed to read.
	 *
	 * @param $start start for sql query
	 * @param $limit limit for sql query
	 * @param $tags the tag to find the topics for
	 * @param $mode AND=all tags must be assigned, OR=at least one tag needs to be assigned
	 * @return array of topics, each containing all fields from TOPIC_TABLE
	 */
	public function get_topics_by_tags($tags, $start, $limit, $mode = "AND", $casesensitive = false)
	{
		$sql = $this->get_topics_build_query($tags, $mode, $casesensitive);

		$order_by = ' ORDER BY topics.topic_last_post_time DESC';
		$sql .= $order_by;

		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$topics = array();
        while ($row = $this->db->sql_fetchrow($result))
		{
			$topics[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $topics;
	}

	/**
	 * Gets the topics which are tagged with any or all of the given $tags from all forums, where tagging is enabled and only those which the user is allowed to read.
	 *
	 * @param $start start for sql query
	 * @param $limit limit for sql query
	 * @param $tags the tag to find the topics for
	 * @param $mode AND=all tags must be assigned, OR=at least one tag needs to be assigned
	 * @return array of topics, each containing all fields from TOPIC_TABLE
	 */
	public function count_topics_by_tags($tags, $mode = "AND", $casesensitive = false)
	{
		$sql = $this->get_topics_build_query($tags, $mode, $casesensitive);

		$sql = "SELECT COUNT(*) as total_results FROM ($sql) a";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('total_results');
		$this->db->sql_freeresult($result);
		return $count;
	}

	private function get_topics_build_query($tags, $mode = "AND", $casesensitive = false)
	{
		if (empty($tags))
		{
			return array();
		}

		// validate mode
		$mode = ($mode == 'OR' ? 'OR' : 'AND');

		$escaped_tags = array();
		foreach ($tags as $tag)
 		{
			if (!$casesensitive)
			{
				$tag = mb_strtolower($tag);
			}
			$escaped_tags[] = "'" . $this->db->sql_escape($tag) . "'";
		}
		
		// Get forums that the user is allowed to read
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
		
		// Get sql-source for the topics that reside in forums that the user can read and which are approved.
		$sql_where_topic_access = $this->db->sql_in_set('topics.forum_id', $forum_ary, false, true);
		$sql_where_topic_access .= ' AND topics.topic_visibility = 1';

		$sql_where_tag_in = $casesensitive ? ' t.tag' : 'LOWER(t.tag)';
		$sql_where_tag_in .= ' IN (' . join(',', $escaped_tags) . ')';

		$sql = '';
		if ('AND' == $mode)
		{
			// http://stackoverflow.com/questions/26038114/sql-select-distinct-where-exist-row-for-each-id-in-other-table
			$tag_count = sizeof($tags);
			$sql = 'SELECT topics.*
				FROM 	' . TOPICS_TABLE								. ' topics
					JOIN ' . $this->table_prefix . TABLES::TOPICTAGS	. ' tt ON tt.topic_id = topics.topic_id
					JOIN ' . $this->table_prefix . TABLES::TAGS			. ' t  ON tt.tag_id = t.id
					JOIN ' . FORUMS_TABLE								. ' f  ON f.forum_id = topics.forum_id
				WHERE
					' . $sql_where_tag_in . '
					AND f.rh_topictags_enabled = 1
					AND ' . $sql_where_topic_access . '
				GROUP BY topics.topic_id
				HAVING count(t.id) = ' . $tag_count;
		}
		else
		{
			// OR mode, we produce: AND t.tag IN ('tag1', 'tag2', ...)
			$sql_array = array(
				'SELECT'	=> 'topics.*',
				'FROM'		=> array(
					TOPICS_TABLE							=> 'topics',
					$this->table_prefix . TABLES::TOPICTAGS	=> 'tt',
					$this->table_prefix . TABLES::TAGS		=> 't',
					FORUMS_TABLE							=> 'f',
				),
				'WHERE'		=> '
					' . $sql_where_tag_in . '
					AND topics.topic_id = tt.topic_id
					AND f.rh_topictags_enabled = 1
					AND f.forum_id = topics.forum_id
					AND ' . $sql_where_topic_access . '
					AND t.id = tt.tag_id
					');
			$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);
		}
		return $sql;
	}

	/**
	 * Checks if the given tag matches the configured regex for valid tags, Note that the tag is trimmed to 30 characters before the check!
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
		$pattern = $this->config[PREFIXES::CONFIG.'_allowed_tags_regex'];
		return preg_match($pattern, $tag);
	}

	/**
	 * Splits the given tags into valid and invalid ones.
	 *
	 * @param $tags an array of potential tags
	 * @return array('valid'=> array(), 'invalid' => array())
	 */
	public function split_valid_tags($tags)
	{
		$re = array(
			'valid' => array(),
			'invalid' => array()
		);
		foreach ($tags as $tag) {
			$tag = $this->clean_tag($tag);
			$type = $this->is_valid_tag($tag, true) ? 'valid' : 'invalid';
			$re[$type][] = $tag;
		}
		return $re;
	}

	/**
	 * Trims the tag to 30 characters.
	 *
	 * @param the tag to clean
	 * @return cleaned tag
	 */
	public function clean_tag($tag)
	{
		$tag = trim($tag);

		// db-field is max 30 characters!
		$tag = substr($tag, 0, 30);

		//might have a space at the end now, so trim again
		$tag = trim($tag);

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
		$result = $this->db->sql_query($sql);
		return (int) $this->db->sql_fetchfield($field);	
	}

	/**
	 * Enables tagging engine in all forums (not categories and links).
	 *
	 * @return number of affected forums (should be the count of all forums (type FORUM_POST ))
	 */
	public function enable_tags_in_all_forums()
	{
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', array(
				'rh_topictags_enabled'	=> 1
				)) . '
			WHERE forum_type = ' . FORUM_POST . '
				AND rh_topictags_enabled = 0';
		$this->db->sql_query($sql);
		return $this->db->sql_affectedrows();
	}

	/**
	 * Disables tagging engine in all forums (not categories and links).
	 *
	 * @return number of affected forums (should be the count of all forums (type FORUM_POST ))
	 */
	public function disable_tags_in_all_forums()
	{
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', array(
				'rh_topictags_enabled'	=> 0
				)) . '
			WHERE forum_type = ' . FORUM_POST . '
				AND rh_topictags_enabled = 1';
		$this->db->sql_query($sql);
		return $this->db->sql_affectedrows();
	}

	/**
	 * Checks if tagging is enabled or for all forums (not categories and links).
	 *
	 * @return true if for all forums tagging is enabled (type FORUM_POST ))
	 */
	public function is_enabled_in_all_forums()
	{
		// there exist any which are disabled => is_enabled_in_all_forums == false
		$sql_array = array(
			'SELECT'	=> 'COUNT(*) as all_enabled',
			'FROM'      => array(
				FORUMS_TABLE => 'f',
			),
			'WHERE'		=> 'f.rh_topictags_enabled = 0
				AND forum_type = ' . FORUM_POST,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$this->db->sql_query($sql);
		return ((int) $this->db->sql_fetchfield('all_enabled')) == 0;
	}

	/**
	 * Checks if tagging is disabled or for all forums (not categories and links).
	 *
	 * @return true if for all forums tagging is disabled (type FORUM_POST ))
	 */
	public function is_disabled_in_all_forums()
	{
		// there exist any which are enabled => is_disabled_in_all_forums == false
		$sql_array = array(
			'SELECT'	=> 'COUNT(*) as all_disabled',
			'FROM'      => array(
				FORUMS_TABLE => 'f',
			),
			'WHERE'		=> 'f.rh_topictags_enabled = 1
				AND forum_type = ' . FORUM_POST,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$this->db->sql_query($sql);
		return ((int) $this->db->sql_fetchfield('all_disabled')) == 0;
	}

	/**
	 * Count how often each tag is used and store it for each tag.
	 */
	public function calc_count_tags()
	{
		$sql = 'UPDATE ' . $this->table_prefix . TABLES::TAGS . ' t
				SET t.count = (
					SELECT COUNT(tt.id)
					FROM ' . $this->table_prefix . TABLES::TOPICTAGS . ' tt
					WHERE tt.tag_id = t.id)';
		$this->db->sql_query($sql);
	}

}

