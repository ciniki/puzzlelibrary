//
// This is the main app for the puzzlelibrary module
//
function ciniki_puzzlelibrary_main() {
    //
    // The panel to list the item
    //
    this.menu = new M.panel('item', 'ciniki_puzzlelibrary_main', 'menu', 'mc', 'medium', 'sectioned', 'ciniki.puzzlelibrary.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
/*        'types':{'label':'Document Types', 'type':'simplegrid', 'aside':'yes', 'num_cols':2,
            'visible':'no',
            'noData':'No categories',
            'cellClasses':['', 'alignright'],
            }, */
/*        'categories':{'label':'Categories', 'type':'simplegrid', 'aside':'yes', 'num_cols':2,
            'visible':'no',
            'noData':'No categories',
            'cellClasses':['', 'alignright'],
            'editFn':{},
            }, */
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':1,
            'cellClasses':[''],
            'hint':'Search item',
            'noData':'No item found',
            },
        'items':{'label':'Item', 'type':'simplegrid', 'num_cols':3,
            'headerValues':['Brand', 'Title', 'Pieces'],
            'sortable':'yes',
            'sortTypes':['text', 'text', 'number'],
            'noData':'No item',
            'addTxt':'Add Item',
            'addFn':'M.ciniki_puzzlelibrary_main.item.open(\'M.ciniki_puzzlelibrary_main.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('ciniki.puzzlelibrary.itemSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.ciniki_puzzlelibrary_main.menu.liveSearchShow('search',null,M.gE(M.ciniki_puzzlelibrary_main.menu.panelUID + '_' + s), rsp.items);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        return d.name;
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.ciniki_puzzlelibrary_main.item.open(\'M.ciniki_puzzlelibrary_main.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'items' ) {
            switch(j) {
                case 0: return d.brand;
                case 1: return d.name;
                case 2: return d.pieces;
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'items' ) {
            return 'M.ciniki_puzzlelibrary_main.item.open(\'M.ciniki_puzzlelibrary_main.menu.open();\',\'' + d.id + '\',M.ciniki_puzzlelibrary_main.item.nplist);';
        }
    }
    this.menu.open = function(cb) {
        M.api.getJSONCb('ciniki.puzzlelibrary.itemList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_puzzlelibrary_main.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to edit Item
    //
    this.item = new M.panel('Item', 'ciniki_puzzlelibrary_main', 'item', 'mc', 'medium mediumaside', 'sectioned', 'ciniki.puzzlelibrary.main.item');
    this.item.data = null;
    this.item.item_id = 0;
    this.item.nplist = [];
    this.item.sections = {
        'general':{'label':'', 'aside':'yes', 'fields':{
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'20':'In Library', '40':'On Loan', '70':'Lost', '80':'Sold', '90':'Archived'}},
            'flags':{'label':'Options', 'type':'flags', 'flags':{'1':{'name':'Visible'}, '2':{'name':'Loaner'}}},
            'pieces':{'label':'Pieces', 'type':'text', 'size':'small'},
            'length':{'label':'Length (mm)', 'type':'text', 'size':'small'},
            'width':{'label':'Width (mm)', 'type':'text', 'size':'small'},
//            'difficulty':{'label':'Difficulty', 'type':'text'},
            'owner_customer_id':{'label':'Owner', 'type':'select', 'options':{}},
            'current_customer_id':{'label':'Current Holder', 'type':'select', 'options':{}},
            'paid_amount':{'label':'Amount Paid', 'type':'text', 'size':'small'},
            'unit_amount':{'label':'Sold Price', 'type':'text', 'size':'small'},
            }},
        '_categories':{'label':'Categories', 'aside':'yes', 
            'fields':{
                'categories':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new category: '},
            }},
        '_collections':{'label':'Collections', 'aside':'yes', 
            'fields':{
                'collections':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new collection: '},
            }},
        '_brands':{'label':'Brands', 'aside':'yes', 
            'fields':{
                'brands':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new brand: '},
            }},
        '_artists':{'label':'Artist', 'aside':'yes', 
            'fields':{
                'artists':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new artist: '},
            }},
        '_primary_image_id':{'label':'Image', 'type':'imageform', 'fields':{
            'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                'addDropImage':function(iid) {
                    M.ciniki_puzzlelibrary_main.item.setFieldValue('primary_image_id', iid);
                    return true;
                    },
                'addDropImageRefresh':'',
             },
        }},
        '_description':{'label':'Description', 'fields':{
            'description':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
            }},
        '_notes':{'label':'Notes', 'fields':{
            'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_puzzlelibrary_main.item.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_puzzlelibrary_main.item.item_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_puzzlelibrary_main.item.remove();'},
            }},
        };
    this.item.fieldValue = function(s, i, d) { return this.data[i]; }
    this.item.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.puzzlelibrary.itemHistory', 'args':{'tnid':M.curTenantID, 'item_id':this.item_id, 'field':i}};
    }
    this.item.open = function(cb, iid, list) {
        if( iid != null ) { this.item_id = iid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.puzzlelibrary.itemGet', {'tnid':M.curTenantID, 'item_id':this.item_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_puzzlelibrary_main.item;
            p.data = rsp.item;
            p.sections._categories.fields.categories.tags = rsp.categories != null ? rsp.categories : [];
            p.sections._collections.fields.collections.tags = rsp.collections != null ? rsp.collections : [];
            p.sections._brands.fields.brands.tags = rsp.brands != null ? rsp.brands : [];
            p.sections._artists.fields.artists.tags = rsp.artists != null ? rsp.artists : [];
            p.refresh();
            p.show(cb);
        });
    }
    this.item.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_puzzlelibrary_main.item.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.item_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.puzzlelibrary.itemUpdate', {'tnid':M.curTenantID, 'item_id':this.item_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.puzzlelibrary.itemAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_puzzlelibrary_main.item.item_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.item.remove = function() {
        if( confirm('Are you sure you want to remove item?') ) {
            M.api.getJSONCb('ciniki.puzzlelibrary.itemDelete', {'tnid':M.curTenantID, 'item_id':this.item_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_puzzlelibrary_main.item.close();
            });
        }
    }
    this.item.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.item_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_puzzlelibrary_main.item.save(\'M.ciniki_puzzlelibrary_main.item.open(null,' + this.nplist[this.nplist.indexOf('' + this.item_id) + 1] + ');\');';
        }
        return null;
    }
    this.item.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.item_id) > 0 ) {
            return 'M.ciniki_puzzlelibrary_main.item.save(\'M.ciniki_puzzlelibrary_main.item_id.open(null,' + this.nplist[this.nplist.indexOf('' + this.item_id) - 1] + ');\');';
        }
        return null;
    }
    this.item.addButton('save', 'Save', 'M.ciniki_puzzlelibrary_main.item.save();');
    this.item.addClose('Cancel');
    this.item.addButton('next', 'Next');
    this.item.addLeftButton('prev', 'Prev');

    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = M.createContainer(ap, 'ciniki_puzzlelibrary_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}
