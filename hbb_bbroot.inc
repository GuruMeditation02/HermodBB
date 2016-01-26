<?php

/**
 *	Render the forum categories
 *
 */

$noForums = '<p>There are currently no forums to display!</p>';
$noForumCats = '<p>There are currently no forum categories to display!</p>';
$fOnline = $hbb->hbbForumOnline; // Is the forum online? 1 = yes, 0 = no
$fOfflineMsg = $hbb->hbbForumOffMsg; // Offline message to display
$forumName = $hbb->hbbForumName; // Name of the forum

echo "<h1>" . $forumName . "</h1>";

// Make sure the forum is online
if($fOnline == 1) {

	// Make sure there is at least one category configured for rendering
	$forumCats = $pages->find("template=hbb_forum_cat, start=0, sort=sort");
	if(count($forumCats)) {

		foreach ($forumCats as $cat) {

			// Only show the forum categories to those that have permission to view them
			$rolesCatView = $cat->hbb_p_forum_cat_view;
		  if($rolesCatView->has('id=' . $user->roles)) {

		  	// Check for forums and render them
		  	$forums = $pages->find("has_parent=$cat, template=hbb_forum, start=0, sort=sort");
		  	if(count($forums)) {
			  	echo "<table class='uk-table uk-table-condensed'>" .
						    "<thead>" .
					        "<tr>" .
						       	"<th class='uk-width-2-4'>" .
						          $cat->title .
						        "</th>" .
						        "<th class='uk-width-1-4'>" .
						          "Stats" .
						        "</th>" .
						        "<th class='uk-width-1-4'>" .
						          "Latest" .
						        "</th>" .
					        "</tr>" .
						    "</thead>";

			  	foreach ($forums as $forum) {

						// Only show the forums to those that are allowed to view them
						$rolesForumView = $forum->hbb_p_forum_view;
						if($rolesForumView->has('id=' . $user->roles)) {

							//$topics = $forum->children("template=hbb_topic, sort=created"); // Find the topics
							// Find all the comments and topics and sort them
							$topicsComments = $pages->find("has_parent=$forum, template=hbb_topic|hbb_comment, sort=created"); // = $topics->
							//$comments = $pages->find("has_parent=$forum, template=hbb_comment, sort=created"); // Find the comments

							$comments = $pages->find("has_parent=$forum, template=hbb_comment, sort=created");
							$topics = $pages->find("has_parent=$forum, template=hbb_topic, sort=-hbb_ldate");

							//$commentCount = $pages->count("has_parent=$forum, template=hbb_comment");
							//$topicCount = $pages->count("has_parent=$forum, template=hbb_topic");

							if(count($topics)) {

								if(count($topics) == 1) {
									$topicCount = "1 Topic";
								} else {
									$topicCount = count($topics) . " Topics";
								}

								$topicTitle = $topics->last()->title;
								$topicAuthor = $topics->last()->createdUser->name;
								$topicDate = wireRelativeTimeStr($topics->last()->hbb_date);

							} else {

								$topicTitle = "-";
								$topicCount = "No Topics";
								$topicAuthor = "-";
								$topicDate = "-";

							}

							if(count($comments)) {
								if(count($comments) == 1) {
									$commentCount = "1 Comment";
								} else {
									$commentCount = count($comments) . " Comments";
								}
							} else {
								$commentCount = "No Comments";
							}

							echo  "<tbody>" .
							        "<tr>" .
							        		"<td>" .
														"<ul class='uk-list uk-list-space'>" .
														    "<li><a href='{$forum->url}'>" . $forum->title . "</a></li>" .
														    "<li><small>" . $forum->hbb_forum_description . "</small></li>" .
														"</ul>" .
							        		"</td>" .
							            "<td>" .
														"<ul class='uk-list'>" .
														    "<li><small>" . $topicCount . "</small></li>" .
														    "<li><small>" . $commentCount . "</small></li>" .
														"</ul>" .
													"</td>" .
							            "<td>" .
														"<ul class='uk-list'>" .
														    "<li><small>" . "Title: " . $topicTitle . "</small></li>" .
														    "<li><small>" . "By: " . $topicAuthor . "</small></li>" .
														    "<li><small>" . "Date: " . $topicDate . "</small></li>" .
														"</ul>" .
													"</td>" .
							        "</tr>" .
								    "</tbody>";

						} else {
							// No permission to view the forum
						}
			  	}
				  // End of the table for this category
			  	echo "</table>";
			  } else {
					// No forums have been set up
					echo $noForums;
				}

		  } else {
		  	// No permission to view forum category
		  }
		}
	} else {
		// No forum categories found
		echo $noForumCats;
	}

} else {
	// The forums are offline
	echo $fOfflineMsg;
}
