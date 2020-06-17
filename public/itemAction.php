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
function ciniki_puzzlelibrary_itemAction(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'item_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'),
        'action'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Action'),
        'holder'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Holder'),
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
    $rc = ciniki_puzzlelibrary_checkAccess($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.itemAction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current details about the item
    //
    $strsql = "SELECT ciniki_puzzlelibrary_items.id, "
        . "ciniki_puzzlelibrary_items.name, "
        . "ciniki_puzzlelibrary_items.permalink, "
        . "ciniki_puzzlelibrary_items.status, "
        . "ciniki_puzzlelibrary_items.flags, "
        . "ciniki_puzzlelibrary_items.pieces, "
        . "ciniki_puzzlelibrary_items.length, "
        . "ciniki_puzzlelibrary_items.width, "
        . "ciniki_puzzlelibrary_items.difficulty, "
        . "ciniki_puzzlelibrary_items.primary_image_id, "
        . "ciniki_puzzlelibrary_items.description, "
        . "ciniki_puzzlelibrary_items.owner, "
        . "ciniki_puzzlelibrary_items.holder, "
        . "ciniki_puzzlelibrary_items.paid_amount, "
        . "ciniki_puzzlelibrary_items.unit_amount, "
        . "ciniki_puzzlelibrary_items.notes "
        . "FROM ciniki_puzzlelibrary_items "
        . "WHERE ciniki_puzzlelibrary_items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_puzzlelibrary_items.id = '" . ciniki_core_dbQuote($ciniki, $args['item_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('name', 'permalink', 'status', 'flags', 'pieces', 'length', 'width', 'difficulty', 'primary_image_id', 'description', 'owner', 'holder', 'paid_amount', 'unit_amount', 'notes'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.8', 'msg'=>'Item not found', 'err'=>$rc['err']));
    }
    if( !isset($rc['items'][0]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.9', 'msg'=>'Unable to find Item'));
    }
    $item = $rc['items'][0];

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


    if( $args['action'] == 'loan' && isset($args['holder']) ) {
        //
        // Update the Item in the database
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
        $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.item', $args['item_id'], array(
            'status' => 40,
            'holder' => $args['holder'],
            ), 0x04);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.puzzlelibrary');
            return $rc;
        }
    }

    if( $args['action'] == 'returned' ) {
        //
        // Update the Item in the database
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
        $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.item', $args['item_id'], array(
            'status' => 20,
            'holder' => $item['owner'],
            ), 0x04);
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
