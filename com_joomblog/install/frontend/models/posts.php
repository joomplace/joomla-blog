<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class JoomblogModelPosts extends JModelList
{

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'catid', 'a.catid', 'category_title', 'tag_name', 'tag_id',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'ordering', 'a.created', 'a.id',
                'language', 'a.language',
                'hits', 'a.hits',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down', 'search',
            );
        }
        parent::__construct($config);
    }

    public function setListLimit($limit)
    {
        $this->setState('list.limit', $limit);
    }

    public function setLimitstart($limitstart)
    {
        $this->setState('list.start', $limitstart);
    }

    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        global $_JB_CONFIGURATION;

        $app = JFactory::getApplication();
        // List state information
        $value = JFactory::getApplication()->input->get('limit', $_JB_CONFIGURATION->get('numEntry'), 'UINT');
        $this->setState('list.limit', $value);

        $value = JFactory::getApplication()->input->get('limitstart', 0, 'UINT');
        $this->setState('list.start', $value);

        $orderCol = JFactory::getApplication()->input->get('filter_order', 'a.ordering', 'CMD');
        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'a.created';
        }
        $this->setState('list.ordering', $orderCol);

        $listOrder = JFactory::getApplication()->input->get('filter_order_Dir', 'DESC', 'CMD');
        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'DESC';
        }
        $this->setState('list.direction', $listOrder);

        $user = JFactory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_joomblog')) && (!$user->authorise('core.edit', 'com_joomblog'))) {
            // filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', 1);
        }

        $this->setState('filter.language', $app->getLanguageFilter());

        //setting vars from tasks
        $tag = JFactory::getApplication()->input->get('tag', '', 'string');
        if (!empty($tag)) {
            $this->setState('filter.tag_name', $tag);
        }
        $category = JFactory::getApplication()->input->get('category', JFactory::getApplication()->getParams()->get('category', 0), 'string');

        if (!empty($category)) {
            if (is_numeric($category)) {
                $category = strval(urldecode($category));
                $category = str_replace("+", " ", $category);
                $searchby['jcategory'] = $category;
                $this->setState('filter.category_id', $category);
            } else {
                $category = strval(urldecode($category));
                $category = str_replace("+", " ", $category);
                $searchby['category'] = $category;
                $this->setState('filter.category_title', $category);
            }
        }

        $archive = JFactory::getApplication()->input->get('archive', '');
        if (!empty($archive)) {
            $archive = urldecode($archive);
            $archive = explode('-', $archive);
            $this->setState('filter.created_year', trim($archive[0]));
            if (!empty($archive[1])) $this->setState('filter.created_month', trim($archive[1]));
        }
        $authorId = JFactory::getApplication()->input->get('user', '');
        if (!empty($authorId)) {
            $this->setState('filter.author_id', is_string($authorId) ? jbGetAuthorId(urldecode($authorId)) : intval($authorId));
        }
        $this->setState('view', JFactory::getApplication()->input->get('view', ''));

        $blogid = JFactory::getApplication()->input->get('blogid', 0, 'INT');
        if (!empty($blogid)) {
            $this->setState('filter.blogid', $blogid);
        }

        $viewer = JFactory::getApplication()->input->get('viewer', 0, 'INT');
        if (!empty($viewer)) {
            $hash = JFactory::getApplication()->input->get('hash', '');
            if (!empty($hash) && md5('(n&(#$m2l()UBN34' . $viewer . '35rf&#$()gFS#E6^@#23') == $hash) {
                $this->setState('filter.access_viewer', $viewer);
            }
        }

    }

    public function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.alias, a.introtext,  a.fulltext, a.defaultimage, ' .
                'a.catid, a.created, a.created_by, ' .
                // use created if modified is 0
                'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified, ' .
                'a.modified_by, ' .
                // use created if publish_up is 0
                'CASE WHEN a.publish_up = 0 THEN a.created ELSE a.publish_up END as publish_up,' .
                'a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
                'a.hits,' . ' ' . $query->length('a.fulltext') . ' AS readmore'
            )
        );

        // Process an Archived Article layout
        if ($this->getState('filter.published') == 2) {
            // If badcats is not null, this means that the article is inside an archived category
            // In this case, the state is set to 2 to indicate Archived (even if the article state is Published)
            $query->select($this->getState('list.select', 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END AS state'));
        } else {
            // Process non-archived layout
            // If badcats is not null, this means that the article is inside an unpublished category
            // In this case, the state is set to 0 to indicate Unpublished (even if the article state is Published)
            $query->select($this->getState('list.select', 'CASE WHEN badcats.id is not null THEN 0 ELSE a.state END AS state'));
        }

        $query->where('a.state=1');

        $query->from('#__joomblog_posts AS a');

        //Select votes - nneds recheck
        $query->select(" (( SELECT COUNT(v2.vote) FROM #__joomblog_votes as v2 WHERE v2.vote = 1 AND v2.contentid = a.id ) - ( SELECT COUNT(v1.vote) FROM #__joomblog_votes as v1 WHERE v1.vote = -1 AND v1.contentid = a.id )) as sumvote ");

        //Join over the blogs
        $query->select('b.blog_id as blogid, lb.title as blogtitle');
        $query->join('LEFT', '#__joomblog_blogs AS b ON b.content_id = a.id');
        $query->join('LEFT', '#__joomblog_list_blogs AS lb ON lb.id = b.blog_id');

        if ($this->getState('filter.blogid')) {
            $query->where('b.blog_id = ' . $db->quote($this->getState('filter.blogid')));
        }

        // Join over the categories.

        //$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias');
        $query->join('LEFT', '#__joomblog_multicats AS mc ON mc.aid = a.id');
        $query->join('LEFT', '#__categories AS c ON c.id = mc.cid');

        //Join and filter by tags
        if ($this->getState('filter.tag_id') || $this->getState('filter.tag_name')) {
            $query->join('LEFT', '#__joomblog_content_tags AS jct ON jct.contentid = a.id');
            $query->join('LEFT', '#__joomblog_tags AS jt ON jt.id = jct.tag');
        }

        if ($tag_name = $this->getState('filter.tag_name')) {
            $tag_name = urldecode($tag_name);
            $tag_name = $db->escape(trim($tag_name));
            $tag_name = str_replace(' ', '%', $tag_name);
            $query->where('jt.name LIKE ' . $db->quote($tag_name));
        }
        if ($tag_id = $this->getState('filter.tag_id')) {
            $tag_id = $db->escape($tag_id);
            $query->where('jct.tag = ' . $db->quote($tag_id));
        }
        // Join over the users for the author and modified_by names.
        //$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author");
        $query->select("ua.email AS author_email");

        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
        $query->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

        // Join on contact table
        $subQuery = $db->getQuery(true);
        $subQuery->select('contact.user_id, MAX(contact.id) AS id, contact.language');
        $subQuery->from('#__contact_details AS contact');
        $subQuery->where('contact.published = 1');
        $subQuery->group('contact.user_id, contact.language');
        $query->select('contact.id as contactid');
        $query->join('LEFT', '(' . $subQuery . ') AS contact ON contact.user_id = a.created_by');

        // Join over the categories to get parent category titles - Unnecessary now
        $query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
        $query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

        // Join on voting table
        $query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count');
        $query->join('LEFT', '#__joomblog_posts_rating AS v ON a.id = v.content_id');

        // Join to check for category published state in parent categories up the tree
        $query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
        $subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
        $subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
        $subquery .= 'WHERE parent.extension = ' . $db->quote('com_joomblog');

        if ($this->getState('filter.published') == 2) {
            // Find any up-path categories that are archived
            // If any up-path categories are archived, include all children in archived layout
            $subquery .= ' AND parent.published = 2 GROUP BY cat.id ';
            // Set effective state to archived if up-path category is archived
            $publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END';
        } else {
            // Find any up-path categories that are not published
            // If all categories are published, badcats.id will be null, and we just use the article state
            $subquery .= ' AND parent.published != 1 GROUP BY cat.id ';
            // Select state to unpublished if up-path category is unpublished
            $publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 0 END';
        }
        $query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');

        // Filter by access level.
        if (!in_array('8', JFactory::getUser()->getAuthorisedGroups())) {
            if ($this->getState('filter.access_viewer')) $user = JFactory::getUser($this->getState('filter.access_viewer'));
            else $user = JFactory::getUser();
            $user_id = (int)$user->id;
            $groups = implode(',', $user->getAuthorisedViewLevels());
            if (!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false)) {
                $query->where('(a.access IN (' . $groups . ') OR a.created_by=' . $user_id . ')');
                $query->where('c.access IN (' . $groups . ')');
                $query->where('(lb.access IN (' . $groups . ') OR lb.user_id=' . $user_id . ')');
            } else {
                $userJSGroups = JbblogBaseController::getJSGroups($user->id);
                $userJSFriends = JbblogBaseController::getJSFriends($user->id);
                if (count($userJSGroups) > 0) {
                    $tmpQ1 = ' OR (a.access=-4 AND a.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
                    $tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
                } else {
                    $tmpQ1 = ' ';
                    $tmpQ2 = '';
                }
                if (count($userJSFriends) > 0) {
                    $tmpQ11 = ' OR (a.access=-2 AND a.created_by IN (' . implode(',', $userJSFriends) . ')) ';
                    $tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN (' . implode(',', $userJSFriends) . ')) ';
                } else {
                    $tmpQ11 = ' ';
                    $tmpQ22 = '';
                }
                $query->where('(a.access IN (' . $groups . ')
		OR a.created_by=' . $user_id . ' ' . $tmpQ1 . ' ' . $tmpQ11 . ' )');
                $query->where('c.access IN (' . $groups . ')');
                $query->where('(lb.access IN (' . $groups . ')
		OR lb.user_id=' . $user_id . ' ' . $tmpQ2 . ' ' . $tmpQ22 . ' )');
            }
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            // Use article state if badcats.id is null, otherwise, force 0 for unpublished
            $query->where($publishedWhere . ' = ' . (int)$published);
        } elseif (is_array($published)) {
            JArrayHelper::toInteger($published);
            $published = implode(',', $published);
            // Use article state if badcats.id is null, otherwise, force 0 for unpublished
            $query->where($publishedWhere . ' IN (' . $published . ')');
        }

        // Filter by featured state
        $featured = $this->getState('filter.featured');
        switch ($featured) {
            case 'hide':
                $query->where('a.featured = 0');
                break;

            case 'only':
                $query->where('a.featured = 1');
                break;

            case 'show':
            default:
                // Normally we do not discriminate
                // between featured/unfeatured items.
                break;
        }

        // Filter by a single or group of articles.
        $articleId = $this->getState('filter.article_id');

        if (is_numeric($articleId)) {
            $type = $this->getState('filter.article_id.include', true) ? '= ' : '<> ';
            $query->where('a.id ' . $type . (int)$articleId);
        } elseif (is_array($articleId)) {
            JArrayHelper::toInteger($articleId);
            $articleId = implode(',', $articleId);
            $type = $this->getState('filter.article_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('a.id ' . $type . ' (' . $articleId . ')');
        }

        // Filter by a single or group of categories
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

            // Add subcategory check
            $includeSubcategories = $this->getState('filter.subcategories', false);
            $categoryEquals = 'mc.cid ' . $type . (int)$categoryId;

            if ($includeSubcategories) {
                $levels = (int)$this->getState('filter.max_category_levels', '1');
                // Create a subquery for the subcategory list
                $subQuery = $db->getQuery(true);
                $subQuery->select('sub.id');
                $subQuery->from('#__categories as sub');
                $subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
                $subQuery->where('this.id = ' . (int)$categoryId);
                if ($levels >= 0) {
                    $subQuery->where('sub.level <= this.level + ' . $levels);
                }

                // Add the subquery to the main query
                $query->where('(' . $categoryEquals . ' OR mc.cid IN (' . $subQuery->__toString() . '))');
            } else {
                $query->where($categoryEquals);
            }
        } elseif (is_array($categoryId) && (count($categoryId) > 0)) {
            JArrayHelper::toInteger($categoryId);
            $categoryId = implode(',', $categoryId);
            if (!empty($categoryId)) {
                $type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
                $query->where('mc.cid ' . $type . ' (' . $categoryId . ')');
            }
        }

        // Filter by author
        $authorId = $this->getState('filter.author_id');
        $authorWhere = '';

        if (is_numeric($authorId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
            $authorWhere = 'a.created_by ' . $type . (int)$authorId;
        } elseif (is_array($authorId)) {
            JArrayHelper::toInteger($authorId);
            $authorId = implode(',', $authorId);

            if ($authorId) {
                $type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
                $authorWhere = 'a.created_by ' . $type . ' (' . $authorId . ')';
            }
        }

        // Filter by author alias
        $authorAlias = $this->getState('filter.author_alias');
        $authorAliasWhere = '';

        if (is_string($authorAlias)) {
            $type = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
            $authorAliasWhere = 'a.created_by_alias ' . $type . $db->Quote($authorAlias);
        } elseif (is_array($authorAlias)) {
            $first = current($authorAlias);

            if (!empty($first)) {
                JArrayHelper::toString($authorAlias);

                foreach ($authorAlias as $key => $alias) {
                    $authorAlias[$key] = $db->Quote($alias);
                }

                $authorAlias = implode(',', $authorAlias);

                if ($authorAlias) {
                    $type = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
                    $authorAliasWhere = 'a.created_by_alias ' . $type . ' (' . $authorAlias .
                        ')';
                }
            }
        }

        if (!empty($authorWhere) && !empty($authorAliasWhere)) {
            $query->where('(' . $authorWhere . ' OR ' . $authorAliasWhere . ')');
        } elseif (empty($authorWhere) && empty($authorAliasWhere)) {
            // If both are empty we don't want to add to the query
        } else {
            // One of these is empty, the other is not so we just add both
            $query->where($authorWhere . $authorAliasWhere);
        }

        $config = JFactory::getConfig();
        $offset = $config->get('offset');

        // Filter by start and end dates.
        $nullDate = $db->Quote($db->getNullDate());

        $date = JFactory::getDate("now");
        $timezone = new DateTimeZone($offset);
        $date->setTimezone($timezone);
        $nowDate = $db->Quote($date->toSql(true));


        $query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
        $query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

        // Filter by Date Range or Relative Date
        $dateFiltering = $this->getState('filter.date_filtering', 'off');
        $dateField = $this->getState('filter.date_field', 'a.created');

        switch ($dateFiltering) {
            case 'range':
                $startDateRange = $db->Quote($this->getState('filter.start_date_range', $nullDate));
                $endDateRange = $db->Quote($this->getState('filter.end_date_range', $nullDate));
                $query->where('(' . $dateField . ' >= ' . $startDateRange . ' AND ' . $dateField . ' <= ' . $endDateRange . ')');
                break;

            case 'relative':
                $relativeDate = (int)$this->getState('filter.relative_date', 0);
                $query->where($dateField . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . $relativeDate . ' DAY)');
                break;

            case 'off':
            default:
                break;
        }

        if ($keyword = $this->getState('filter.search')) {
            $keyword = JString::strtolower($keyword);
            $keyword = $db->Quote('%' . $db->escape($keyword, true) . '%', false);
            $query->where('(LOWER( a.title ) LIKE ' . $keyword . ' OR LOWER( a.introtext ) LIKE ' . $keyword . ' OR LOWER( a.fulltext ) LIKE ' . $keyword . ')');
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
            $query->where('(contact.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR contact.language IS NULL)');
        }
        $created_month = $this->getState('filter.created_month');
        $created_year = $this->getState('filter.created_year');
        if (!empty($created_year)) {
            $query->where('YEAR( a.created )=' . $db->quote($created_year));
        }
        if (!empty($created_month)) {
            $query->where('MONTH( a.created )=' . $db->quote($created_month));
        }


        // Add the list ordering clause.
        $query->order($this->getState('list.ordering', 'a.ordering') . ' ' . $this->getState('list.direction', 'ASC'));
        $query->group('a.id'); //Unique posts

        return $query;
    }

    private function getCacheSQL()
    {
        $query = $this->getListQuery();
        $cacheSQL = $query->__toString();

        $cacheSQL = preg_replace('/AND \(a.publish_up[^\)]+\)/s', '', $cacheSQL);
        $cacheSQL = preg_replace('/AND \(a.publish_down[^\)]+\)/s', '', $cacheSQL);

        return $cacheSQL;
    }

    public function getTotal()
    {
        if ($total = jbCacheChecker('postmodel_items_total', $this->getCacheSQL())) {
            return $total;
        } else {
            $total = parent::getTotal();
            jbCacheChecker('postmodel_items_total', $this->getCacheSQL(), $total, 600);

            return $total;
        }
    }

    /**
     * Method to get a list of articles.
     *
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    An array of objects on success, false on failure.
     * @since    1.6
     */
    public function getItems()
    {
        /*if ($items = jbCacheChecker('postmodel_items', $this->getCacheSQL()))
            return $items;*/

        $items = parent::getItems();
        $user = JFactory::getUser();
        $userId = $user->get('id');
        $guest = $user->get('guest');
        $groups = $user->getAuthorisedViewLevels();

        // Get the global params
        $globalParams = JComponentHelper::getParams('com_content', true);

        // Convert the parameter fields into objects.
        foreach ($items as &$item) {
            continue;

            $articleParams = new JRegistry;
            $articleParams->loadString($item->attribs);

            // Unpack readmore and layout params
            $item->alternative_readmore = $articleParams->get('alternative_readmore');
            $item->layout = $articleParams->get('layout');

            $item->params = clone $this->getState('params');

            // For blogs, article params override menu item params only if menu param = 'use_article'
            // Otherwise, menu item params control the layout
            // If menu item is 'use_article' and there is no article param, use global
            if ((JFactory::getApplication()->input->get('layout') == 'blog') || (JFactory::getApplication()->input->get('view') == 'featured')
                || ($this->getState('params')->get('layout_type') == 'blog')
            ) {
                // create an array of just the params set to 'use_article'
                $menuParamsArray = $this->getState('params')->toArray();
                $articleArray = array();

                foreach ($menuParamsArray as $key => $value) {
                    if ($value === 'use_article') {
                        // if the article has a value, use it
                        if ($articleParams->get($key) != '') {
                            // get the value from the article
                            $articleArray[$key] = $articleParams->get($key);
                        } else {
                            // otherwise, use the global value
                            $articleArray[$key] = $globalParams->get($key);
                        }
                    }
                }

                // merge the selected article params
                if (count($articleArray) > 0) {
                    $articleParams = new JRegistry;
                    $articleParams->loadArray($articleArray);
                    $item->params->merge($articleParams);
                }
            } else {
                // For non-blog layouts, merge all of the article params
                $item->params->merge($articleParams);
            }

            // get display date
            switch ($item->params->get('list_show_date')) {
                case 'modified':
                    $item->displayDate = $item->modified;
                    break;

                case 'published':
                    $item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
                    break;

                default:
                case 'created':
                    $item->displayDate = $item->created;
                    break;
            }

            // Compute the asset access permissions.
            // Technically guest could edit an article, but lets not check that to improve performance a little.
            if (!$guest) {
                $asset = 'com_content.article.' . $item->id;

                // Check general edit permission first.
                if ($user->authorise('core.edit', $asset)) {
                    $item->params->set('access-edit', true);
                } // Now check if edit.own is available.
                elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                    // Check for a valid user and that they are the owner.
                    if ($userId == $item->created_by) {
                        $item->params->set('access-edit', true);
                    }
                }
            }

            $access = true;

            if ($access) {
                // If the access filter has been set, we already have only the articles this user can view.
                $item->params->set('access-view', true);
            } else {
                // If no access filter is set, the layout takes some responsibility for display of limited information.
                if ($item->catid == 0 || $item->category_access === null) {
                    $item->params->set('access-view', in_array($item->access, $groups));
                } else {
                    $item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
                }
            }
            if (!empty($item->defaultimage)) {
                jimport('joomla.filesystem.file');
                if (!JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $item->defaultimage)) {
                    $item->defaultimage = '';
                }
            }
        }

        jbCacheChecker('postmodel_items', $this->getCacheSQL(), $items, 600);

        return $items;
    }
}

?>
