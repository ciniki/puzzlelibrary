<?php
//
// Description
// -----------
// This method will return the list of Items for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Item for.
//
// Returns
// -------
//
function ciniki_puzzlelibrary_itemList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'puzzlelibrary', 'private', 'checkAccess');
    $rc = ciniki_puzzlelibrary_checkAccess($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.itemList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of items
    //
    $strsql = "SELECT items.id, "
        . "items.name, "
        . "items.permalink, "
        . "items.status, "
        . "items.flags, "
        . "items.pieces, "
        . "items.length, "
        . "items.width, "
        . "items.difficulty, "
        . "items.owner, "
        . "items.holder, "
        . "items.paid_amount, "
        . "items.unit_amount, "
        . "brands.tag_name AS brand "
        . "FROM ciniki_puzzlelibrary_items AS items "
        . "LEFT JOIN ciniki_puzzlelibrary_tags AS brands ON ("
            . "items.id = brands.item_id "
            . "AND brands.tag_type = 50 "
            . "AND brands.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'brand', 'status', 'flags', 'pieces', 
                'length', 'width', 'difficulty', 'owner', 'holder', 
                'paid_amount', 'unit_amount'),
            'dlists'=>array('brand'=>','),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['items']) ) {
        $items = $rc['items'];
        $item_ids = array();
        foreach($items as $iid => $item) {
            $item_ids[] = $item['id'];
        }
    } else {
        $items = array();
        $item_ids = array();
    }

    return array('stat'=>'ok', 'items'=>$items, 'nplist'=>$item_ids);
}
?>
