<?php

/**
 *	Render the list of topics for the current forum
 *
 */

$noTopics = '<p>There are currently no topics to display!</p>';
$topicsPerPage = $hbb->hbbTopicsPerPage; // Topics per page
$fOnline = $hbb->hbbForumOnline; // Is the forum online? 1 = yes, 0 = no
$fOfflineMsg = $hbb->hbbForumOffMsg; // Offline message to display
$forumName = $hbb->hbbForumName; // Name of the forum

// Make sure the forum is online
if($fOnline == 1) {

	// Breadcrumbs
	$forumRoot = $pages->get("template=hbb_bbroot");
	echo "<ul class='uk-breadcrumb'>" .
			    "<li><a href='{$forumRoot->url}'>" . $forumRoot->title . "</a></li>" .
			    "<li class='uk-active'><span>" . $page->title . "</span></li>" .
			  "</ul>" .
			  "<hr>";

	// Only show the forum & topics to those that have permission
	$rolesForumView = $page->hbb_p_forum_view;
	if($rolesForumView->has('id=' . $user->roles)) {

		//$topicsComments = $pages->find("has_parent=$page, template=hbb_topic|hbb_comment, sort=created");
		$topics = $pages->find("has_parent=$page, template=hbb_topic, sort=-hbb_ldate, limit=$topicsPerPage");

		if(count($topics)) {

	  	echo "<table class='uk-table uk-table-condensed'>" .
				    "<thead>" .
			        "<tr>" .
				       	"<th class='uk-width-2-4'>" .
				          $page->title .
				        "</th>" .
				        "<th class='uk-width-1-4'>" .
				          "Comments" .
				        "</th>" .
				        "<th class='uk-width-1-4'>" .
				          "Latest" .
				        "</th>" .
			        "</tr>" .
				    "</thead>";

			foreach ($topics as $topic) {

				// Topic
        $topicTitle = $topic->title;
        $topicAuthor = $topic->createdUser->name;
        $topicDate = wireRelativeTimeStr($topic->hbb_date);

        // Comments
				$comments = $pages->find("has_parent=$topic, template=hbb_comment");
				if(count($comments)) {

					if(count($comments) == 1) {
						$commentCount = "1 Comment";
					}	else {
						$commentCount = count($comments) . " Comments";
				  }

					$commentAuthor = $comments->last()->createdUser->name;
					$commentDate = wireRelativeTimeStr($comments->last()->hbb_date);

				} else {

					// No comments
					$commentCount = "No Comments";
					$commentAuthor = "-";
					$commentDate = "-";

				}

				echo  "<tbody>" .
				        "<tr>" .
				        		"<td>" .
											"<ul class='uk-list'>" .
											    "<li><a href='{$topic->url}'>" . $topicTitle . "</a></li>" .
											    "<li><small>" . "By: " . $topicAuthor . "</small></li>" .
											    "<li><small>" . "Date: " . $topicDate . "</small></li>" .
											"</ul>" .
				        		"</td>" .
				            "<td>" .
											"<ul class='uk-list'>" .
											    "<li><small>" . $commentCount . "</small></li>" .
											"</ul>" .
										"</td>" .
				            "<td>" .
											"<ul class='uk-list'>" .
											    "<li><small>" . "Comment by: " . $commentAuthor . "</small></li>" .
											    "<li><small>" . "Date: " . $commentDate . "</small></li>" .
											"</ul>" .
										"</td>" .
				        "</tr>" .
					    "</tbody>";
			}

		  echo "</table>";

/*			echo $topicsComments->renderPager(array(
			'nextItemLabel' => "Next",
			'previousItemLabel' => "Prev",
			'listMarkup' => "<ul class='uk-pagination'>{out}</ul>",
			'itemMarkup' => "<li class='{class}'>{out}</li>",
			'linkMarkup' => "<a href='{url}'><span>{out}</span></a>"
			));*/

		} else {
			echo $noTopics;
		}

  } else {
  	// No permission to view the forum
  }

} else {
	// The forums are offline
	echo $fOfflineMsg;
}
