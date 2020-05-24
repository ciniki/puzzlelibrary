<?php
//
// Description
// -----------
// This method will search a field for the search string provided.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to search.
// field:           The field to search.  Possible fields available to search are:
//
//                  - category
//                  - media
//                  - location
//                  - size
//                  - framed_size
//                  - price
//                  - location
//
// start_needle:    The search string to search the field for.
//
// limit:           (optional) Limit the number of results to be returned. 
//                  If the limit is not specified, the default is 25.
// 
// Returns
// -------
// <results>
//      <result name="Landscape" />
//      <result name="Portrait" />
// </results>
//
function ciniki_puzzlelibrary_searchField($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Search'), 
        'limit'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Limit'), 
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
    $rc = ciniki_puzzlelibrary_checkAccess($ciniki, $args['tnid'], 'ciniki.puzzlelibrary.searchField'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Reject if an unknown field
    //
    if( $args['field'] != 'owner'
        && $args['field'] != 'holder'
        && $args['field'] != 'pieces'
        && $args['field'] != 'length'
        && $args['field'] != 'width'
        ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.38', 'msg'=>'Unvalid search field'));
    }
    $strsql = "SELECT DISTINCT " . $args['field'] . " AS name "
        . "FROM ciniki_puzzlelibrary_items "
        . "WHERE ciniki_puzzlelibrary_items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND (" . $args['field']  . " LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "AND " . $args['field'] . " <> '' "
            . ") "
        . "";
    if( $args['field'] == 'year' ) {
        $strsql .= "ORDER BY " . $args['field'] . " COLLATE latin1_general_cs DESC ";
    } else {
        $strsql .= "ORDER BY " . $args['field'] . " COLLATE latin1_general_cs ";
    }
    if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
        array('container'=>'results', 'fname'=>'name', 'fields'=>array('name')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    return array('stat'=>'ok', 'results'=>$rc['results']);
}
?>
