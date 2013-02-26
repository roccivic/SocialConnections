<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

class PageSelector {
	private $pos;
	public function __construct()
	{
		$this->pos = 0;
		if (! empty($_REQUEST['pos'])) {
			$pos = intval($_REQUEST['pos']);
			if ($pos > 0) {
				$this->pos = $pos;
			}
		}
	}

	public function getLimit()
	{
		return 10;
	}

	public function getPos()
	{
		return $this->pos;
	}

    /**
     * Generate a pagination selector for browsing resultsets
     *
     * @param string $name        The name for the request parameter
     * @param int    $rows        Number of rows in the pagination set
     * @param int    $pageNow     current page number
     * @param int    $nbTotalPage number of total pages
     * @param int    $showAll     If the number of pages is lower than this
     *                            variable, no pages will be omitted in pagination
     * @param int    $sliceStart  How many rows at the beginning should always
     *                            be shown?
     * @param int    $sliceEnd    How many rows at the end should always be shown?
     * @param int    $percent     Percentage of calculation page offsets to hop to a
     *                            next page
     * @param int    $range       Near the current page, how many pages should
     *                            be considered "nearby" and displayed as well?
     * @param string $prompt      The prompt to display (sometimes empty)
     *
     * @return string
     *
     * @access  public
     */
    private function pageselector(
        $name, $rows, $pageNow = 1, $nbTotalPage = 1, $showAll = 200,
        $sliceStart = 5,
        $sliceEnd = 5, $percent = 20, $range = 10, $prompt = ''
    ) {
        $increment = floor($nbTotalPage / $percent);
        $pageNowMinusRange = ($pageNow - $range);
        $pageNowPlusRange = ($pageNow + $range);

        $gotopage = $prompt . ' <select class="autosubmit" data-theme="e" name="' . $name . '" >';
        if ($nbTotalPage < $showAll) {
            $pages = range(1, $nbTotalPage);
        } else {
            $pages = array();

            // Always show first X pages
            for ($i = 1; $i <= $sliceStart; $i++) {
                $pages[] = $i;
            }

            // Always show last X pages
            for ($i = $nbTotalPage - $sliceEnd; $i <= $nbTotalPage; $i++) {
                $pages[] = $i;
            }

            // Based on the number of results we add the specified
            // $percent percentage to each page number,
            // so that we have a representing page number every now and then to
            // immediately jump to specific pages.
            // As soon as we get near our currently chosen page ($pageNow -
            // $range), every page number will be shown.
            $i = $sliceStart;
            $x = $nbTotalPage - $sliceEnd;
            $met_boundary = false;

            while ($i <= $x) {
                if ($i >= $pageNowMinusRange && $i <= $pageNowPlusRange) {
                    // If our pageselector comes near the current page, we use 1
                    // counter increments
                    $i++;
                    $met_boundary = true;
                } else {
                    // We add the percentage increment to our current page to
                    // hop to the next one in range
                    $i += $increment;

                    // Make sure that we do not cross our boundaries.
                    if ($i > $pageNowMinusRange && ! $met_boundary) {
                        $i = $pageNowMinusRange;
                    }
                }

                if ($i > 0 && $i <= $x) {
                    $pages[] = $i;
                }
            }

            /*
            Add page numbers with "geometrically increasing" distances.

            This helps me a lot when navigating through giant tables.

            Test case: table with 2.28 million sets, 76190 pages. Page of interest
            is between 72376 and 76190.
            Selecting page 72376.
            Now, old version enumerated only +/- 10 pages around 72376 and the
            percentage increment produced steps of about 3000.

            The following code adds page numbers +/- 2,4,8,16,32,64,128,256 etc.
            around the current page.
            */
            $i = $pageNow;
            $dist = 1;
            while ($i < $x) {
                $dist = 2 * $dist;
                $i = $pageNow + $dist;
                if ($i > 0 && $i <= $x) {
                    $pages[] = $i;
                }
            }

            $i = $pageNow;
            $dist = 1;
            while ($i >0) {
                $dist = 2 * $dist;
                $i = $pageNow - $dist;
                if ($i > 0 && $i <= $x) {
                    $pages[] = $i;
                }
            }

            // Since because of ellipsing of the current page some numbers may be
            // double, we unify our array:
            sort($pages);
            $pages = array_unique($pages);
        }

        foreach ($pages as $i) {
            if ($i == $pageNow) {
                $selected = 'selected="selected" style="font-weight: bold"';
            } else {
                $selected = '';
            }
            $gotopage .= '                <option ' . $selected
                . ' value="' . (($i - 1) * $rows) . '">' . $i . '</option>';
        }

        $gotopage .= ' </select>';

        return $gotopage;
    } // end function

    /**
     * Prepare navigation for a list
	 *
     * @return string
     */
    public function getPageSelector($count, $script)
    {
    	$pos = $this->getPos();
    	$max_count = $this->getLimit();
    	$itemCount = 0;
        $list_navigator_html = array();
        if ($max_count < $count) {
        	$itemCount = 1;
            // Move to the beginning or to the previous page
            if ($pos > 0) {
            	$itemCount += 2;
                $caption1 = '&lt;&lt;';
                $caption2 = ' &lt; ';
                $title1   = ' title="' . _pgettext('First page', 'Begin') . '"';
                $title2   = ' title="'
                    . _pgettext('Previous page', 'Previous') . '"';

                $list_navigator_html[] = '<a data-theme="e" data-role="button"' . $title1
                	. ' href="' . $script . '&pos=0">'
                	. $caption1 . '</a>';

                $pNum = $pos - $max_count;
                $list_navigator_html[] = '<a data-theme="e" data-role="button"' . $title2 
                	. ' href="' . $script . '&pos=' . $pNum . '">'
                	. $caption2 . '</a>';
            }

            $form  = '<form action="' . $script . '" method="post">';
            $form .= $this->pageselector(
                'pos',
                $max_count,
                floor(($pos + 1) / $max_count) + 1,
                ceil($count / $max_count)
            );
            $form .= '</form>';
			$list_navigator_html[] = $form;

            if ($pos + $max_count < $count) {
            	$itemCount += 2;
                $caption3 = ' &gt; ';
                $caption4 = '&gt;&gt;';
                $title3   = ' title="' . _pgettext('Next page', 'Next') . '"';
                $title4   = ' title="' . _pgettext('Last page', 'End') . '"';

                $pNum = $pos + $max_count;
                $list_navigator_html[] = '<a data-theme="e" data-role="button"'. $title3
                	. ' href="' . $script . '&pos=' . $pNum . '" >'
                	. $caption3 . '</a>';

                $pNum = floor($count / $max_count) * $max_count;
                if ($pNum == $count) {
                    $pNum = $count - $max_count;
                }

                $list_navigator_html[] = '<a data-theme="e" data-role="button"'. $title4
                	. ' href="' . $script . '&pos=' . $pNum . '" >'
                	. $caption4 . '</a>';
            }
        }

        if ($itemCount > 0) {
        	$letters = array('a','b','c','d','e');
        	if ($itemCount == 3) {
				$pageSelector = '<div class="ui-grid-b">';
        	} else {
				$pageSelector = '<div class="ui-grid-d">';
        	}
        	foreach ($list_navigator_html as $index => $value) {
				$pageSelector .= '<div class="ui-block-' . $letters[$index] . '">';
				$pageSelector .= $value;
				$pageSelector .= '</div>';
			}
			$pageSelector .= '</div><br />';
        	return $pageSelector;
        } else {
	        return '';
        }
    }
}
?>