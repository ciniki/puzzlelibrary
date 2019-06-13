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
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
        'brand'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Brand'),
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
    if( isset($args['category']) && $args['category'] != '' ) {
        $strsql = "SELECT items.id, "
            . "items.name, "
            . "items.permalink, "
            . "items.status, "
            . "items.status AS status_text, "
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
            . "FROM ciniki_puzzlelibrary_tags AS tags "
            . "LEFT JOIN ciniki_puzzlelibrary_items AS items ON ("
                . "tags.item_id = items.id "
                . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "LEFT JOIN ciniki_puzzlelibrary_tags AS brands ON ("
                . "items.id = brands.item_id "
                . "AND brands.tag_type = 50 "
                . "AND brands.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND tags.permalink = '" . ciniki_core_dbQuote($ciniki, $args['category']) . "' "
            . "AND tags.tag_type = 10 "
            . "";
    } elseif( isset($args['brand']) && $args['brand'] != '' ) {
        $strsql = "SELECT items.id, "
            . "items.name, "
            . "items.permalink, "
            . "items.status, "
            . "items.status AS status_text, "
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
            . "FROM ciniki_puzzlelibrary_tags AS tags "
            . "LEFT JOIN ciniki_puzzlelibrary_items AS items ON ("
                . "tags.item_id = items.id "
                . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "LEFT JOIN ciniki_puzzlelibrary_tags AS brands ON ("
                . "items.id = brands.item_id "
                . "AND brands.tag_type = 50 "
                . "AND brands.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND tags.permalink = '" . ciniki_core_dbQuote($ciniki, $args['brand']) . "' "
            . "AND tags.tag_type = 50 "
            . "";

    } else {
        $strsql = "SELECT items.id, "
            . "items.name, "
            . "items.permalink, "
            . "items.status, "
            . "items.status AS status_text, "
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
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'brand', 'status', 'status_text', 'flags', 'pieces', 
                'length', 'width', 'difficulty', 'owner', 'holder', 'paid_amount', 'unit_amount'),
            'maps'=>array('status_text'=>$maps['item']['status']),
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
            $items[$iid]['length_width'] = (int)($item['length']/10) . 'x' . (int)($item['width']/10);
        }
    } else {
        $items = array();
        $item_ids = array();
    }

    $rsp = array('stat'=>'ok', 'items'=>$items, 'nplist'=>$item_ids);

    //
    // Get the list of categories
    //
    $strsql = "SELECT tags.tag_type, "
        . "tags.permalink, "
        . "tags.tag_name, "
        . "COUNT(items.id) AS num_items "
        . "FROM ciniki_puzzlelibrary_tags AS tags "
        . "INNER JOIN ciniki_puzzlelibrary_items AS items ON ("
            . "tags.item_id = items.id "
            . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE tags.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "GROUP BY tags.tag_type, tags.permalink "
        . "ORDER BY tags.tag_type, tags.permalink "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
        array('container'=>'types', 'fname'=>'tag_type', 'fields'=>array('tag_type')),
        array('container'=>'categories', 'fname'=>'permalink', 
            'fields'=>array('tag_name', 'permalink', 'num_items'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.21', 'msg'=>'Unable to load categories', 'err'=>$rc['err']));
    }
    foreach($rc['types'] as $type) {
        if( $type['tag_type'] == 10 ) {
            $rsp['categories'] = $type['categories'];
        } elseif( $type['tag_type'] == 50 ) {
            $rsp['brands'] = $type['categories'];
        }
    }


    return $rsp;
}
?>
