<?php

/**
 *	Render the topic if the user has permission to view the parent page (forum)
 *
 */

$noForumView = '<p>You do not have permission to view that forum / topic</p>';
$fOnline = $hbb->hbbForumOnline; // Is the forum online? 1 = yes, 0 = no
$fOfflineMsg = $hbb->hbbForumOffMsg; // Offline message to display

// Make sure the forum is online
if ($fOnline == 1) {

	$forumRoot = $pages->get("template=hbb_bbroot");
	echo "<ul class='uk-breadcrumb'>" .
			    "<li><a href='{$forumRoot->url}'>" . $forumRoot->title . "</a></li>" .
			    "<li><a href='{$page->parent->url}'>" . $page->parent->title . "</a></li>" .
			    "<li class='uk-active'><span>" . $page->title . "</span></li>" .
			  "</ul>" .
			  "<hr>";

	// Is the user allowed to post comments (replies)?
	$rolesCommentsPost = $page->parent->hbb_p_comments_post;
	if ($rolesCommentsPost->has('id=' . $user->roles)) {
		// If the submit button is clicked, we can sanitize the text, save the comment and redirect
		// to the last comment
		if ($input->post->subComment) {

	    $commentsPerPage = $hbb->hbbCommentsPerPage; // How many comments (replies) per page?

	    $purifier = $modules->get('MarkupHTMLPurifier');
	    $cleanHTML = $purifier->purify($input->post->commentsCK);

	    $hbb->commentSave(array('commentContent' => $cleanHTML));
	    $comments = $pages->get($page->path . "comments/")->find("template=hbb_comment");
	    $commentCount = $comments->count();
	    $commentLast = $comments->last();
	    $pageNum = ceil(($commentCount) / $commentsPerPage);
	    $session->redirect('./page' . $pageNum . '#' . $commentLast->id);
		}
	}

	// Find which user roles are selected from the "View Forum / Topics" options (hbb_forum_view)
	$rolesForumView = $page->parent->hbb_p_forum_view;

	// If the user has a role that is checked in the "View Forum / Topics" options, they will be allowed to view the topic
	if ($rolesForumView->has('id=' . $user->roles)) {

		$renderHeader = constructItemHeader("uk-icon-file-text-o", $page, "Topic", $page->hbb_date);

		echo "<article class='uk-article'>" .
		     	"<h2>" . $page->title . "</h2>" .
		      "<p class='uk-article-meta'>" . $renderHeader . "</p>" .
		      //"<hr class='uk-article-divider'>" .
		      $page->hbb_topic_content .
		     "</article>";

		// Add the user's name to the hbb_topic_viewed list so that we know they have viewed this topic
		// When a new comment is posted, this list can be cleared
		$userViewed = $pages->get("template=user, name={$user->name}");

		$page->of(false);
		$page->hbb_topic_viewed->add($userViewed);
		$page->save();
		$page->of(true);

		// If the user has a role that is checked in the "View Comments (replies)" options, they will be allowed to view the comments
		$rolesCommentsView = $page->parent->hbb_p_comments_view;
		if ($rolesCommentsView->has('id=' . $user->roles)) {

			// Display the comments if they are online. This can be set in the admin module settings
			$cOnline = $hbb->hbbCommentsOnline;
			if ($cOnline == 1) {

				echo "<hr>";
				// Render the comments
				$comments = $pages->get($page->path . "comments/")->find("template=hbb_comment");

				foreach ($comments as $comment) {
					echo $comment->hbb_comment_content;
				}

				// Is the user allowed to post comments (replies)?
				$rolesCommentsPost = $page->parent->hbb_p_comments_post;
				if ($rolesCommentsPost->has('id=' . $user->roles)) {
					echo "<form class='uk-form' method='post'>
	  							<fieldset data-uk-margin>
	    							<div class='uk-form-row'>
	      							<textarea class='ckeditor' name='commentsCK' id='commentsCK' rows='10' cols='80' required></textarea>
	    							</div>
								    <div class='uk-form-row'>
								      <button class='uk-button' type='submit' name='subComment' value='Send'><i class='uk-icon-comment'></i> Post Comment</button>
								    </div>
								  </fieldset>
								</form>";
				} else {
					echo "<p>You do not have permission to post comments!</p>";
				}

			} else {
				// If the comments are offline, display the appropriate message (admin module settings)
				echo $hbb->hbbCommentsOffMsg;
			}

		} else {
			echo "<p>You do not have permission to view comments!</p>";
		}

	} else {
		echo $noForumView;
	}

} else {
	echo $fOfflineMsg;
}
