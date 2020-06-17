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
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'puzzlelibrary', 'private', 'maps');
    $rc = ciniki_puzzlelibrary_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the list of items
    //
    $strsql = "SELECT items.id, "
        . "items.name, "
        . "items.permalink, "
        . "items.status, "
        . "items.status AS status_text, "
        . "items.flags, "
        . "items.pieces, "
        . "items.length, "
        . "items.width, "
        . "items.owner, "
        . "items.holder, "
        . "items.primary_image_id, "
        . "items.paid_amount, "
        . "items.unit_amount, "
        . "items.last_updated, "
        . "history.new_value AS prev_holders "
        . "FROM ciniki_puzzlelibrary_items AS items "
        . "LEFT JOIN ciniki_puzzlelibrary_history AS history ON ("
            . "history.table_name = 'ciniki_puzzlelibrary_items' "
            . "AND items.id = history.table_key "
            . "AND history.table_field = 'holder' "
            . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR owner LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR owner LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR holder LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR holder LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "ORDER BY name ";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 35 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'status', 'status_text', 'flags', 'pieces', 'length', 'width', 'owner', 'holder', 
                'primary_image_id', 'paid_amount', 'unit_amount', 'last_updated', 'prev_holders',
                ),
            'maps'=>array('status_text'=>$maps['item']['status']),
            'dlists'=>array('prev_holders'=>', '),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['items']) ) {
        $items = $rc['items'];
        $item_ids = array();
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
        foreach($items as $iid => $item) {
            $item_ids[] = $item['id'];
            if( isset($item['primary_image_id']) && $item['primary_image_id'] > 0 ) {
                $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], array(
                    'image_id' => $item['primary_image_id'], 
                    'maxlength' => 150, 
                    'last_updated' => $item['last_updated'],
                    ));                
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $items[$iid]['image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
            }
        }
    } else {
        $items = array();
        $item_ids = array();
    }

    return array('stat'=>'ok', 'items'=>$items, 'nplist'=>$item_ids);
}
?>
