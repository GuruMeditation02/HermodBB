<?php

/**
 *
 */
class HermodBB extends WireData implements Module {

  /**
   * Initialise
   *
   * @access public
   *
   */
  public function init() {
    $this->fuel->set("hbb", $this);
  }
  
  /**
   * Count the total amount of comments under the current page (NEEDED for the commentSave method)
   *
   * @access public
   * @return integer $commentCount
   *
   */
  public function commentCount() {

    $pages = wire('pages');
    $page = wire('page');
    $commentCount = $pages->get($page->path . "comments/")->find("template=hbb_comment")->getTotal();

    return $commentCount;
  }

  /**
   * Save a new comment
   *
   * @access public
   * @param array $options Options for the comment, published / unpublished, comment content etc
   *
   */
  public function commentSave($options = array()) {

    $pages = wire('pages');
    $page = wire('page');
    $templates = wire('templates');
    $user = wire('user');

    $defaultOptions = array(
      'commentContent' => '', // The comment content
      'commentPath' => $page->path . 'comments/', // The path to where we save the comment
      'status' => 'published', // status can be published or unpublished
      'pinned' => 0, // Is this comment pinned?
      'isEdit' => false, // Is this an edit?- true/false
      'editID' => 0, // The $page->id of the comment to edit
      'isReply' => false, // Is this a reply? - true/false
      'replyID' => 0, // If it's a reply, what is the ID of the comment that has been replied to
    );

    // Use the defaults if any options are omitted
    $comment = array_merge($defaultOptions, $options);

    $currentDateTime = date('U'); // Unix timestamp

    // Update the comment if requested (edit == true)
    if($comment['isEdit'] == true) {
      $editComment = $pages->get($comment['editID']);

      $editComment->of(false);
      $editComment->hbb_comment_content = $comment['commentContent'];
      $editComment->title = "(" . $user->name . ") " . substr(strip_tags($comment['commentContent']), 0, 25);
      $editComment->hbb_edate = $currentDateTime;
      if($comment['pinned'] == 1) {
        $editComment->hbb_pinned = 1;
      } else {
        $editComment->hbb_pinned = 0;
      }
      $editComment->save();
      $editComment->of(true);
    // Otherwise, save a new comment (edit == false)
    } else {

      $cat = $page->children('template=hbb_comment_cat');

      // Create the comments category if it doesn't exist
      if(!count($cat)) {

        $template_comment_cat = $templates->get('hbb_comment_cat');
        $commentsCatPage = new Page();
        $commentsCatPage->of(false);
        $commentsCatPage->parent = $page->path;
        $commentsCatPage->template = $template_comment_cat;
        $commentsCatPage->title = 'Comments';
        $commentsCatPage->save();
        $commentsCatPage->of(true);
      }

      // Save the comment
      $template_comment_full = $templates->get('hbb_comment');
      $newComment = new Page();
      $newComment->of(false);
      $newComment->parent = $comment['commentPath'];
      $newComment->template = $template_comment_full;
      $newComment->title = "(" . $user->name . ") " . substr(strip_tags($comment['commentContent']), 0, 25); // Display name and the first 25 comment characters.
      $newComment->hbb_comment_id = ($this->commentCount()+1);
      $newComment->hbb_comment_content = $comment['commentContent'];
      $newComment->hbb_ip = $this->wire('session')->getIP();
      $newComment->hbb_agent = $_SERVER['HTTP_USER_AGENT'];
      $newComment->hbb_edate = $currentDateTime;
      $newComment->hbb_date = $currentDateTime;
      if($comment['pinned'] == 1) {
        $newComment->hbb_pinned = 1;
      }
      if($comment['status'] == 'published') {
        $newComment->addStatus(Page::statusOn);
      } elseif($comment['status'] == 'unpublished') {
        $newComment->addStatus(Page::statusUnpublished);
      }
      $newComment->save();
      $newComment->of(true);

      // Update the hbb_ldate field for the topic so that we can easily sort the topics based on the last
      // reply date
      if($page->template = 'hbb_topic') {
        $page->of(false);
        $page->hbb_ldate = $currentDateTime;
        $page->save();
        $page->of(true);
      }
    }
  }

