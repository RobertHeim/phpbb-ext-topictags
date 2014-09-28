phpbb-ext-topictags
===================

phpBB 3.1 extension, that adds the ability to tag topics with key words.

## Features

* enable tagging of topics on a per forum basis
* add tags when posting a new topic
* edit tags when editing first post of topic
* search topics by tag(s)
* /tags shows a tag-cloud
* /tag/{tags}/{mode}/{casesensitive} shows topics tagged with all (mode=AND, default) or any (mode=OR) of the given tags, where tags are comma separated tags and casesensitive can be true to search case-sensitive or false (default), e.g.:
  * */tag/tag1,tag2/OR* lists topics that are tagged with tag1 OR tag2 OR tAG2
  * */tag/tag1,tag2/AND* lists topics that are tagged with [tag1 AND (tag2 OR tAG2)]
  * */tag/tag1,tag2 lists* topics that are tagged with [tag1 AND (tag2 OR tAG2)] (mode=default=AND, casesensitive=default=false)
  * */tag/tag1,tAG2/AND/true* lists topics that are tagged with (tag1 AND tAG2)
* configure a regex to decide which tags are valid and which are not
* maintenance functions in ACP -> Extensions -> RH Topic Tags
* tags are added to meta-content keywords in viewtopic

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

Goto ACP -> Extensions -> RH Topic Tags

## Support

https://www.phpbb.com/community/viewtopic.php?f=456&t=2263616
