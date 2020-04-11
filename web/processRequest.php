<?php
//
// Description
// -----------
// This function will process a web request for the blog module.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get post for.
//
// args:            The possible arguments for posts
//
//
// Returns
// -------
//
function ciniki_puzzlelibrary_web_processRequest(&$ciniki, $settings, $tnid, $args) {

    if( !isset($ciniki['tenant']['modules']['ciniki.puzzlelibrary']) ) { 
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.puzzlelibrary.11', 'msg'=>"I'm sorry, the page you requested does not exist."));    }
    $page = array(
        'title'=>$args['page_title'],
        'breadcrumbs'=>$args['breadcrumbs'],
        'blocks'=>array(),
        );

    //
    // Setup titles
    //  
    if( count($page['breadcrumbs']) == 0 ) {
        $page['breadcrumbs'][] = array('name'=>'Puzzle Library', 'url'=>$args['base_url']);
    }

    //
    // Default to display list of albums
    //  
    $ciniki['response']['head']['og']['url'] = $args['domain_base_url'];

    $base_url = $args['base_url'];

    //
    // Setup the base url as the base url for this page. This may be altered below
    // as the uri_split is processed, but we do not want to alter the original passed in.
    //  

    //
    // Parse the url
    //  
    $uri_split = $args['uri_split'];

    //
    // Setup the submenu
    //
    if( $args['module_page'] == 'ciniki.puzzlelibrary' ) {
        $strsql = "SELECT tags.tag_type, COUNT(items.id) AS num "
            . "FROM ciniki_puzzlelibrary_tags AS tags "
            . "LEFT JOIN ciniki_puzzlelibrary_items AS items ON ("
                . "tags.item_id = items.id "
                . "AND (items.flags&0x01) = 0x01 "
                . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE tags.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "GROUP BY tags.tag_type "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.puzzlelibrary', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.12', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
        }
        $page['submenu'] = array(); 
        if( isset($rc['rows']) ) {
            foreach($rc['rows'] as $row) {
                if( $row['tag_type'] == 10 && $row['num'] > 0 ) {
                    $page['submenu']['categories'] = array('name'=>'Categories', 'url'=>$args['base_url'] . '/categories', 'tag_type'=>10);
                } elseif( $row['tag_type'] == 20 && $row['num'] > 0 ) {
                    $page['submenu']['keywords'] = array('name'=>'Keywords', 'url'=>$args['base_url'] . '/keywords', 'tag_type'=>20);
                } elseif( $row['tag_type'] == 30 && $row['num'] > 0 ) {
                    $page['submenu']['collections'] = array('name'=>'Collections', 'url'=>$args['base_url'] . '/collections', 'tag_type'=>30);
                } elseif( $row['tag_type'] == 50 && $row['num'] > 0 ) {
                    $page['submenu']['brands'] = array('name'=>'Brands', 'url'=>$args['base_url'] . '/brands', 'tag_type'=>50);
                } elseif( $row['tag_type'] == 60 && $row['num'] > 0 ) {
                    $page['submenu']['artists'] = array('name'=>'Artists', 'url'=>$args['base_url'] . '/artists', 'tag_type'=>60);
                }
            }
        }

        $tag_type = 0;
        $display = 'items'; 
        if( count($page['submenu']) == 0 ) {
            $tag_type = 0;
            $display = 'items';
        } elseif( count($page['submenu']) == 1 ) {
            $display = 'tag';
            $item = array_pop($page['submenu']);
            $tag_type = $item['tag_type'];
            $page['submenu'] = array(); 
        } elseif( isset($uri_split[0]) && $uri_split[0] != '' && isset($page['submenu'][$uri_split[0]]) ) {
            $display = 'tag';
            $tag_type = $page['submenu'][$uri_split[0]]['tag_type'];
            $page['submenu'][$uri_split[0]]['selected'] = 'yes';
            $base_url .= '/' . $uri_split[0];
            $page['breadcrumbs'][] = array('name'=>$page['submenu'][$uri_split[0]]['name'], 'url'=>$base_url);
            array_shift($uri_split);
        } elseif( count($page['submenu']) > 1 ) {
            $display = 'tag';
            $tag = array_keys($page['submenu'])[0];
            $tag_type = $page['submenu'][$tag]['tag_type'];
            $page['submenu'][$tag]['selected'] = 'yes';
            $base_url .= '/' . $tag;
            $page['breadcrumbs'][] = array('name'=>$page['submenu'][$tag]['name'], 'url'=>$base_url);
        }

    } elseif( $args['module_page'] == 'ciniki.puzzlelibrary.categories' ) {
        $display = 'tag';
        $tag_type = 10;
    } elseif( $args['module_page'] == 'ciniki.puzzlelibrary.keywords' ) {
        $display = 'tag';
        $tag_type = 20;
    } elseif( $args['module_page'] == 'ciniki.puzzlelibrary.collections' ) {
        $display = 'tag';
        $tag_type = 30;
    } elseif( $args['module_page'] == 'ciniki.puzzlelibrary.brands' ) {
        $display = 'tag';
        $tag_type = 50;
    } elseif( $args['module_page'] == 'ciniki.puzzlelibrary.artists' ) {
        $display = 'tag';
        $tag_type = 60;
    } elseif( $args['module_page'] == 'ciniki.puzzlelibrary.latest' ) {
        $display = 'latest';
        $tag_type = 0;
    } elseif( $args['module_page'] == 'ciniki.puzzlelibrary.sizes' ) {
        $display = 'sizes';
        $tag_type = 0;
        if( isset($uri_split[0]) && $uri_split[0] != '' ) {
            $size_permalink = $uri_split[0];
            $base_url .= '/' . $size_permalink;
            array_shift($uri_split);
            $display = 'size';
        }
    } else {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.puzzlelibrary.19', 'msg'=>'Page not found'));
    }

    //
    // Check if a tag type is specified in submenu, and 
    // a tag was selected, show the items with that tag
    //
    if( $tag_type > 0 && isset($uri_split[0]) && $uri_split[0] != '' ) {
        $display = 'items';
        $tag_permalink = $uri_split[0];
        array_shift($uri_split);
        $strsql = "SELECT tag_name "
            . "FROM ciniki_puzzlelibrary_tags "
            . "WHERE permalink = '" . ciniki_core_dbQuote($ciniki, $tag_permalink) . "' "
            . "AND tag_type = '" . ciniki_core_dbQuote($ciniki, $tag_type) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.puzzlelibrary', 'tag');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.15', 'msg'=>'Unable to load tag', 'err'=>$rc['err']));
        }
        if( !isset($rc['tag']) ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.puzzlelibrary.16', 'msg'=>'Page not found'));
        }
        $tag = $rc['tag'];
        
        $base_url .= '/' . $tag_permalink;
        $page['breadcrumbs'][] = array('name'=>$tag['tag_name'], 'url'=>$base_url);
    }
    //
    // Check if an items is specified
    //
    if( isset($uri_split[0]) && $uri_split[0] != '' ) {
        $display = 'item';
        $item_permalink = $uri_split[0];
        array_shift($uri_split);
    }
  
    //
    // Show an item
    //
    if( $display == 'item' ) {
        //
        // Load the item
        //
        $strsql = "SELECT items.id, "
            . "items.name AS title, "
            . "items.permalink, "
            . "items.status, "
            . "items.flags, "
            . "items.pieces, "
            . "items.length, "
            . "items.width, "
            . "items.primary_image_id AS image_id, "
            . "items.description "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "WHERE items.permalink = '" . ciniki_core_dbQuote($ciniki, $item_permalink) . "' "
            . "AND (items.flags&0x01) = 0x01 "      // Visible
            . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.puzzlelibrary', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.17', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.puzzlelibrary.18', 'msg'=>'Unable to find requested item'));
        }
        $item = $rc['item'];

        $base_url .= '/' . $item_permalink;
        $page['breadcrumbs'][] = array('name'=>$item['title'], 'url'=>$base_url);

        // Prepare description
        $item['description'] .= ($item['description'] != '' ? '<br/><br/>' : '') 
            . '<b>Size:</b> ' . (float)($item['length']/10) . ' x ' . (float)($item['width']/10) . ' cm'
            . ' (' . (number_format($item['length']*0.03937, 0)) . ' x ' . (number_format($item['width']*0.03937, 0)) . ' inches)';
        if( $item['status'] == 20 && ($item['flags']&0x02) == 0x02 ) {
            $item['description'] .= "<br/><b>Status:</b> Available";
        } elseif( $item['status'] == 40 ) {
            $item['description'] .= "<br/><b>Status:</b> On Loan";
        } else {
            $item['description'] .= "<br/><b>Status:</b> Not Available";
        }
        $page['blocks'][] = array('type'=>'galleryimage', 'image'=>$item,
            'quality'=>'high',
            'size'=>'large',
            );
    }

    //
    // Show the list of items
    //
    elseif( $display == 'items' || $display == 'latest' || $display == 'size' ) {
        if( $display == 'latest' ) {
            $strsql = "SELECT items.id, "
                . "items.name, "
                . "items.permalink, "
                . "items.status, "
                . "items.flags, "
                . "items.pieces, "
                . "items.length, "
                . "items.width, "
                . "items.primary_image_id, "
                . "items.description, "
                . "'yes' AS is_details, "
                . "DATE_FORMAT(items.date_added, '%M %d, %Y') AS date_added "
                . "FROM ciniki_puzzlelibrary_items AS items "
                . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND (items.flags&0x01) = 0x01 "      // Visible
                . "AND items.primary_image_id > 0 "
                . "ORDER BY items.date_added DESC "
                . "LIMIT 20 "
                . "";
        } elseif( $display == 'size' ) {
            $strsql = "SELECT items.id, "
                . "items.name, "
                . "items.permalink, "
                . "items.status, "
                . "items.flags, "
                . "items.pieces, "
                . "items.length, "
                . "items.width, "
                . "items.primary_image_id, "
                . "items.description, "
                . "'yes' AS is_details, "
                . "DATE_FORMAT(items.date_added, '%M %d, %Y') AS date_added "
                . "FROM ciniki_puzzlelibrary_items AS items "
                . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND (items.flags&0x01) = 0x01 "      // Visible
                . "AND items.pieces = '" . ciniki_core_dbQuote($ciniki, $size_permalink) . "' "
                . "AND items.primary_image_id > 0 "
                . "ORDER BY items.name "
                . "LIMIT 20 "
                . "";
        } elseif( $tag_type != 0 ) {
            $strsql = "SELECT items.id, "
                . "items.name, "
                . "items.permalink, "
                . "items.status, "
                . "items.flags, "
                . "items.pieces, "
                . "items.length, "
                . "items.width, "
                . "items.primary_image_id, "
                . "items.description, "
                . "'yes' AS is_details, "
                . "DATE_FORMAT(items.date_added, '%M %d, %Y') AS date_added "
                . "FROM ciniki_puzzlelibrary_tags AS tags "
                . "INNER JOIN ciniki_puzzlelibrary_items AS items ON ("
                    . "tags.item_id = items.id "
                    . "AND items.primary_image_id > 0 "
                    . "AND (items.flags&0x01) = 0x01 "      // Visible
                    . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                    . ") "
                . "WHERE tags.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' ";
            if( isset($tag_permalink) && $tag_permalink != '' ) {
                $strsql .= "AND tags.permalink = '" . ciniki_core_dbQuote($ciniki, $tag_permalink) . "' ";
            }
            $strsql .= "ORDER BY items.pieces ASC, items.date_added DESC "
                . "";
        } else {
            $strsql = "SELECT items.id, "
                . "items.name, "
                . "items.permalink, "
                . "items.status, "
                . "items.flags, "
                . "items.pieces, "
                . "items.length, "
                . "items.width, "
                . "items.primary_image_id, "
                . "items.description, "
                . "'yes' AS is_details, "
                . "DATE_FORMAT(items.date_added, '%M %d, %Y') AS date_added "
                . "FROM ciniki_puzzlelibrary_items AS items "
                . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND (items.flags&0x01) = 0x01 "      // Visible
                . "AND items.primary_image_id > 0 "
                . "ORDER BY items.pieces ASC, items.date_added DESC "
                . "";
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'items', 'fname'=>'id', 
                'fields'=>array('id', 'name', 'permalink', 'status', 'flags',
                    'pieces', 'length', 'width', 'image_id'=>'primary_image_id', 'description', 'is_details', 'date_added'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.13', 'msg'=>'Unable to load items', 'err'=>$rc['err']));
        }
        $items = isset($rc['items']) ? $rc['items'] : array();
        
        //
        // Prepare synopsis
        //
        foreach($items as $iid => $item) {
            $items[$iid]['name'] .= ' (' . $item['pieces'] . ')';
            $items[$iid]['synopsis'] = '';
            if( $item['status'] == 20 && ($item['flags']&0x02) == 0x02 ) {
                $items[$iid]['synopsis'] .= "<b>Status:</b> Available";
            } elseif( $item['status'] == 40 ) {
                $items[$iid]['synopsis'] .= "<b>Status:</b> On Loan";
            } else {
                $items[$iid]['synopsis'] .= "<b>Status:</b> Not Available";
            }
            $items[$iid]['synopsis'] .= '<br/><b>Size:</b> ' . (float)($item['length']/10) . ' x ' . (float)($item['width']/10) . ' cm'
                . ' (' . (number_format($item['length']*0.03937, 0)) . ' x ' . (number_format($item['width']*0.03937, 0)) . ' inches)';
            $items[$iid]['synopsis'] .= '<br/><b>Added:</b> ' . $item['date_added'];
            if( $item['description'] != '' ) {
                $items[$iid]['synopsis'] .= '<br/><br/>' . $item['description'];
            }
        }

        $page['blocks'][] = array('type'=>'imagelist', 'base_url'=>$base_url, 'image_width'=>300, 'noimage'=>'yes', 'list'=>$items);
    } 
    
    elseif( $display == 'tag' ) {
        //
        // Get the tag names
        //
        $strsql = "SELECT tags.tag_name, "
            . "tags.permalink, "
            . "items.id AS item_id, "
            . "items.primary_image_id "
            . "FROM ciniki_puzzlelibrary_tags AS tags "
            . "INNER JOIN ciniki_puzzlelibrary_items AS items ON ("
                . "tags.item_id = items.id "
                . "AND items.primary_image_id > 0 "
                . "AND (items.flags&0x01) = 0x01 "      // Visible
                . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE tags.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND tags.tag_type = '" . ciniki_core_dbQuote($ciniki, $tag_type) . "' "
            . "ORDER BY tags.tag_name, items.date_added DESC "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'tags', 'fname'=>'permalink', 
                'fields'=>array('title'=>'tag_name', 'permalink', 'image_id'=>'primary_image_id'),
                ),
            array('container'=>'items', 'fname'=>'item_id', 
                'fields'=>array('id'=>'item_id', 'image_id'=>'primary_image_id'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.14', 'msg'=>'Unable to load tags', 'err'=>$rc['err']));
        }
        $tags = isset($rc['tags']) ? $rc['tags'] : array();
        foreach($tags as $tid => $tag) {
            if( $tag['image_id'] == 0 && isset($tag['items']) ) {
                foreach($tag['items'] as $item) {
                    if( $item['image_id'] > 0 ) {
                        $tags[$tid]['image_id'] = $item['image_id'];
                        break;
                    }
                }
            }
        }

        $page['blocks'][] = array('type'=>'tagimages', 'base_url'=>$base_url, 'tags'=>$tags);
    }
    elseif( $display == 'sizes' ) {
        //
        // Get the sizes available
        //
        $strsql = "SELECT items.pieces, "
            . "items.id AS item_id, "
            . "items.primary_image_id "
            . "FROM ciniki_puzzlelibrary_items AS items "
            . "WHERE items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND items.primary_image_id > 0 "
            . "AND (items.flags&0x01) = 0x01 "      // Visible
            . "ORDER BY items.pieces, items.date_added DESC "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.puzzlelibrary', array(
            array('container'=>'sizes', 'fname'=>'pieces', 
                'fields'=>array('title'=>'pieces', 'permalink'=>'pieces', 'image_id'=>'primary_image_id'),
                ),
            array('container'=>'items', 'fname'=>'item_id', 
                'fields'=>array('id'=>'item_id', 'image_id'=>'primary_image_id'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.puzzlelibrary.20', 'msg'=>'Unable to load tags', 'err'=>$rc['err']));
        }
        $sizes = isset($rc['sizes']) ? $rc['sizes'] : array();
        foreach($sizes as $sid => $size) {
            if( $size['image_id'] == 0 && isset($size['items']) ) {
                foreach($size['items'] as $item) {
                    if( $item['image_id'] > 0 ) {
                        $sizes[$sid]['image_id'] = $item['image_id'];
                        break;
                    }
                }
            }
        }

        $page['blocks'][] = array('type'=>'tagimages', 'base_url'=>$base_url, 'tags'=>$sizes);
    }

//    $page['blocks'][] = array('type'=>'content', 'html'=>'<pre>' . print_r($args, true) . "</pre>"); 

    return array('stat'=>'ok', 'page'=>$page);
}
?>
