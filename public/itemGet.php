<?php
//
// Description
// ===========
// This method will return all the information about an item.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the item is attached to.
// item_id:          The ID of the item to get the details for.
//
// Returns
// -------
//
function ciniki_puzzlelibrary_itemGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'item_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'),
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
    $rc = ciniki_puzzlelibrary_checkAccess($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.itemGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Item
    //
    if( $args['item_id'] == 0 ) {
        $item = array('id'=>0,
            'name'=>'',
            'permalink'=>'',
            'status'=>'20',
            'flags'=>'0',
            'pieces'=>'',
            'length'=>'',
            'width'=>'',
            'difficulty'=>'0',
            'primary_image_id'=>'',
            'description'=>'',
            'owner_customer_id'=>'0',
            'current_customer_id'=>'0',
            'paid_amount'=>'0',
            'unit_amount'=>'0',
            'notes'=>'',
        );
    }

    //
    // Get the details for an existing Item
    //
    else {
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
            . "ciniki_puzzlelibrary_items.owner_customer_id, "
            . "ciniki_puzzlelibrary_items.current_customer_id, "
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
                'fields'=>array('name', 'permalink', 'status', 'flags', 'pieces', 'length', 'width', 'difficulty', 'primary_image_id', 'description', 'owner_customer_id', 'current_customer_id', 'paid_amount', 'unit_amount', 'notes'),
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
        // Get the categories
        //
        $strsql = "SELECT tag_type, tag_name AS names "
            . "FROM ciniki_puzzlelibrary_tags "
            . "WHERE item_id = '" . ciniki_core_dbQuote($ciniki, $args['item_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY tag_type, tag_name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'tags', 'fname'=>'tag_type', 
                'fields'=>array('tag_type', 'names'), 'dlists'=>array('names'=>'::')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tags']) ) {
            foreach($rc['tags'] as $tags) {
                if( $tags['tag_type'] == 10 ) {
                    $item['categories'] = $tags['names'];
                } elseif( $tags['tag_type'] == 20 ) {
                    $item['keywords'] = $tags['names'];
                } elseif( $tags['tag_type'] == 30 ) {
                    $item['collections'] = $tags['names'];
                } elseif( $tags['tag_type'] == 50 ) {
                    $item['brands'] = $tags['names'];
                } elseif( $tags['tag_type'] == 60 ) {
                    $item['artists'] = $tags['names'];
                }
            }
        }
    }
    $rsp = array('stat'=>'ok', 'item'=>$item);

    //
    // Check if all tags should be returned
    //
    $strsql = "SELECT DISTINCT tag_type, tag_name AS names "
        . "FROM ciniki_puzzlelibrary_tags "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY tag_type, tag_name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.lapt', array(
        array('container'=>'types', 'fname'=>'tag_type', 'fields'=>array('type'=>'tag_type', 'names'), 
            'dlists'=>array('names'=>'::')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['types']) ) {
        foreach($rc['types'] as $tid => $type) {
            if( $type['type'] == 10 ) {
                $rsp['categories'] = explode('::', $type['names']);
            } elseif( $type['type'] == 20 ) {
                $rsp['keywords'] = explode('::', $type['names']);
            } elseif( $type['type'] == 30 ) {
                $rsp['collections'] = explode('::', $type['names']);
            } elseif( $type['type'] == 50 ) {
                $rsp['brands'] = explode('::', $type['names']);
            } elseif( $type['type'] == 60 ) {
                $rsp['artists'] = explode('::', $type['names']);
            }
        }
    }

    return $rsp;
}
?>
