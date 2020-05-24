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
        '20'=>'In Library',
        '40'=>'On Loan',
        '70'=>'Lost',
        '80'=>'Sold',
        '90'=>'Archived',
    ));
    //
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
