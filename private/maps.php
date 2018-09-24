<?php
//
// Description
// -----------
// This function returns the int to text mappings for the module.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_puzzlelibrary_maps(&$ciniki) {
    //
    // Build the maps object
    //
    $maps = array();
    $maps['item'] = array('status'=>array(
        '10'=>'Private',
    ));
    //
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