  public function topicSave($options = array()) {

    $pages = wire('pages');
    $templates = wire('templates');

    $defaultOptions = array(
      'topicContent' => '', // The topic content
      'topicTitle' => '', // The topic title
      'forumID' => 0, // The $page->id of the forum we want to save our topic to
      'status' => 'published', // status can be published or unpublished
      'pinned' => 0, // Is this topic pinned?
      'locked' => 0, // Is this topic locked?
      'isEdit' => false, // Is this an edit?- true/false
      'editID' => 0, // The $page->id of the topic to edit
    );

    // Use the defaults if any options are omitted
    $topic = array_merge($defaultOptions, $options);

    // Store the forum id from our options in a variable (readability)
    $forumID = $topic['forumID'];

    $currentDateTime = date('U'); // Unix timestamp

    // Update the topic if requested (edit == true)
    if($topic['isEdit'] == true) {
      $editTopic = $pages->get($topic['editID']);

      $editTopic->of(false);
      $editTopic->hbb_topic_content = $topic['topicContent'];
      $editTopic->title = $topic['topicTitle'];
      $editTopic->hbb_edate = $currentDateTime;
      if($topic['pinned'] == 1) {
        $editTopic->hbb_pinned = 1;
      } else {
        $editTopic->hbb_pinned = 0;
      }
      if($topic['locked'] == 1) {
        $editTopic->hbb_locked = 1;
      } else {
        $editTopic->hbb_locked = 0;
      }
      $editTopic->save();
      $editTopic->of(true);
    // Otherwise, save a new topic (edit == false)
    } else {
      // Save the topic
      $template_topic = $templates->get('hbb_topic');
      $newTopic = new Page();
      $newTopic->of(false);
      $newTopic->parent = $pages->get($forumID)->path();
      $newTopic->template = $template_topic;
      $newTopic->title = $topic['topicTitle'];
      $newTopic->hbb_topic_content = $topic['topicContent'];
      $newTopic->hbb_ip = $this->wire('session')->getIP();
      $newTopic->hbb_agent = $_SERVER['HTTP_USER_AGENT'];
      $newTopic->hbb_edate = $currentDateTime;
      $newTopic->hbb_date = $currentDateTime;
      $newTopic->hbb_ldate = $currentDateTime;
      if($comment['locked'] == 1) {
        $newComment->hbb_locked = 1;
      }
      if($comment['pinned'] == 1) {
        $newComment->hbb_pinned = 1;
      }
      if($topic['status'] == 'published') {
        $newTopic->addStatus(Page::statusOn);
      } elseif($topic['status'] == 'unpublished') {
        $newTopic->addStatus(Page::statusUnpublished);
      }
      $newTopic->save();
      $newTopic->of(true);

    }
  }

