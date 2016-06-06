[![Build Status](https://travis-ci.org/RobertHeim/phpbb-ext-topictags.svg?branch=3.1.x)](https://travis-ci.org/RobertHeim/phpbb-ext-topictags)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RobertHeim/phpbb-ext-topictags/badges/quality-score.png?b=3.1.x)](https://scrutinizer-ci.com/g/RobertHeim/phpbb-ext-topictags/?branch=3.1.x)

phpbb-ext-topictags
===================

phpBB 3.1 extension, that adds the ability to tag topics with key words.

## Features

### Common

* add tags when posting a new topic
* Tag suggestions based on existing tags
* edit tags when editing first post of topic
* SEO-optimization: tags are added to meta-content keywords in viewtopic
* tags are shown in viewforum (can be disabled in acp)
* enable tagging of topics on a per forum basis
* Responsive layout
* Full UTF-8 support

### Search
* search topics by tag(s)
* /tag/{tags}/{mode}/{casesensitive} shows topics tagged with all (mode=AND, default) or any (mode=OR) of the given tags, where tags are comma separated tags and casesensitive can be true to search case-sensitive or false (default), e.g.:
  * */tag/tag1,tag2/OR* lists topics that are tagged with tag1 OR tag2 OR tAG2
  * */tag/tag1,tag2/AND* lists topics that are tagged with \[tag1 AND (tag2 OR tAG2)\]
  * */tag/tag1,tag2* lists topics that are tagged with \[tag1 AND (tag2 OR tAG2)\] (mode=default=AND, casesensitive=default=false)
  * */tag/tag1,tAG2/AND/true* lists topics that are tagged with (tag1 AND tAG2)

### Tag-Cloud
* /tags shows a tag cloud
* acp option for tag cloud to be displayed on board-index or not
* acp option to limit count of tags shown in tag cloud on the index page
* dynamic tag-size in tag cloud depending on its usage count
* acp option to en/disable display of usage count of tags in tag cloud

### Advanced configuration
* configure a regex to decide which tags are valid and which are not
* maintenance functions in ACP -> Extensions -> RH Topic Tags
* Whitelist
* Blacklist
* User and Mod+Admin permission for who can add/edit RH topic tags
* spaces in tags are converted to "-" by default (you can disable it in ACP)
* Manage existing tags in ACP
  * Delete tag
  * Rename tag
  * Merge tags (rename one tag to the same name as another tag and they will automatically be merged )

## Installation

### 1. clone
Clone (or download an move) the repository into the folder phpBB3/ext/robertheim/topictags:

```
cd phpBB3
git clone https://github.com/RobertHeim/phpbb-ext-topictags.git ext/robertheim/topictags/
```

### 2. activate
Go to ACP -> tab Customise -> Manage extensions -> enable RH Topic Tags
Go to ACP -> Forums -> edit/create any forum -> set *Enable RH Topic Tags* to *Yes*

### 3. configure

Goto ACP -> Extensions -> RH Topic Tags

## Update

Go to ACP -> tab Customise -> Manage extensions -> disable RH Topic Tags

```
cd phpBB3/ext/robertheim/topictags
git pull
```

Go to ACP -> tab Customise -> Manage extensions -> enable RH Topic Tags

## Support

https://www.phpbb.com/community/viewtopic.php?f=456&t=2263616
