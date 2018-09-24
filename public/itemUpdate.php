<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_puzzlelibrary_itemUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'item_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'),
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'),
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'pieces'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Pieces'),
        'length'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Length'),
        'width'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Width'),
        'difficulty'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Difficulty'),
        'primary_image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'),
        'owner_customer_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Owner'),
        'current_customer_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Current Holder'),
        'paid_amount'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Amount Paid'),
        'unit_amount'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sold Price'),
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
        'categories'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Categories'),
        'keywords'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Keywords'),
        'collections'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Collections'),
        'brands'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Brands'),
        'artists'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Artists'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'puzzlelibrary', 'private', 'checkAccess');
    $rc = ciniki_puzzlelibrary_checkAccess($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.itemUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    error_log(print_r($args['brands'], true));

    if( isset($args['name']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, name, permalink "
            . "FROM ciniki_puzzlelibrary_items "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['item_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.puzzlelibrary', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.10', 'msg'=>'You already have an item with this name, please choose another.'));
        }
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.puzzlelibrary');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the Item in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.item', $args['item_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.puzzlelibrary');
        return $rc;
    }

    //
    // Update the categories
    //
    if( isset($args['categories']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.puzzlelibrary', 'tag', $args['tnid'],
            'ciniki_puzzlelibrary_tags', 'ciniki_puzzlelibrary_history',
            'item_id', $args['item_id'], 10, $args['categories']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.puzzlelibrary');
            return $rc;
        }
    }

    //
    // Update the keywords
    //
    if( isset($args['keywords']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.puzzlelibrary', 'tag', $args['tnid'],
            'ciniki_puzzlelibrary_tags', 'ciniki_puzzlelibrary_history',
            'item_id', $args['item_id'], 20, $args['keywords']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.puzzlelibrary');
            return $rc;
        }
    }

    //
    // Update the collections
    //
    if( isset($args['collections']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.puzzlelibrary', 'tag', $args['tnid'],
            'ciniki_puzzlelibrary_tags', 'ciniki_puzzlelibrary_history',
            'item_id', $args['item_id'], 30, $args['collections']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.puzzlelibrary');
            return $rc;
        }
    }

    //
    // Update the brands
    //
    if( isset($args['brands']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.puzzlelibrary', 'tag', $args['tnid'],
            'ciniki_puzzlelibrary_tags', 'ciniki_puzzlelibrary_history',
            'item_id', $args['item_id'], 50, $args['brands']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.puzzlelibrary');
            return $rc;
        }
    }

    //
    // Update the artists
    //
    if( isset($args['artists']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.puzzlelibrary', 'tag', $args['tnid'],
            'ciniki_puzzlelibrary_tags', 'ciniki_puzzlelibrary_history',
            'item_id', $args['item_id'], 60, $args['artists']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.puzzlelibrary');
            return $rc;
        }
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.puzzlelibrary');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'puzzlelibrary');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.puzzlelibrary.item', 'object_id'=>$args['item_id']));

    return array('stat'=>'ok');
}
?>
