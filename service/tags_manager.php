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

class tags_manager
{

	private $db;
	private $table_prefix;

	public function __construct(\phpbb\db\driver\driver $db, $table_prefix)
	{
		$this->db			= $db;
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
				WHERE topic_id = '.$topic_id;
		$this->db->sql_query($sql);
		if ($delete_unused_tags) {
			$this->delete_unused_tags();
		}
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
		return $this->db->sql_affectedrows();
	}

	/**
	 * Deletes all topic-tag-assignments where the topic resides in a forum with tagging disabled.
	 *
	 * @return count of deleted assignments
	 */
	public function delete_tags_from_tagdisabled_forums()
	{
		// Deletes all topic-assignments to topics that reside in a forum with tagging disabled.
		$sql = 'DELETE tt FROM ' . $this->table_prefix . TABLES::TOPICTAGS . ' tt
				WHERE EXISTS (
					SELECT 1
					FROM ' . TOPICS_TABLE . ' topics,
						' . FORUMS_TABLE . ' f
					WHERE topics.topic_id = tt.topic_id
						AND f.forum_id = topics.forum_id
						AND f.rh_topictags_enabled = 0
				)';
		$this->db->sql_query($sql);
		return $this->db->sql_affectedrows();
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
			WHERE tt.topic_id = '.$topic_id.'
				AND t.id = tt.tag_id');
		$tags = array();
        while ($row = $this->db->sql_fetchrow($result))
		{
			$tags[] = $row['tag'];
		}
		return $tags;
	}

    /**
     * Assigns the topic exactly the given tags (all other tags are removed from the topic and if a tag does not exist yet, it will be created).
	 *
	 * @param $topic_id
	 * @param $tags			array containing tag-names
	 */
	public function assign_tags_to_topic($topic_id, $tags)
	{
		$topic_id = (int) $topic_id;

		$this->remove_all_tags_from_topic($topic_id, false);
		$this->create_missing_tags($tags);

		// get ids of tags
		$ids = $this->get_existing_tags($tags, true);
		
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
    }

	/**
	 * Finds whether the given tags already exist and if not creates them in the db.
	 */
	private function create_missing_tags($tags)
	{
		// we will get all existing tags of $tags
		// and then substract these from $tags
		// result contains th tags that needs to be created
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
	// TODO test if in_array_r is working
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
			// prepare tags for sql-where-in ('tag1', 'tag2', ...)
			$sql_tags = array();
			foreach ($tags as $tag) {
				$sql_tags[] = "'".$this->db->sql_escape($tag)."'";
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
	 * Gets the topics which are tagged with any or all of the given $tags
	 *
	 * @param $tags the tag to find the topics for
	 * @param $is_clean if true the tag is not cleaned again
	 * @param $mode AND=all tags must be assigned, OR=at least one tag needs to be assigned
	 * @return array of topics, each containing all fields from TOPIC_TABLE
	 */
	public function get_topics_by_tags($tags, $is_clean = false, $mode = 'AND')
	{
		if (!$is_clean)
		{
			$tags = $this->clean_tags($tags);
		}

		if (empty($tags))
		{
			return array();
		}

		// validate mode
		$mode = $mode == 'OR' ? 'OR' : 'AND';

		$escaped_tags = array();
		foreach ($tags as $tag)
		{
			$escaped_tags[] = "'" . $this->db->sql_escape($tag) . "'";
		}

		$sql = '';
		if ('AND' == $mode) {
			// http://stackoverflow.com/questions/26038114/sql-select-distinct-where-exist-row-for-each-id-in-other-table
			$tag_count = sizeof($tags);
			$sql = 'SELECT topics.*
				FROM '.TOPICS_TABLE.' topics
					JOIN ' . $this->table_prefix . TABLES::TOPICTAGS	. ' tt ON tt.topic_id = topics.topic_id
					JOIN ' . $this->table_prefix . TABLES::TAGS			. ' t  ON tt.tag_id = t.id
				WHERE t.tag IN ('.join(",", $escaped_tags).')
				GROUP BY topics.topic_id
				HAVING count(t.id) = '.$tag_count ;
				$where_sql = '';
		} else {
			// OR mode, we produce: AND t.tag IN ('tag1', 'tag2', ...)
			$sql_array = array(
				'SELECT'	=> 'topics.*',
				'FROM'		=> array(
					TOPICS_TABLE							=> 'topics',
					$this->table_prefix . TABLES::TOPICTAGS	=> 'tt',
					$this->table_prefix . TABLES::TAGS		=> 't',
				),
				'WHERE'		=> 'topics.topic_id = tt.topic_id
					AND t.id = tt.tag_id
					AND t.tag IN (' . join(",", $escaped_tags) . ')');
			$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);
		}
		$result = $this->db->sql_query($sql);
		$topics = array();
        while ($row = $this->db->sql_fetchrow($result))
		{
			$topics[] = $row;
		}
		return $topics;
	}

	/**
	 * Cleans the given tags, see $this->clean_tag($tag) for details.
	 *
	 * @param $tags array of tags to clean
	 * @return array containing the cleaned tags
	 */
	public function clean_tags(array $tags)
	{
		$clean_tags_ary = array();
		foreach ($tags as $tag) {
			$tag = $this->clean_tag($tag);
			if (!empty($tag))
			{
				$clean_tags_ary[] = $tag;
			} 
		}
		return $clean_tags_ary;
	}

	/**
	 * trims and shortens the given tag to 30 characters, trims it again and makes it lowercase.
	 *
	 * TODO remove unallowed characters before trim
	 *
	 * @param $tag the tag to clean
	 * @return the clean tag
	 */
	public function clean_tag($tag)
	{
		$tag = trim($tag);
		// max 30 length
		$tag = substr($tag, 0,30);

		//might have a space at the end now, so trim again
		$tag = trim($tag);

		// lowercase
		$tag = mb_strtolower($tag, 'UTF-8');
		return $tag;
	}

}
