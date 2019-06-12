<?php
//
// Description
// -----------
// This function will return the list of options for the module that can be set for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get puzzlelibrary for.
//
// args:            The possible arguments for posts
//
//
// Returns
// -------
//
function ciniki_puzzlelibrary_hooks_webOptions(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.puzzlelibrary']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.2', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }

    //
    // Get the settings from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'tnid', $tnid, 'ciniki.web', 'settings', 'page-puzzlelibrary');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        $settings = array();
    } else {
        $settings = $rc['settings'];
    }


    $options = array();
    $pages['ciniki.puzzlelibrary'] = array('name'=>'Puzzle Library', 'options'=>$options);
    $pages['ciniki.puzzlelibrary.categories'] = array('name'=>'Puzzle Library - Categories', 'options'=>$options);
//    $pages['ciniki.puzzlelibrary.keywords'] = array('name'=>'Puzzle Library - Categories', 'options'=>$options);
    $pages['ciniki.puzzlelibrary.brands'] = array('name'=>'Puzzle Library - Brands', 'options'=>$options);
    $pages['ciniki.puzzlelibrary.artists'] = array('name'=>'Puzzle Library - Artists', 'options'=>$options);
    $pages['ciniki.puzzlelibrary.latest'] = array('name'=>'Puzzle Library - Latest', 'options'=>$options);
    $pages['ciniki.puzzlelibrary.sizes'] = array('name'=>'Puzzle Library - Sizes', 'options'=>$options);

    return array('stat'=>'ok', 'pages'=>$pages);
}
?>
