<?php
/**
 *
 * When present, the module will be configurable and the configurable properties
 * described here will be automatically populated to the module at runtime. 
 * 
 * 
 */

$config = array(

  'hbbForumName' => array(
    'name' => 'hbbForumName',
    'label' => 'Forum Name', 
    'description' => 'The name of your forum. $forumName = $hbb->hbbForumName;',
    'type' => 'text',
    'value' => 'HermodBB'
  ),

  'hbbForumRules' => array(
    'name' => 'hbbForumRules',
    'label' => 'Forum Rules',
    'description' => 'Forum rules and guidelines. $forumRules = $hbb->hbbForumRules;',
    'type' => 'textarea',
    'value' => "Your basic forum rules can go here."
  ),

  'hbbForumOnline' => array(
    'name' => 'hbbForumOnline',
    'label' => 'Forum Online?',
    'description' => 'You can check the status with $fOnline = $hbb->hbbForumOnline; 1 is returned if checked, 0 for unchecked',
    'type' => 'checkbox',
    'value' => 1
  ),  
  
  'hbbForumOffMsg' => array(
    'name' => 'hbbForumOffMsg',
    'label' => 'Forum Offline Message',
    'description' => 'A message or notice to display when the forum is offline $fOfflineMsg = $hbb->hbbForumOffMsg',
    'type' => 'textarea',
    'value' => "The forum is currently offline."
  ),

  'hbbCommentsOnline' => array(
    'name' => 'hbbCommentsOnline',
    'label' => 'Comments Online?',
    'description' => 'You can check the status with $cOnline = $hbb->hbbCommentsOnline; 1 is returned if checked, 0 for unchecked',
    'type' => 'checkbox',
    'value' => 1
  ),

  'hbbCommentsOffMsg' => array(
    'name' => 'hbbCommentsOffMsg',
    'label' => 'Comments Offline Message',
    'description' => 'A message or notice to display when the comments are offline $cOfflineMsg = $hbb->hbbCommentsOffMsg',
    'type' => 'textarea',
    'value' => "Comments are currently disabled."
  ),

  'hbbTopicsPerPage' => array(
    'name' => 'hbbTopicsPerPage',
    'label' => 'Topics per page',
    'description' => 'How many topics should be displayed before pagination? $topicsPerPage = $hbb->hbbTopicsPerPage;',
    'type' => 'integer',
    'value' => 10
  ),

  'hbbCommentsPerPage' => array(
    'name' => 'hbbCommentsPerPage',
    'label' => 'Comments (replies) per page', 
    'description' => 'How many comments should be displayed before pagination? $commentsPerPage = $hbb->hbbCommentsPerPage;',
    'type' => 'integer',
    'value' => 10
  ),   
);