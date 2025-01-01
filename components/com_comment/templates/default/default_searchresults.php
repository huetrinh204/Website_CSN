<?php
/**
 * @package    Ccomment
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       24.04.17
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access'); ?>

<div id='SearchResults'>
    <a name='SearchResults'></a>
    <ul>
        <li>
            {resulttitle}
        </li>
        {searchresult}
        <li class='post'>
            <div class='posthead'>
                <span class='postinfo'>
                    {date}
                </span>
                <span class='comment_by'>
                    {title} {COM_COMMENT_BY} {name}
                </span>
            </div>
            <div class='postcontent'>
                <a  href='{address}'>{preview}</a>
            </div>
            {/searchresult}
        </li>
    </ul>
    <div style='margin-bottom: 5px;'></div>
</div>