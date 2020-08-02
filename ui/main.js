//
// This is the main app for the puzzlelibrary module
//
function ciniki_puzzlelibrary_main() {
    //
    // The panel to list the item
    //
    this.menu = new M.panel('item', 'ciniki_puzzlelibrary_main', 'menu', 'mc', 'large narrowaside', 'sectioned', 'ciniki.puzzlelibrary.main.menu');
    this.menu.status = '';
    this.menu.category = '';
    this.menu.collection = '';
    this.menu.brand = '';
    this.menu.owner = '';
    this.menu.holder = '';
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
/*        'types':{'label':'Document Types', 'type':'simplegrid', 'aside':'yes', 'num_cols':2,
            'visible':'no',
            'noData':'No categories',
            'cellClasses':['', 'alignright'],
            }, */
        'tabs':{'label':'', 'type':'menutabs', 'selected':'categories', 'tabs':{
            'status':{'label':'Status', 'fn':'M.ciniki_puzzlelibrary_main.menu.switchTab("status");'},
            'categories':{'label':'Categories', 'fn':'M.ciniki_puzzlelibrary_main.menu.switchTab("categories");'},
            'collections':{'label':'Collections', 'fn':'M.ciniki_puzzlelibrary_main.menu.switchTab("collections");'},
            'brands':{'label':'Brands', 'fn':'M.ciniki_puzzlelibrary_main.menu.switchTab("brands");'},
            'artists':{'label':'Artists', 'fn':'M.ciniki_puzzlelibrary_main.menu.switchTab("artists");'},
            'owners':{'label':'Owners', 'fn':'M.ciniki_puzzlelibrary_main.menu.switchTab("owners");'},
            'holders':{'label':'Holders', 'fn':'M.ciniki_puzzlelibrary_main.menu.switchTab("holders");'},
            }},
        'status':{'label':'Status', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'status' ? 'yes' : 'no'; },
            'noData':'No statuses',
            'cellClasses':['', 'alignright'],
            }, 
        'flags':{'label':'Flags', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'status' ? 'yes' : 'no'; },
            'noData':'No flags',
            'cellClasses':['', 'alignright'],
            }, 
        'categories':{'label':'Categories', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'categories' ? 'yes' : 'no'; },
            'noData':'No categories',
            'cellClasses':['', 'alignright'],
            }, 
        'collections':{'label':'Collections', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'collections' ? 'yes' : 'no'; },
            'noData':'No collections',
            'cellClasses':['', 'alignright'],
            }, 
        'brands':{'label':'Brands', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'brands' ? 'yes' : 'no'; },
            'noData':'No brands',
            'cellClasses':['', 'alignright'],
            }, 
        'artists':{'label':'Artists', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'artists' ? 'yes' : 'no'; },
            'noData':'No artists',
            'cellClasses':['', 'alignright'],
            }, 
        'owners':{'label':'Owners', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'owners' ? 'yes' : 'no'; },
            'noData':'No owners',
            'cellClasses':['', 'alignright'],
            }, 
        'holders':{'label':'Holders', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            'visible':function() { return M.ciniki_puzzlelibrary_main.menu.sections.tabs.selected == 'holders' ? 'yes' : 'no'; },
            'noData':'No holders',
            'cellClasses':['', 'alignright'],
            }, 
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':5,
            'headerValues':['Image', 'Title', 'Status', 'Current Holder'],
            'cellClasses':['Image', 'Title', 'Status', 'Current Holder'],
            'hint':'Search item',
            'noData':'No item found',
            },
        'items':{'label':'Items', 'type':'simplegrid', 'num_cols':3,
            'headerValues':['Image', 'Info','Location'],
            'cellClasses':['thumbnail', 'multiline', 'multiline', 'multiline'],
            'sortable':'yes',
            'sortTypes':['', 'text', 'text', 'number', 'text', 'text'],
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
        switch(j) {
            case 0:
                if( d.primary_image_id > 0 && d.image != '' ) {
                    return '<img width="120px" height="120px" src=\'' + d.image + '\'/>';
                }
                return '<img width="120px" height="120px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
                
            case 1: return d.name;
            case 2: return d.status_text;
            case 3: return d.prev_holders.replace(/,/g, '<br/>');
            case 4: 
                if( d.status == 20 && this.holder != '' && this.sections.tabs.selected == 'holders' ) {
                    return d.holder + '<br/><button onclick="event.stopPropagation(); M.ciniki_puzzlelibrary_main.menu.loanItem(' + d.id + ');">Loan to ' + unescape(this.holder) + '</button>';
                }
                return d.holder;
        }
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.ciniki_puzzlelibrary_main.item.open(\'M.ciniki_puzzlelibrary_main.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.rowClass = function(s, i, d) {
        if( s == 'status' && this.status == d.permalink ) {
            return 'highlight';
        } else if( s == 'flags' && this.status == d.permalink ) {
            return 'highlight';
        } else if( s == 'categories' && this.category == d.permalink ) {
            return 'highlight';
        } else if( s == 'collections' && this.collection == d.permalink ) {
            return 'highlight';
        } else if( s == 'brands' && this.brand == d.permalink ) {
            return 'highlight';
        } else if( s == 'artists' && this.artist == d.permalink ) {
            return 'highlight';
        } else if( s == 'owners' && this.owner == d.permalink ) {
            return 'highlight';
        } else if( s == 'holders' && this.holder == d.permalink ) {
            return 'highlight';
        }
        return '';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'flags' || s == 'status' || s == 'categories' || s == 'collections' || s == 'brands' || s == 'artists' ) {
            return d.tag_name + '<span class="count">' + d.num_items + '</span>';
        }
        if( s == 'owners' || s == 'holders' ) {
            return d.name + '<span class="count">' + d.num_items + '</span>';
        }
        if( s == 'items' && j == 0 ) {
            if( d.primary_image_id > 0 && d.image != '' ) {
                return '<img width="120px" height="120px" src=\'' + d.image + '\'/>';
            } else {

            }
        }
        if( s == 'items' ) {
            switch(j) {
                case 0:
                    if( d.primary_image_id > 0 && d.image != '' ) {
                        return '<img width="100px" height="100px" src=\'' + d.image + '\'/>';
                    }
                    return '<img width="100px" height="100px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
                    
                case 1: return '<span class="maintext">' + d.name + '</span>'
                    + '<span class="subtext">' + d.brand + '</span>'
                    + '<span class="subtext">' + ((d.flags&0x02) == 0x02 ? 'Loaner' : 'Private' ) + ' [' + ((d.flags&0x01) == 0x01 ? 'Visible' : '' ) + ']</span>';
//                    + '<span class="subtext">' + d.status_text + ' [' + d.holder + ']</span>'
                    + '<span class="subsubtext">' + d.pieces + '</span>';
//                case 2: return '<span class="maintext">' + ((d.flags&0x02) == 0x02 ? 'Loaner' : 'Private' ) + '</span><span class="subtext">' + ((d.flags&0x01) == 0x01 ? 'Visible' : '' ) + '</span>';
                case 2: return '<span class="maintext">' + d.status_text + '</span><span class="subtext">' + d.holder + '</span>'
                    + (d.status == 40 ? '<button onclick="event.stopPropagation(); M.ciniki_puzzlelibrary_main.menu.returnItem(' + d.id + ');">Returned</button>': '');
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'status' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchStatus("' + M.eU(d.permalink) + '");';
        }
        if( s == 'flags' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchStatus("' + M.eU(d.permalink) + '");';
        }
        if( s == 'categories' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchCategory("' + M.eU(d.permalink) + '");';
        }
        if( s == 'collections' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchCollection("' + M.eU(d.permalink) + '");';
        }
        if( s == 'brands' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchBrand("' + M.eU(d.permalink) + '");';
        }
        if( s == 'artists' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchArtist("' + M.eU(d.permalink) + '");';
        }
        if( s == 'owners' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchOwner("' + M.eU(d.permalink) + '");';
        }
        if( s == 'holders' ) {
            return 'M.ciniki_puzzlelibrary_main.menu.switchHolder("' + M.eU(d.permalink) + '");';
        }
        if( s == 'items' ) {
            return 'M.ciniki_puzzlelibrary_main.item.open(\'M.ciniki_puzzlelibrary_main.menu.open();\',\'' + d.id + '\',M.ciniki_puzzlelibrary_main.item.nplist);';
        }
    }
    this.menu.switchTab = function(t) {
        this.sections.tabs.selected = t;
        this.open();
    }
    this.menu.switchStatus = function(c) {
        this.status = c;
        this.open();
    }
    this.menu.switchCategory = function(c) {
        this.category = c;
        this.open();
    }
    this.menu.switchCollection = function(b) {
        this.collection = b;
        this.open();
    }
    this.menu.switchBrand = function(b) {
        this.brand = b;
        this.open();
    }
    this.menu.switchArtist = function(b) {
        this.artist = b;
        this.open();
    }
    this.menu.switchOwner = function(b) {
        this.owner = b;
        this.open();
    }
    this.menu.switchHolder = function(b) {
        this.holder = b;
        this.open();
    }
    this.menu.loanItem = function(id) {
        M.api.getJSONCb('ciniki.puzzlelibrary.itemAction', {'tnid':M.curTenantID, 'action':'loan', 'item_id':id, 'holder':this.holder}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.ciniki_puzzlelibrary_main.menu.open();
        });
    }
    this.menu.returnItem = function(id) {
        M.api.getJSONCb('ciniki.puzzlelibrary.itemAction', {'tnid':M.curTenantID, 'action':'returned', 'item_id':id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.ciniki_puzzlelibrary_main.menu.open();
        });
    }
    this.menu.open = function(cb) {
        var args = {'tnid':M.curTenantID, 'list':this.sections.tabs.selected};
        if( this.sections.tabs.selected == 'status' ) { 
            args['status'] = this.status;
        } else if( this.sections.tabs.selected == 'categories' ) { 
            args['category'] = this.category;
        } else if( this.sections.tabs.selected == 'brands' ) { 
            args['brand'] = this.brand;
        } else if( this.sections.tabs.selected == 'artists' ) { 
            args['artist'] = this.artist;
        } else if( this.sections.tabs.selected == 'owners' ) { 
            args['owner'] = this.owner;
        } else if( this.sections.tabs.selected == 'holders' ) { 
            args['holder'] = this.holder;
        }
        
        M.api.getJSONCb('ciniki.puzzlelibrary.itemList', args, function(rsp) {
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
    this.menu.addButton('add', 'Add', 'M.ciniki_puzzlelibrary_main.item.open(\'M.ciniki_puzzlelibrary_main.menu.open();\',0,null);');
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
            'pieces':{'label':'Pieces', 'required':'yes', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
            'length':{'label':'Length (mm)', 'required':'yes', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
            'width':{'label':'Width (mm)', 'required':'yes', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
//            'difficulty':{'label':'Difficulty', 'type':'text'},
            'owner':{'label':'Owner', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'holder':{'label':'Current Holder', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
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
    this.item.liveSearchCb = function(s, i, value) {
        if( i == 'owner' || i == 'holder' || i == 'pieces' || i == 'length' || i == 'width' ) {
            M.api.getJSONBgCb('ciniki.puzzlelibrary.searchField', 
                {'tnid':M.curTenantID, 'field':i, 'start_needle':value, 'limit':15},
                function(rsp) {
                    M.ciniki_puzzlelibrary_main.item.liveSearchShow(s, i, M.gE(M.ciniki_puzzlelibrary_main.item.panelUID + '_' + i), rsp.results);
                });
        }
    };
    this.item.liveSearchResultValue = function(s, f, i, j, d) {
        if( (f == 'owner' || f == 'holder' || f == 'pieces' || f == 'length' || f == 'width' ) && d != null ) { 
            return d.name;
        }
        return '';
    };
    this.item.liveSearchResultRowFn = function(s, f, i, j, d) {
        if( (f == 'owner' || f == 'holder' || f == 'pieces' || f == 'length' || f == 'width') && d != null ) {
            return 'M.ciniki_puzzlelibrary_main.item.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.name) + '\');';
        }
    };
    this.item.updateField = function(s, fid, result) {
        M.gE(this.panelUID + '_' + fid).value = unescape(result);
        this.removeLiveSearch(s, fid);
    };
    this.item.open = function(cb, iid, list) {
        if( iid != null ) { this.item_id = iid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.puzzlelibrary.itemGet', {'tnid':M.curTenantID, 'item_id':this.item_id, 'category':M.ciniki_puzzlelibrary_main.menu.category, 'brand':M.ciniki_puzzlelibrary_main.menu.brand}, function(rsp) {
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
