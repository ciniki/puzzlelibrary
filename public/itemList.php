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
        'list'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'List'),
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
        'collection'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Collection'),
        'brand'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Brand'),
        'artist'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Artist'),
        'owner'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Owner'),
        'holder'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Holder'),
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
    if( $args['list'] == 'status' && isset($args['status']) && $args['status'] != '' ) {
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
            . "items.primary_image_id, "
            . "items.paid_amount, "
            . "items.unit_amount, "
            . "items.last_updated, "
            . "brands.tag_name AS brand "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "LEFT JOIN ciniki_puzzlelibrary_tags AS brands ON ("
                . "items.id = brands.item_id "
                . "AND brands.tag_type = 50 "
                . "AND brands.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        if( $args['status'] == 'visible' ) {
            $strsql .= "AND (items.flags&0x01) = 0x01 ";
        } elseif( $args['status'] == 'hidden' ) {
            $strsql .= "AND (items.flags&0x01) = 0 ";
        } elseif( $args['status'] == 'loaner' ) {
            $strsql .= "AND (items.flags&0x02) = 0x02 ";
        } elseif( $args['status'] == 'private' ) {
            $strsql .= "AND (items.flags&0x02) = 0 ";
        } elseif( is_numeric($args['status']) ) {
            $strsql .= "AND items.status = '" . ciniki_core_dbQuote($ciniki, $args['status']) . "' ";
        }
    } elseif( $args['list'] == 'categories' && isset($args['category']) && $args['category'] != '' ) {
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
            . "items.primary_image_id, "
            . "items.paid_amount, "
            . "items.unit_amount, "
            . "items.last_updated, "
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
    } elseif( $args['list'] == 'collections' && isset($args['collection']) && $args['collection'] != '' ) {
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
            . "items.primary_image_id, "
            . "items.paid_amount, "
            . "items.unit_amount, "
            . "items.last_updated, "
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
            . "AND tags.permalink = '" . ciniki_core_dbQuote($ciniki, $args['collection']) . "' "
            . "AND tags.tag_type = 20 "
            . "";
    } elseif( $args['list'] == 'brands' && isset($args['brand']) && $args['brand'] != '' ) {
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
            . "items.primary_image_id, "
            . "items.paid_amount, "
            . "items.unit_amount, "
            . "items.last_updated, "
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
    } elseif( $args['list'] == 'artists' && isset($args['artist']) && $args['artist'] != '' ) {
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
            . "items.primary_image_id, "
            . "items.paid_amount, "
            . "items.unit_amount, "
            . "items.last_updated, "
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
            . "AND tags.permalink = '" . ciniki_core_dbQuote($ciniki, $args['artist']) . "' "
            . "AND tags.tag_type = 60 "
            . "";
    } elseif( $args['list'] == 'owners' && isset($args['owner']) ) {
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
            . "items.primary_image_id, "
            . "items.paid_amount, "
            . "items.unit_amount, "
            . "items.last_updated, "
            . "brands.tag_name AS brand "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "LEFT JOIN ciniki_puzzlelibrary_tags AS brands ON ("
                . "items.id = brands.item_id "
                . "AND brands.tag_type = 50 "
                . "AND brands.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND items.owner = '" . ciniki_core_dbQuote($ciniki, $args['owner']) . "' "
            . "";
    } elseif( $args['list'] == 'holders' && isset($args['holder']) ) {
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
            . "items.primary_image_id, "
            . "items.paid_amount, "
            . "items.unit_amount, "
            . "items.last_updated, "
            . "brands.tag_name AS brand "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "LEFT JOIN ciniki_puzzlelibrary_tags AS brands ON ("
                . "items.id = brands.item_id "
                . "AND brands.tag_type = 50 "
                . "AND brands.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND items.holder = '" . ciniki_core_dbQuote($ciniki, $args['holder']) . "' "
            . "";

    } else {
        $strsql = "";
/*        $strsql = "SELECT items.id, "
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
            . ""; */
    }
    $items = array();
    $item_ids = array();
    if( $strsql != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'items', 'fname'=>'id', 
                'fields'=>array('id', 'name', 'permalink', 'brand', 'status', 'status_text', 'flags', 'pieces', 
                    'length', 'width', 'difficulty', 'owner', 'holder', 'primary_image_id', 'paid_amount', 'unit_amount', 'last_updated'),
                'maps'=>array('status_text'=>$maps['item']['status']),
                'dlists'=>array('brand'=>','),
                'utctots'=>array('last_updated'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['items']) ) {
            $items = $rc['items'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
            foreach($items as $iid => $item) {
                $item_ids[] = $item['id'];
                $items[$iid]['length_width'] = (int)($item['length']/10) . 'x' . (int)($item['width']/10);
                $items[$iid]['flags_text'] = '';
                if( ($item['flags']&0x01) == 0x01 ) {
                    $items[$iid]['flags_text'] .= 'Visible';
                }
                if( ($item['flags']&0x02) == 0x02 ) {
                    $items[$iid]['flags_text'] .= ($items[$iid]['flags_text'] != '' ? ', ' : '') . 'Loaner';
                }
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
        }
    }

    $rsp = array('stat'=>'ok', 'items'=>$items, 'nplist'=>$item_ids);

    //
    // Get the list of owners
    //
    if( $args['list'] == 'status' ) {
        $strsql = "SELECT status, "
            . "COUNT(items.id) AS num_items "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY status "
            . "ORDER BY status "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'statuslist', 'fname'=>'status', 'fields'=>array('status', 'num_items')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.23', 'msg'=>'Unable to load ', 'err'=>$rc['err']));
        }
        $statuslist = isset($rc['statuslist']) ? $rc['statuslist'] : array();
        $rsp['status'] = array(
            array('tag_name'=>'In Library', 'permalink'=>20, 'num_items'=>(isset($statuslist[20]) ? $statuslist[20]['num_items'] : 0)),
            array('tag_name'=>'On Loan', 'permalink'=>40, 'num_items'=>(isset($statuslist[40]) ? $statuslist[40]['num_items'] : 0)),
            array('tag_name'=>'Lost', 'permalink'=>70, 'num_items'=>(isset($statuslist[70]) ? $statuslist[70]['num_items'] : 0)),
            array('tag_name'=>'Sold', 'permalink'=>80, 'num_items'=>(isset($statuslist[80]) ? $statuslist[80]['num_items'] : 0)),
            array('tag_name'=>'Archived', 'permalink'=>90, 'num_items'=>(isset($statuslist[90]) ? $statuslist[90]['num_items'] : 0)),
            );

        $strsql = "SELECT (flags&0x03) AS type, "
            . "COUNT(items.id) AS num_items "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY type "
            . "ORDER BY type "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'types', 'fname'=>'type', 'fields'=>array('type', 'num_items')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.23', 'msg'=>'Unable to load ', 'err'=>$rc['err']));
        }
        $types = isset($rc['types']) ? $rc['types'] : array();
        $num_visible = 0;
        $num_loaner = 0;
        $num_total = 0;
        if( isset($types[0]['num_items']) ) {
            $num_total += $types[0]['num_items'];
        }
        if( isset($types[1]['num_items']) ) {
            $num_visible += $types[1]['num_items'];
            $num_total += $types[1]['num_items'];
        } 
        if( isset($types[2]['num_items']) ) {
            $num_loaner += $types[2]['num_items'];
            $num_total += $types[2]['num_items'];
        } 
        if( isset($types[3]['num_items']) ) {
            $num_visible += $types[3]['num_items'];
            $num_loaner += $types[3]['num_items'];
            $num_total += $types[3]['num_items'];
        }
        $rsp['flags'] = array(
            array('tag_name'=>'Visible', 'permalink'=>'visible', 'num_items'=>$num_visible),
            array('tag_name'=>'Hidden', 'permalink'=>'hidden', 'num_items'=>($num_total - $num_visible)),
            array('tag_name'=>'Loaner', 'permalink'=>'loaner', 'num_items'=>$num_loaner),
            array('tag_name'=>'Private', 'permalink'=>'private', 'num_items'=>($num_total - $num_loaner)),
            array('tag_name'=>'Total', 'permalink'=>'total', 'num_items'=>($num_total)),
            );
    }

    //
    // Get the list of categories, brands, collections
    //
    if( $args['list'] == 'categories' || $args['list'] == 'collections' || $args['list'] == 'brands' || $args['list'] == 'artists' ) {
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
        $types = isset($rc['types']) ? $rc['types'] : array();
        foreach($types as $type) {
            if( $type['tag_type'] == 10 ) {
                $rsp['categories'] = $type['categories'];
            } elseif( $type['tag_type'] == 20 ) {
                $rsp['collections'] = $type['categories'];
            } elseif( $type['tag_type'] == 50 ) {
                $rsp['brands'] = $type['categories'];
            } elseif( $type['tag_type'] == 60 ) {
                $rsp['artists'] = $type['categories'];
            }
        }
    }

    //
    // Get the list of owners
    //
    if( $args['list'] == 'owners' ) {
        $strsql = "SELECT owner AS name, "
            . "owner AS permalink, "
            . "COUNT(items.id) AS num_items "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY owner "
            . "ORDER BY owner "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'owners', 'fname'=>'permalink', 
                'fields'=>array('name', 'permalink', 'num_items'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.21', 'msg'=>'Unable to load owners', 'err'=>$rc['err']));
        }
        $rsp['owners'] = isset($rc['owners']) ? $rc['owners'] : array();
    }

    //
    // Get the list of holders
    //
    if( $args['list'] == 'holders' ) {
        $strsql = "SELECT holder AS name, "
            . "holder AS permalink, "
            . "COUNT(items.id) AS num_items "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY holder "
            . "ORDER BY holder "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'holders', 'fname'=>'permalink', 
                'fields'=>array('name', 'permalink', 'num_items'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.21', 'msg'=>'Unable to load holders', 'err'=>$rc['err']));
        }
        $rsp['holders'] = isset($rc['holders']) ? $rc['holders'] : array();
    }


    return $rsp;
}
?>
