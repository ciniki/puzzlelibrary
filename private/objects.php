<?php
//
// Description
// -----------
// This function returns the list of objects for the module.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_puzzlelibrary_objects(&$ciniki) {
    //
    // Build the objects
    //
    $objects = array();
    $objects['item'] = array(
        'name' => 'Item',
        'sync' => 'yes',
        'o_name' => 'item',
        'o_container' => 'items',
        'table' => 'ciniki_puzzlelibrary_items',
        'fields' => array(
            'name' => array('name'=>'Name'),
            'permalink' => array('name'=>'Permalink', 'default'=>''),
            'status' => array('name'=>'Status', 'default'=>'20'),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            'pieces' => array('name'=>'Pieces', 'default'=>''),
            'length' => array('name'=>'Length', 'default'=>''),
            'width' => array('name'=>'Width', 'default'=>''),
            'difficulty' => array('name'=>'Difficulty', 'default'=>'0'),
            'primary_image_id' => array('name'=>'Image', 'ref'=>'ciniki.images.image', 'default'=>'0'),
            'description' => array('name'=>'Description', 'default'=>''),
            'owner' => array('name'=>'Owner', 'default'=>''),
            'holder' => array('name'=>'Current Holder', 'default'=>''),
            'paid_amount' => array('name'=>'Amount Paid', 'default'=>'0'),
            'unit_amount' => array('name'=>'Sold Price', 'default'=>'0'),
            'notes' => array('name'=>'Notes', 'default'=>''),
            ),
        'history_table' => 'ciniki_puzzlelibrary_history',
        );
    $objects['image'] = array(
        'name' => 'Image',
        'sync' => 'yes',
        'o_name' => 'image',
        'o_container' => 'images',
        'table' => 'ciniki_puzzlelibrary_images',
        'fields' => array(
            'item_id' => array('name'=>'Item', 'ref'=>'ciniki.puzzlelibrary.item'),
            'name' => array('name'=>'Name', 'default'=>''),
            'permalink' => array('name'=>'Permalink', 'default'=>''),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            'image_id' => array('name'=>'Image', 'ref'=>'ciniki.images.image'),
            'description' => array('name'=>'Description', 'default'=>''),
            ),
        'history_table' => 'ciniki_puzzlelibrary_history',
        );
    $objects['tag'] = array(
        'name' => 'Tag',
        'sync' => 'yes',
        'o_name' => 'tag',
        'o_container' => 'tags',
        'table' => 'ciniki_puzzlelibrary_tags',
        'fields' => array(
            'item_id' => array('name'=>'Item', 'ref'=>'ciniki.puzzlelibrary.item'),
            'tag_type' => array('name'=>'Tag Type'),
            'tag_name' => array('name'=>'Tag Name'),
            'permalink' => array('name'=>'Permalink'),
            ),
        'history_table' => 'ciniki_puzzlelibrary_history',
        );

    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