  /**
   * Install all the required fields, templates etc
   *
   * @access public
   *
   */
  public function ___install() {


    // Pin Topics / Comments Permission
    if(!$this->fields->get('hbb_p_pin')) {
      $field_topic_pin = new Field();
      $field_topic_pin->type = $this->modules->get("FieldtypePage");
      $field_topic_pin->name = 'hbb_p_pin';
      $field_topic_pin->label = 'Pin Topics / Comments';
      $field_topic_pin->template_id = wire('templates')->get("role")->id; // Get the id number of the 'role' template
      $field_topic_pin->labelFieldName = 'name';
      $field_topic_pin->inputfield = 'InputfieldCheckboxes';
      $field_topic_pin->optionColumns = 1;
      $field_topic_pin->tags = 'HermodBB';
      $field_topic_pin->icon = 'thumb-tack';
      $field_topic_pin->save();
    }

    // Lock Topics Permission
    if(!$this->fields->get('hbb_p_topic_lock')) {
      $field_topic_lock = new Field();
      $field_topic_lock->type = $this->modules->get("FieldtypePage");
      $field_topic_lock->name = 'hbb_p_topic_lock';
      $field_topic_lock->label = 'Lock Topics';
      $field_topic_lock->template_id = wire('templates')->get("role")->id; // Get the id number of the 'role' template
      $field_topic_lock->labelFieldName = 'name';
      $field_topic_lock->inputfield = 'InputfieldCheckboxes';
      $field_topic_lock->optionColumns = 1;
      $field_topic_lock->tags = 'HermodBB';
      $field_topic_lock->icon = 'lock';
      $field_topic_lock->save();
    }

    // View Forum Permission
    if(!$this->fields->get('hbb_p_forum_view')) {
      $field_forum_view = new Field();
      $field_forum_view->type = $this->modules->get("FieldtypePage");
      $field_forum_view->name = 'hbb_p_forum_view';
      $field_forum_view->label = 'View Forum & Topics';
      $field_forum_view->template_id = wire('templates')->get("role")->id; // Get the id number of the 'role' template
      $field_forum_view->labelFieldName = 'name';
      $field_forum_view->inputfield = 'InputfieldCheckboxes';
      $field_forum_view->optionColumns = 1;
      $field_forum_view->tags = 'HermodBB';
      $field_forum_view->icon = 'eye';
      $field_forum_view->save();
    }

    // Start Topics Permission
    if(!$this->fields->get('hbb_p_topic_start')) {
      $field_topic_post = new Field();
      $field_topic_post->type = $this->modules->get("FieldtypePage");
      $field_topic_post->name = 'hbb_p_topic_start';
      $field_topic_post->label = 'Start Topics';
      $field_topic_post->template_id = wire('templates')->get("role")->id; // Get the id number of the 'role' template
      $field_topic_post->labelFieldName = 'name';
      $field_topic_post->inputfield = 'InputfieldCheckboxes';
      $field_topic_post->optionColumns = 1;
      $field_topic_post->tags = 'HermodBB';
      $field_topic_post->icon = 'file';
      $field_topic_post->save();
    }

    // View Comments Permission
    if(!$this->fields->get('hbb_p_comments_view')) {
      $field_comments_view = new Field();
      $field_comments_view->type = $this->modules->get("FieldtypePage");
      $field_comments_view->name = 'hbb_p_comments_view';
      $field_comments_view->label = 'View Comments (replies)';
      $field_comments_view->template_id = wire('templates')->get("role")->id; // Get the id number of the 'role' template
      $field_comments_view->labelFieldName = 'name';
      $field_comments_view->inputfield = 'InputfieldCheckboxes';
      $field_comments_view->optionColumns = 1;
      $field_comments_view->tags = 'HermodBB';
      $field_comments_view->icon = 'eye';
      $field_comments_view->save();
    }

    // Post Comments Permission
    if(!$this->fields->get('hbb_p_comments_post')) {
      $field_comments_post = new Field();
      $field_comments_post->type = $this->modules->get("FieldtypePage");
      $field_comments_post->name = 'hbb_p_comments_post';
      $field_comments_post->label = 'Post Comments (replies)';
      $field_comments_post->template_id = wire('templates')->get("role")->id; // Get the id number of the 'role' template
      $field_comments_post->labelFieldName = 'name';
      $field_comments_post->inputfield = 'InputfieldCheckboxes';
      $field_comments_post->optionColumns = 1;
      $field_comments_post->tags = 'HermodBB';
      $field_comments_post->icon = 'comment';
      $field_comments_post->save();
    }

    // Forum Description
    if(!$this->fields->get('hbb_forum_description')) {
      $field_forum_description = new Field();
      $field_forum_description->type = $this->modules->get("FieldtypeTextarea");
      $field_forum_description->name = 'hbb_forum_description';
      $field_forum_description->label = 'Forum Description / Rules';
      $field_forum_description->tags = 'HermodBB';
      $field_forum_description->icon = 'question-circle';
      $field_forum_description->save();
    }

    // View Forum Cat Permission
    if(!$this->fields->get('hbb_p_forum_cat_view')) {
      $field_forum_cat_view = new Field();
      $field_forum_cat_view->type = $this->modules->get("FieldtypePage");
      $field_forum_cat_view->name = 'hbb_p_forum_cat_view';
      $field_forum_cat_view->label = 'View Forum Category';
      $field_forum_cat_view->template_id = wire('templates')->get("role")->id; // Get the id number of the 'role' template
      $field_forum_cat_view->labelFieldName = 'name';
      $field_forum_cat_view->inputfield = 'InputfieldCheckboxes';
      $field_forum_cat_view->optionColumns = 1;
      $field_forum_cat_view->tags = 'HermodBB';
      $field_forum_cat_view->icon = 'eye';
      $field_forum_cat_view->save();
    }

    // Comment Field
    if(!$this->fields->get('hbb_comment_content')) {
      $field_comment = new Field();
      $field_comment->type = $this->modules->get("FieldtypeTextarea");
      $field_comment->name = 'hbb_comment_content';
      $field_comment->label = 'Comment';
      $field_comment->tags = 'HermodBB';
      $field_comment->icon = 'comment';
      $field_comment->save();
    }

    // Comment ID
    if(!$this->fields->get('hbb_comment_id')) {
      $field_id = new Field();
      $field_id->type = $this->modules->get("FieldtypeInteger");
      $field_id->name = 'hbb_comment_id';
      $field_id->label = 'Comment ID';
      $field_id->tags = 'HermodBB';
      $field_id->icon = 'comment';
      $field_id->save();
    }

    // Reply ID
    if(!$this->fields->get('hbb_reply_id')) {
      $field_reply_id = new Field();
      $field_reply_id->type = $this->modules->get("FieldtypeInteger");
      $field_reply_id->name = 'hbb_reply_id';
      $field_reply_id->label = 'Reply ID';
      $field_reply_id->description = 'The $page->id of the comment that was replied to (if applicable)';
      $field_reply_id->tags = 'HermodBB';
      $field_reply_id->icon = 'reply';
      $field_reply_id->save();
    }

    // Author IP
    if(!$this->fields->get('hbb_ip')) {
      $field_ip = new Field();
      $field_ip->type = $this->modules->get("FieldtypeText");
      $field_ip->name = 'hbb_ip';
      $field_ip->label = 'IP Address';
      $field_ip->tags = 'HermodBB';
      $field_ip->icon = 'search';
      $field_ip->save();
    }

    // Author User Agent
    if(!$this->fields->get('hbb_agent')) {
      $field_agent = new Field();
      $field_agent->type = $this->modules->get("FieldtypeText");
      $field_agent->name = 'hbb_agent';
      $field_agent->label = 'User Agent';
      $field_agent->tags = 'HermodBB';
      $field_agent->icon = 'search';
      $field_agent->save();
    }

    // Comment/Topic Date
    if(!$this->fields->get('hbb_date')) {
      $field_date = new Field();
      $field_date->type = $this->modules->get("FieldtypeDatetime");
      $field_date->name = "hbb_date";
      $field_date->label = "Comment / Topic Created";
      $field_date->dateOutputFormat = "U";
      $field_date->dateInputFormat = "j F Y";
      $field_date->timeInputFormat = "g:i A";
      $field_date->defaultToday = 1;
      $field_date->datepicker = 1; // datepicker on click
      $field_date->tags = 'HermodBB';
      $field_date->icon = 'calendar';
      $field_date->save();
    }

    // Comment/Topic Edited Date
    if(!$this->fields->get('hbb_edate')) {
      $field_edit_date = new Field();
      $field_edit_date->type = $this->modules->get("FieldtypeDatetime");
      $field_edit_date->name = "hbb_edate";
      $field_edit_date->label = "Comment / Topic Last Edited";
      $field_edit_date->dateOutputFormat = "U";
      $field_edit_date->dateInputFormat = "j F Y";
      $field_edit_date->timeInputFormat = "g:i A";
      $field_edit_date->defaultToday = 1;
      $field_edit_date->datepicker = 1; // datepicker on click
      $field_edit_date->tags = 'HermodBB';
      $field_edit_date->icon = 'calendar';
      $field_edit_date->save();
    }

    // Last Reply Date
    if(!$this->fields->get('hbb_ldate')) {
      $field_last_date = new Field();
      $field_last_date->type = $this->modules->get("FieldtypeDatetime");
      $field_last_date->name = "hbb_ldate";
      $field_last_date->label = "Last Reply Date";
      $field_last_date->dateOutputFormat = "U";
      $field_last_date->dateInputFormat = "j F Y";
      $field_last_date->timeInputFormat = "g:i A";
      $field_last_date->defaultToday = 1;
      $field_last_date->datepicker = 1; // datepicker on click
      $field_last_date->tags = 'HermodBB';
      $field_last_date->icon = 'calendar';
      $field_last_date->save();
    }

    // Pinned
    if(!$this->fields->get('hbb_pinned')) {
      $field_pinned = new Field();
      $field_pinned->type = $this->modules->get("FieldtypeCheckbox");
      $field_pinned->name = "hbb_pinned";
      $field_pinned->label = "Pinned";
      $field_pinned->autocheck = 0;
      $field_pinned->uncheckedValue = 0;
      $field_pinned->checkedValue = 1;
      $field_pinned->tags = 'HermodBB';
      $field_pinned->icon = 'thumb-tack';
      $field_pinned->save();
    }

    // Private Members
    if(!$this->fields->get('hbb_private_members')) {
      $field_private_members = new Field();
      $field_private_members->type = $this->modules->get("FieldtypePage");
      $field_private_members->name = 'hbb_private_members';
      $field_private_members->label = 'Allowed Members';
      $field_private_members->description = 'Which members are allowed to view this topic? Blank = public';
      $field_private_members->template_id = wire('templates')->get("user")->id; // Get the id number of the 'user' template
      $field_private_members->labelFieldName = 'name';
      $field_private_members->inputfield = 'InputfieldAsmSelect';
      $field_private_members->tags = 'HermodBB';
      $field_private_members->icon = 'unlock';
      $field_private_members->save();
    }

    // Topic Viewed
    if(!$this->fields->get('hbb_topic_viewed')) {
      $field_topic_viewed = new Field();
      $field_topic_viewed->type = $this->modules->get("FieldtypePage");
      $field_topic_viewed->name = 'hbb_topic_viewed';
      $field_topic_viewed->label = 'Topic Viewed';
      $field_topic_viewed->description = 'Which members have viewed this topic?';
      $field_topic_viewed->template_id = wire('templates')->get("user")->id; // Get the id number of the 'user' template
      $field_topic_viewed->labelFieldName = 'name';
      $field_topic_viewed->inputfield = 'InputfieldSelectMultiple';
      $field_topic_viewed->tags = 'HermodBB';
      $field_topic_viewed->icon = 'eye';
      $field_topic_viewed->save();
    }

    // Locked
    if(!$this->fields->get('hbb_locked')) {
      $field_locked = new Field();
      $field_locked->type = $this->modules->get("FieldtypeCheckbox");
      $field_locked->name = "hbb_locked";
      $field_locked->label = "Locked";
      $field_locked->autocheck = 0;
      $field_locked->uncheckedValue = 0;
      $field_locked->checkedValue = 1;
      $field_locked->tags = 'HermodBB';
      $field_locked->icon = 'lock';
      $field_locked->save();
    }

    // Topic Field
    if(!$this->fields->get('hbb_topic_content')) {
      $field_topic = new Field();
      $field_topic->type = $this->modules->get("FieldtypeTextarea");
      $field_topic->name = 'hbb_topic_content';
      $field_topic->label = 'Topic';
      $field_topic->tags = 'HermodBB';
      $field_topic->icon = 'file';
      $field_topic->save();
    }

    // Comment Fieldgroup
    if(!$this->fieldgroups->get('hbb_comment')) {
      $fg_comment_full = new Fieldgroup();
      $fg_comment_full->name = 'hbb_comment';
      $fg_comment_full->add($this->fields->get('title'));
      $fg_comment_full->add($field_pinned);
      $fg_comment_full->add($field_id);
      $fg_comment_full->add($field_ip);
      $fg_comment_full->add($field_agent);
      $fg_comment_full->add($field_date);
      $fg_comment_full->add($field_edit_date);
      $fg_comment_full->add($field_comment);
      $fg_comment_full->add($field_reply_id);
      $fg_comment_full->save();
    }

    // Comments Category Fieldgroup
    if(!$this->fieldgroups->get('hbb_comment_cat')) {
      $fg_comment_cat = new Fieldgroup();
      $fg_comment_cat->name = 'hbb_comment_cat';
      $fg_comment_cat->add($this->fields->get('title'));
      $fg_comment_cat->save();
    }

    // Topic Fieldgroup
    if(!$this->fieldgroups->get('hbb_topic')) {
      $fg_topic = new Fieldgroup();
      $fg_topic->name = 'hbb_topic';
      $fg_topic->add($this->fields->get('title'));
      $fg_topic->add($field_pinned);
      $fg_topic->add($field_locked);
      $fg_topic->add($field_topic_viewed);
      $fg_topic->add($field_private_members);
      $fg_topic->add($field_ip);
      $fg_topic->add($field_agent);
      $fg_topic->add($field_date);
      $fg_topic->add($field_edit_date);
      $fg_topic->add($field_last_date);
      $fg_topic->add($field_topic);
      $fg_topic->save();
    }

    // Forum Fieldgroup
    if(!$this->fieldgroups->get('hbb_forum')) {
      $fg_forum = new Fieldgroup();
      $fg_forum->name = 'hbb_forum';
      $fg_forum->add($this->fields->get('title'));
      $fg_forum->add($field_forum_description);
      $fg_forum->add($field_forum_view);
      $fg_forum->add($field_topic_post);
      $fg_forum->add($field_comments_view);
      $fg_forum->add($field_comments_post);
      $fg_forum->add($field_topic_pin);
      $fg_forum->add($field_topic_lock);
      $fg_forum->save();
    }

    // Forum Cat Fieldgroup
    if(!$this->fieldgroups->get('hbb_forum_cat')) {
      $fg_forum_cat = new Fieldgroup();
      $fg_forum_cat->name = 'hbb_forum_cat';
      $fg_forum_cat->add($this->fields->get('title'));
      $fg_forum_cat->add($field_forum_cat_view);
      $fg_forum_cat->save();
    }

    // BB / Forum Root Fieldgroup
    if(!$this->fieldgroups->get('hbb_bbroot')) {
      $fg_bbroot = new Fieldgroup();
      $fg_bbroot->name = 'hbb_bbroot';
      $fg_bbroot->add($this->fields->get('title'));
      $fg_bbroot->save();
    }

    // Comment Template
    if(!$this->templates->get('hbb_comment')) {
      $template_comment_full = new Template();
      $template_comment_full->name ='hbb_comment';
      $template_comment_full->fieldgroup = $fg_comment_full;
      $template_comment_full->label = 'HBB Comment';
      $template_comment_full->icon = 'comment';
      $template_comment_full->tags = 'HermodBB';
      $template_comment_full = $template_comment_full->save();
    }

    // Comments Category Template
    if(!$this->templates->get('hbb_comment_cat')) {
      $template_comment_cat = new Template();
      $template_comment_cat->name = 'hbb_comment_cat';
      $template_comment_cat->fieldgroup = $fg_comment_cat;
      $template_comment_cat->label = 'HBB Comments Category';
      $template_comment_cat->icon = 'comments';
      $template_comment_cat->tags = 'HermodBB';
      $template_comment_cat = $template_comment_cat->save();
    }

    // Topic Template
    if(!$this->templates->get('hbb_topic')) {
      $template_topic = new Template();
      $template_topic->name ='hbb_topic';
      $template_topic->fieldgroup = $fg_topic;
      $template_topic->label = 'HBB Topic';
      $template_topic->icon = 'file';
      $template_topic->tags = 'HermodBB';
      $template_topic = $template_topic->save();
    }

    // Forum Template
    if(!$this->templates->get('hbb_forum')) {
      $template_forum = new Template();
      $template_forum->name = 'hbb_forum';
      $template_forum->fieldgroup = $fg_forum;
      $template_forum->label = 'HBB Forum';
      $template_forum->icon = 'folder-open';
      $template_forum->tags = 'HermodBB';
      $template_forum->allowPageNum = 1;
      $template_forum->childTemplates = array(wire('templates')->get("hbb_topic")->id);
      $template_forum = $template_forum->save();
    }

    // Forum Cat Template
    if(!$this->templates->get('hbb_forum_cat')) {
      $template_forum_cat = new Template();
      $template_forum_cat->name = 'hbb_forum_cat';
      $template_forum_cat->fieldgroup = $fg_forum_cat;
      $template_forum_cat->label = 'HBB Forum Category';
      $template_forum_cat->icon = 'folder';
      $template_forum_cat->tags = 'HermodBB';
      $template_forum_cat->childTemplates = array(wire('templates')->get("hbb_forum")->id);
      $template_forum_cat = $template_forum_cat->save();
    }

    // BB / Forum Root Template
    if(!$this->templates->get('hbb_bbroot')) {
      $template_bbroot = new Template();
      $template_bbroot->name = 'hbb_bbroot';
      $template_bbroot->fieldgroup = $fg_bbroot;
      $template_bbroot->label = 'HBB Forum Root';
      $template_bbroot->icon = 'sitemap';
      $template_bbroot->tags = 'HermodBB';
      $template_bbroot->childTemplates = array(wire('templates')->get("hbb_forum_cat")->id);
      $template_bbroot = $template_bbroot->save();
    }


  }

