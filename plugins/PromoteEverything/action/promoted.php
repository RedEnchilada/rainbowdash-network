<?php
/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * List of popular notices
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Public
 * @package   StatusNet
 * @author    Zach Copley <zach@status.net>
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2008-2009 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/publicgroupnav.php';
require_once INSTALLDIR.'/lib/noticelist.php';

/**
 * List of popular notices
 *
 * We provide a list of the most popular notices. Popularity
 * is measured by
 *
 * @category Personal
 * @package  StatusNet
 * @author   Zach Copley <zach@status.net>
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class PromotedAction extends Action
{
    var $page = null;

    /**
     * Title of the page
     *
     * @return string Title of the page
     */

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Page title for first page of promoted notices.
            return _('Promoted dashes');
        } else {
            // TRANS: Page title for all but first page of promoted notices.
            // TRANS: %d is the page number being displayed.
            return sprintf(_('Promoted dashes, page %d'), $this->page);
        }
    }

    /**
     * Instructions for use
     *
     * @return instructions for use
     */
    function getInstructions()
    {
        // TRANS: Description on page displaying promoted notices.
        return _('Dashes, users, groups, and tags promoted by the administrators.');
    }

    /**
     * Is this page read-only?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     *
     * @todo move queries from showContent() to here
     */
    function prepare($args)
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        common_set_returnto($this->selfUrl());

        return true;
    }

    /**
     * Handle request
     *
     * Shows a page with list of promoted notices
     *
     * @param array $args $_REQUEST args; handled in prepare()
     *
     * @return void
     */
    function handle($args)
    {
        parent::handle($args);

        $this->showPage();
    }

    /**
     * Show the page notice
     *
     * Shows instructions for the page
     *
     * @return void
     */
    function showPageNotice()
    {
        $instr  = $this->getInstructions();
        $output = common_markup_to_html($instr);

        $this->elementStart('div', 'instructions');
        $this->raw($output);
        $this->elementEnd('div');
    }

    function showEmptyList()
    {
        // TRANS: Text displayed instead of a list when a site does not yet have any favourited notices.
        $message = _('Promoted dashes appear on this page but no one has promoted one yet.') . ' ';

        $this->elementStart('div', 'guide');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    }

    /**
     * Local navigation
     *
     * This page is part of the public group, so show that.
     *
     * @return void
     */
    function showLocalNav()
    {
        $nav = new PublicGroupNav($this);
        $nav->show();
    }

    /**
     * Content area
     *
     * Shows the list of popular notices
     *
     * @return void
     */
    function showContent()
    {
        $offset = ($this->page - 1) * NOTICES_PER_PAGE;
        $limit  = NOTICES_PER_PAGE;
        $notice = Promote::getStream($offset, $limit);

        $nl = new NoticeList($notice, $this);

        $cnt = $nl->show();

        if ($cnt == 0) {
            $this->showEmptyList();
        }

        $this->pagination($this->page > 1, $cnt > NOTICES_PER_PAGE,
                          $this->page, 'promoted');
    }
}
