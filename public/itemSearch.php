<?php
//
// Description
// -----------
// This method searchs for a Items for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Item for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_puzzlelibrary_itemSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'puzzlelibrary', 'private', 'checkAccess');
    $rc = ciniki_puzzlelibrary_checkAccess($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.itemSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of items
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
        . "ciniki_puzzlelibrary_items.owner_customer_id, "
        . "ciniki_puzzlelibrary_items.current_customer_id, "
        . "ciniki_puzzlelibrary_items.paid_amount, "
        . "ciniki_puzzlelibrary_items.unit_amount "
        . "FROM ciniki_puzzlelibrary_items "
        . "WHERE ciniki_puzzlelibrary_items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'status', 'flags', 'pieces', 'length', 'width', 'difficulty', 'owner_customer_id', 'current_customer_id', 'paid_amount', 'unit_amount')),
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