  /**
   * Uninstall everything we installed previously
   *
   * @access public
   *
   */
  public function ___uninstall() {

    // Find out if there are any pages using the templates.
    $comment = wire('pages')->find('template=hbb_comment');
    $commentCat = wire('pages')->find('template=hbb_comment_cat');
    $topic = wire('pages')->find('template=hbb_topic');
    $forum = wire('pages')->find('template=hbb_forum');
    $forumCat = wire('pages')->find('template=hbb_forum_cat');
    $bbroot = wire('pages')->find('template=hbb_bbroot');

    if(count($comment))
      throw new WireException("Template hbb_comment is still in use, please delete any associated pages");
    if(count($commentCat))
      throw new WireException("Template hbb_comment_cat is still in use, please delete any associated pages");
    if(count($topic))
      throw new WireException("Template hbb_topic is still in use, please delete any associated pages");
    if(count($forum))
      throw new WireException("Template hbb_forum is still in use, please delete any associated pages");
    if(count($forumCat))
      throw new WireException("Template hbb_forum_cat is still in use, please delete any associated pages");
    if(count($bbroot))
      throw new WireException("Template hbb_bbroot is still in use, please delete any associated pages");

    // If there are no pages using the templates, it is safe to remove everything installed by this module
    //

    // Delete the templates
    $templates = wire('templates');
    $templates->delete($templates->get('hbb_comment_cat'));
    $templates->delete($templates->get('hbb_comment'));
    $templates->delete($templates->get('hbb_forum_cat'));
    $templates->delete($templates->get('hbb_forum'));
    $templates->delete($templates->get('hbb_topic'));
    $templates->delete($templates->get('hbb_bbroot'));

    // Delete the fieldgroups
    $fieldgroups = wire('fieldgroups');
    $fieldgroups->delete($fieldgroups->get('hbb_comment_cat'));
    $fieldgroups->delete($fieldgroups->get('hbb_comment'));
    $fieldgroups->delete($fieldgroups->get('hbb_forum'));
    $fieldgroups->delete($fieldgroups->get('hbb_forum_cat'));
    $fieldgroups->delete($fieldgroups->get('hbb_topic'));
    $fieldgroups->delete($fieldgroups->get('hbb_bbroot'));

    // Delete the fields
    $fields = wire('fields');
    $fields->delete($fields->get('hbb_comment_content'));
    $fields->delete($fields->get('hbb_comment_id'));
    $fields->delete($fields->get('hbb_ip'));
    $fields->delete($fields->get('hbb_agent'));
    $fields->delete($fields->get('hbb_date'));
    $fields->delete($fields->get('hbb_edate'));
    $fields->delete($fields->get('hbb_ldate'));
    $fields->delete($fields->get('hbb_pinned'));
    $fields->delete($fields->get('hbb_locked'));
    $fields->delete($fields->get('hbb_private_members'));
    $fields->delete($fields->get('hbb_forum_description'));
    $fields->delete($fields->get('hbb_p_forum_cat_view'));
    $fields->delete($fields->get('hbb_p_forum_view'));
    $fields->delete($fields->get('hbb_p_comments_post'));
    $fields->delete($fields->get('hbb_p_comments_view'));
    $fields->delete($fields->get('hbb_p_topic_start'));
    $fields->delete($fields->get('hbb_p_pin'));
    $fields->delete($fields->get('hbb_p_topic_lock'));
    $fields->delete($fields->get('hbb_topic_content'));
    $fields->delete($fields->get('hbb_topic_viewed'));
    $fields->delete($fields->get('hbb_reply_id'));
  }
}
