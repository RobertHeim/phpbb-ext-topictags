phpbb-ext-topictags
===================

phpBB 3.1 extension, that adds the ability to tag topics with key words.

## Features

* enable tagging of topics on a per forum basis
* add tags when posting a new topic
* edit tags when editing first post of topic
* search topics by tag(s)
* /tags shows a tag-cloud
* /tag/{tags}/{mode} shows topics tagged with all (mode=AND, default) or any (mode=OR) of the given tags, where tags are comma separated tags, e.g.:
  * /tag/tag1,tag2/OR lists all topics that are tagged with tag1 OR tag2
  * /tag/tag1,tag2/AND lists all topics that are tagged with tag1 AND tag2
  * /tag/tag1,tag2 lists all topics that are tagged with tag1 AND tag2 (mode=defaul=AND)

## Installation

### 1. clone
Clone (or download an move) the repository into the folder phpBB3/ext/robertheim/threadtags:

```
cd phpBB3
git clone https://github.com/RobertHeim/phpbb-ext-topictags.git ext/robertheim/topictags/
```

### 2. activate
Go to admin panel -> tab customise -> Manage extensions -> enable RH Topic Tags

### 3. configure

## Support

https://www.phpbb.com/community/viewtopic.php?f=456&t=2263616
