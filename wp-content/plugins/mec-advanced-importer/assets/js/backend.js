class MEC_ADVIMP_Main {

    constructor(el) {

        if (el && el != null && typeof el != undefined) {
            this.el = el;

            this.el.loading = jQuery('#mec-advimp-loading');
            this.el.alertSuccess = jQuery('#mec-advinp-alert-success');
            this.el.alertSuccessMessage = jQuery('#mec-advimp-alert-success-message');
            this.el.alertError = jQuery('#mec-advinp-alert-error');
            this.el.alertErrorMessage = jQuery('#mec-advimp-alert-error-message');
            this.el.timer = null;
            this.el.timer_stop = null;
            this.el.request_log_show = jQuery('#mec-advimp-showlog');
            this.el.select2 = jQuery('.mec-advimp-select2');
            this.el.category_select2 = jQuery('.mec-advimp-category-select2');

            this.el.single_account = jQuery('#mec-advimp-account-single');
            this.el.account_type = 'single';
            this.el.section = jQuery('#mec-advimp-page').val();
            // this.el.default_slected = null;

            if (typeof this.el.single_account.val() != 'undefined') {
                this.el.account_type = 'single';
            } else {
                this.el.select2.select2();
                this.el.account_type = 'select2';
            }

            if (typeof this.el.category_select2 != 'undefined') {
                this.el.category_select2.select2();
            }

        }
    }

    auth() {
        const that = this;
        this.progress('show');

        var win = window.open(this.el.url, '_blank', 'location=yes,height=570,width=520,scrollbars=yes,status=yes');
        var timer = setInterval(function() {
            if (win.closed) {
                clearInterval(timer);
                jQuery.ajax({
                        url: window.MEC_ADVIMP_VARS.ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: that.el.section + '_check_auth',
                            authid: that.el.authid
                        },
                    })
                    .done(function(data) {

                        if (data.success == true) {
                            that.successShow(data.data);
                            that.el.getAllEventBtn.attr('disabled', false);
                            that.el.AddToSync.attr('disabled', false);
                            that.el.btn.html(window.MEC_ADVIMP_VARS.title.authenticated);
                            that.el.btn.removeClass('button-info');
                            that.el.btn.addClass('button-success');
                        } else {
                            that.errorShow(data.data);
                            that.el.getAllEventBtn.attr('disabled', true);
                            that.el.AddToSync.attr('disabled', true);
                            that.el.btn.html(window.MEC_ADVIMP_VARS.title.needauthentication);
                        }

                    })
                    .fail(function(dataerr) {
                        that.errorShow(dataerr.message);
                    })
                    .always(function() {
                        that.progress('hide');
                    });
            }
        }, 1000);
    }

    getSelectedAccount() {

        if (this.el.default_slected !== null) {
            return JSON.stringify([this.el.default_slected]);
        }


        if (this.el.account_type == 'single') {
            return JSON.stringify([this.el.single_account.val()]);
        }

        const sel = this.el.select2.select2('data');

        if (typeof sel == undefined || sel == null || sel != '') {
            var sone = jQuery('#mec-advimp-selected-one').val();

            if (sone && sone != null && typeof sone != undefined) {
                return JSON.stringify([sone]);
            }
        }


        if (sel.length == 0) {
            return false;
        }

        let ret = [];
        for (var k in sel) {
            ret.push(sel[k]['id']);
        }

        return JSON.stringify(ret);

    }


    progress(s) {
        switch (s) {
            case 'show':
                this.el.btn.attr('disabled', true);
                this.el.loading.css('display', 'inline-block');
                this.alertHide();
                break;
            case 'hide':
                this.el.btn.attr('disabled', false);
                this.el.loading.hide();
                break;
        }
    }

    errorShow(msg) {

        if (msg && typeof msg !== undefined) {
            this.el.alertErrorMessage.html(msg);
        }

        this.el.alertSuccess.hide();
        this.el.alertError.show();
    }

    successShow(msg) {

        if (msg && typeof msg !== undefined) {
            this.el.alertSuccessMessage.html(msg);
        }

        this.el.alertSuccess.show();
        this.el.alertError.hide();
    }

    alertHide() {
        this.el.alertSuccess.hide();
        this.el.alertError.hide();
    }


    reqid() {
        this.el.reqid = Math.floor(new Date().valueOf() * Math.random());
        return this.el.reqid;
    }


    checkrequest() {
        const that = this;
        that.el.timer = null;
        that.el.timer_stop = false;
        that.el.request_log_show.html('');
        let seqid = 0;

        that.el.timer = setInterval(function() {

            if (that.el.timer_stop == true) {
                clearInterval(that.el.timer);
                return;
            }

            jQuery.ajax({
                    url: window.MEC_ADVIMP_VARS.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'mec_advimp_check_request',
                        reqid: that.el.reqid,
                        seqid: seqid
                    },
                })
                .done(function(d) {

                    for (var firstKey in d.data) break;

                    if (d.data[firstKey] == 'finish') {
                        that.el.timer_stop = true;
                    }

                    if (that.el.timer_stop == true) {
                        clearInterval(that.el.timer);
                        that.progress('hide');
                    }

                    for (var k in d.data) {
                        var key = 'advimp-' + k;
                        if (that.el.request_log_show.find(`[id='${key}']`).html() == undefined) {
                            var html = '<li id="advimp-' + k + '">' + k + '&nbsp;' + d.data[k] + '</li>';
                            that.el.request_log_show.parent().show();
                            that.el.request_log_show.append(html);
                        }
                    }
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
            seqid += 1;
        }, 500);
    }
}

class MEC_ADVIMP_Process extends MEC_ADVIMP_Main {

    constructor(def) {

        const el = jQuery.extend({
            url: null,
            btn: jQuery('.mec-advimp-action'),
            btnClick: jQuery('.mec-advimp-action'),
            getAllEventBtn: jQuery('#mec-advimp-getallevent'),
            AddToSync: jQuery('#mec-advimp-add-to-sync'),
            importBySelect: jQuery('#mec-advimp-importby-inp'),
            addAccountAsCategory: jQuery('#mec-advimp-add-account-as-category'),
            importBySelectVal: 'my',
            batchSection: jQuery('#mec-advimp-import-batch'),
            batch: window.MEC_ADVIMP_VARS.Facebook.batch,
            scheduledInp: jQuery('#mec-advimp-import-type-inp'),
            scheduledSection: jQuery('#mec-advimp-import-type-scheduled'),
            scheduledTypeInp: jQuery('#mec-advimp-import-type-scheduled-inp'),
            statusInp: jQuery('#mec-advimp-import-status'),
            sDate: jQuery('#mec-advimp-import-sdate'),
            eDate: jQuery('#mec-advimp-import-edate'),
            category: jQuery('#input[name="tax_input[mec_category][]"]'),
            // categoryElTop: jQuery('#mec-advimp-dialog-content-id-body').find('#mecadvimp-selectcategory-top'),
            // categoryElDown: jQuery('#mec-advimp-dialog-content-id-body').find('#mecadvimp-selectcategory-bottom'),
            next: true,
            authid: null,
            default_slected: null,
            calendar_id: null,
            account_title: null,
        }, def);


        super(el);
        this.el = el;
        this.addListeners();

        if (this.el.default_slected !== null) {
            this.getall(null);
        }

        if (this.el.calendar_id !== null) {

            this.el.default_slected = this.el.calendar_id;
            this.AddToSync(null);
        }
        // return this.el;
    }




    init_table() {

        var timer;
        var delay = 500;
        var that = this;

        jQuery('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function(e) {
            e.preventDefault();
            var query = this.search.substring(1);

            var data = {
                paged: that.__query_table(query, 'paged') || '1',
                order: that.__query_table(query, 'order') || 'asc',
                orderby: that.__query_table(query, 'orderby') || 'title'
            };
            that.update_table(data);
        });

        jQuery('input[name=paged]').on('keyup', function(e) {

            if (13 == e.which)
                e.preventDefault();

            var data = {
                paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                order: jQuery('input[name=order]').val() || 'asc',
                orderby: jQuery('input[name=orderby]').val() || 'title'
            };

            window.clearTimeout(timer);
            timer = window.setTimeout(function() {
                that.update_table(data);
            }, delay);
        });

        jQuery('#email-sent-list').on('submit', function(e) {

            e.preventDefault();

        });

        this.addListeners();

    }

    /** AJAX call
     *
     * Send the call and replace table parts with updated version!
     *
     * @param    object    data The data to pass through AJAX
     */
    update_table(data) {
        var that = this;

        jQuery.ajax({

            url: ajaxurl,
            data: jQuery.extend({
                    _ajax_custom_list_nonce: jQuery('#_ajax_custom_list_nonce').val(),
                    action: '_ajax_fetch_' + that.el.section + '_history',
                },
                data
            ),
            success: function(response) {

                var response = jQuery.parseJSON(response);

                if (response.rows.length)
                    jQuery('#the-list').html(response.rows);
                if (response.column_headers.length)
                    jQuery('thead tr, tfoot tr').html(response.column_headers);
                if (response.pagination.bottom.length)
                    jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
                if (response.pagination.top.length)
                    jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());

                that.init_table();
            }
        });
    }

    /**
     * Filter the URL Query to extract variables
     *
     * @see http://css-tricks.com/snippets/javascript/get-url-variables/
     *
     * @param    string    query The URL query part containing the variables
     * @param    string    variable Name of the variable we want to get
     *
     * @return   string|boolean The variable value if available, false else.
     */
    __query_table(query, variable) {

        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable)
                return pair[1];
        }
        return false;
    }


    directGetAll() {

        var data = window.MEC_ADVIMP_Table_Date;

        jQuery('#mec-advimp-dialog-content-id-body').html('');
        if (!data.hasOwnProperty('data')) {
            console.log('data not found');
            return;
        }

        if (!data.data.hasOwnProperty('table')) {
            console.log('data.data.table not found', data.data);
            return;
        }

        var that = this;
        that.el.section = 'ics';

        if (data.hasOwnProperty('data') && data.data.hasOwnProperty('table')) {

            setTimeout(function() {
                jQuery('#mec-advimp-dialog-content-id-body').html(data.data.table);
                jQuery("tbody").on("click", ".toggle-row", function(e) {
                    e.preventDefault();
                    jQuery(that).closest("tr").toggleClass("is-expanded")
                });
                that.init_table();
            }, 100);

        } else {
            jQuery('#mec-advimp-dialog-content-id-body').html('No Any Events!');
        }
    }


    // let getall = (saved_req) => {
    getall(saved_req) {
        const that = this;
        let req = {};

console.log(that);
        const selectedAccounts = that.getSelectedAccount();
        if (!selectedAccounts) {
            this.errorShow('Select Account!');
            return false;
        }

        that.progress('show');

        if (saved_req && saved_req != null) {
            req = saved_req;
        } else {
            const valSel = jQuery('#mec-advimp-importby-' + that.el.importBySelectVal + '-inp');
            let val = undefined;

            if (valSel[0].nodeName == 'teaxtarea') {
                val = valSel.html();
            } else {
                val = valSel.val();
            }

            if (!val || typeof val === undefined) {
                this.errorShow('Input Value');
                that.progress('hide');
                return false;
            }

            req = {
                importType: that.el.importBySelectVal,
                importTypeVal: val,
                scheduled: that.el.scheduledInp.val(),
                scheduledType: that.el.scheduledTypeInp.val(),
                status: that.el.statusInp.val(),
                action: that.el.section + '_get_events',
                reqid: that.reqid(),
                add_account_as_category: that.el.addAccountAsCategory.val(),
                account_title: that.el.account_title,
                selected: selectedAccounts,
                preview: 1,
                start_date: that.el.sDate.val(),
                end_date: that.el.eDate.val(),
                categoryTop: jQuery('#mec-advimp-dialog-content-id-body').find('#mecadvimp-selectcategory-top').val(),
                categoryBottom: jQuery('#mec-advimp-dialog-content-id-body').find('#mecadvimp-selectcategory-bottom').val(),
                linkmore: jQuery('.mec-options-fields').find('#mec-advimp-import-linkmore').is(':checked') ? 1 : 0,
            };

            var extra = jQuery(".mec-advimp-extra-field").map(function() {
                return {
                    'name': jQuery(this).attr('name'),
                    'val': jQuery(this).val()
                }
            }).get();
            if (extra.length > 0) {
                for (var k in extra) {
                    req[extra[k]['name']] = extra[k]['val'];
                }
            }

            that.checkrequest();
        }

        jQuery.ajax({
                url: window.MEC_ADVIMP_VARS.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: req,
            })
            .done(function(data) {

                req['request_next'] = null;

                jQuery('#mec-advimp-dialog-content-id-body').html('');

                if (data.hasOwnProperty('data') && data.data.hasOwnProperty('table')) {
                    jQuery('#mec-advimp-dialog-content-id-body').attr('data-req-id',data.data.post_id);
                    jQuery('#mec-advimp-dialog-content-id-body').html(data.data.table);

                    jQuery("tbody").on("click", ".toggle-row", function(e) {
                        e.preventDefault();
                        jQuery(this).closest("tr").toggleClass("is-expanded")
                    });
                    that.init_table();
                } else {
                    jQuery('#mec-advimp-dialog-content-id-body').html('No Any Events!');
                }

            })
            .fail(function(data) {
                console.log("error");
                req['request_next'] = null;
                that.el.next = false;
            })
            .always(function(data) {
                console.log("complete");
            });
    }

    AddToSync(saved_req = null) {
        const that = this;
        let req = {};

        const selectedAccounts = that.getSelectedAccount();
        if (!selectedAccounts) {
            this.errorShow('Select Account!');
            return false;
        }

        that.progress('show');

        if (saved_req && saved_req != null) {
            req = saved_req;
        } else {
            const valSel = jQuery('#mec-advimp-importby-' + that.el.importBySelectVal + '-inp');
            let val = undefined;

            if (valSel[0].nodeName == 'textarea') {
                val = valSel.html();
            } else {
                val = valSel.val();
            }

            if (!val || typeof val === undefined) {
                this.errorShow('Input Value');
                that.progress('hide');
                return false;
            }

            req = {
                importType: that.el.importBySelectVal,
                importTypeVal: val,
                scheduled: that.el.scheduledInp.val(),
                scheduledType: that.el.scheduledTypeInp.val(),
                status: that.el.statusInp.val(),
                action: that.el.section + '_add_to_sync',
                class: that.el.section,
                reqid: that.reqid(),
                selected: selectedAccounts,
                preview: 1,
                start_date: that.el.sDate.val(),
                end_date: that.el.eDate.val(),
                category: jQuery('#mec-advimp-import-category').val(),
                linkmore: jQuery('.mec-options-fields').find('#mec-advimp-import-linkmore').is(':checked') ? 1 : 0,
                update: jQuery('.mec-options-fields').find('#mec-advimp-import-update').is(':checked') ? 'yes' : 'no',
            };

            if( that.el.calendar_list_item && that.el.calendar_title ){

                req['calendar_list_item'] = that.el.calendar_list_item;
                req['calendar_title'] = that.el.calendar_title;
            }

            var extra = jQuery(".mec-advimp-extra-field").map(function() {
                return {
                    'name': jQuery(this).attr('name'),
                    'val': jQuery(this).val()
                }
            }).get();
            if (extra.length > 0) {
                for (var k in extra) {
                    req[extra[k]['name']] = extra[k]['val'];
                }
            }
        }

        jQuery.ajax({
                url: window.MEC_ADVIMP_VARS.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: req,
            })
            .done(function(data) {

                req['request_next'] = null;
                jQuery('#mec-advimp-dialog-content-id-body').html('');

                if (data.hasOwnProperty('data')) {
                    jQuery('#mec-advimp-dialog-content-id-body').html(data.data);
                }

                that.progress('hide');
            })
            .fail(function(data) {
                console.log("error");
                req['request_next'] = null;
            })
            .always(function(data) {
                console.log("complete");
            });
    }


    addListeners() {

        var that = this;

        this.el.btnClick.click(function(event) {

            const action = jQuery(this).attr('data-action');
            that.el.url = jQuery(this).attr('data-url');
            that.el.btn = jQuery(this);
            that.el.authid = jQuery(this).attr('data-authid');
            switch (action) {
                case 'auth':
                    that.auth();
                    break;
                case 'getall':

                    // call the callback funcs when the get all
                    const callback = jQuery('#mec-advimp-call-getall').val();
                    if (callback && typeof callback != 'undefined' && callback != null) {
                        window[callback]();
                    }


                    that.getall(null);
                    break;
                case 'add-to-schedule':

                    that.AddToSync();

                    break;

            }
        });


        this.el.importBySelect.change(function(event) {
            const selected = jQuery(this).val();

            // if (that.el.batch.indexOf(selected) !== -1) {
            //     that.el.batchSection.show();
            // } else {
            //     that.el.batchSection.hide();
            // }

            jQuery('.mec-advimp-import-option').hide();
            const sec = jQuery('#mec-advimp-importby-' + selected);
            sec.show();
            that.el.importBySelectVal = selected;

        });



        this.el.scheduledInp.change(function(event) {
            const selected = jQuery(this).val();
            if (selected == 'sheduled') {
                that.el.scheduledSection.show();
            } else {
                that.el.scheduledSection.hide();
            }
        });


        jQuery('.advimp-select').change(function(event) {
            that.showSelectedDownloadButton();
        });
        jQuery('#cb-select-all-1').change(function(event) {
            that.showSelectedDownloadButton();
        });
    }


    showSelectedDownloadButton() {
        var inputs = jQuery('input[type="checkbox"][name="event[]"]:checked').map(function() {
            return this.getAttribute("value");
        }).get();

        if (inputs.length > 0) {
            jQuery('#mec_advimp_download_selected_eventsbottom').show();
        } else {
            jQuery('#mec_advimp_download_selected_eventsbottom').hide();
        }
    }

}


class MEC_ADVIMP_Settings extends MEC_ADVIMP_Main {

    constructor() {
        super();
        this.changeUrl();
    }


    changeUrl() {
        var url = jQuery('#mec-advimp-reset-url').val();
        if (!url || typeof url == undefined) {
            return;
        }

        window.history.pushState({
            path: url
        }, '', url);
    }

}

function MEC_ADVIMP_sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}


function MEC_ADVIMP_Schedule(page, thus) {
    var inputs = jQuery('input[type="checkbox"][name="event[]"]:checked').map(function() {
        return this.getAttribute("data-id");
    }).get();

    if (inputs.length == 0) {
        return;
    }


    var send = {};
    for (var k in inputs) {

        var title = jQuery('#mec-advimp-privew-' + inputs[k]).html();
        var link = jQuery('#mec-advimp-privew-' + inputs[k]).attr('href');

        var id = jQuery('[data-id="'+inputs[k]+'"]').val();
        send[id] = {
            'title': title,
            'link': link
        };
    }

    var selectedCategory = [];

    selectedCategorySelect2 = jQuery('#mec-advimp-import-category').select2('data');
    if (selectedCategorySelect2.length > 0) {
        for (var kcat in selectedCategorySelect2) {
            selectedCategory.push(selectedCategorySelect2[kcat]['id']);
        }
    }


    var scheduled = jQuery('#mec-advimp-import-type-inp').val();
    var scheduledTypeInp = jQuery('#mec-advimp-import-type-scheduled-inp').val();

    jQuery.ajax({
            url: window.MEC_ADVIMP_VARS.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'mec_advimp_schedule_events',
                event_ids: JSON.stringify(send),
                class: page,
                scheduled: scheduled,
                scheduledType: scheduledTypeInp,
                category: JSON.stringify(selectedCategory)
            },
        })
        .done(function(d) {

            if (d.success == true) {
                jQuery('#mec-advimp-dialog-content-id-message b').html('Success Add Events To Schedule');
                jQuery('#mec-advimp-dialog-content-id-message').addClass('mec-success');
                jQuery('.mec-advimp-showlog #mec-advimp-showlog').append('<li>Success Add Events To Schedule.</li>');

            } else {
                jQuery('#mec-advimp-dialog-content-id-message b').html('Error Failed!' + d.data);
                jQuery('#mec-advimp-dialog-content-id-message').addClass('mec-error');
                // jQuery(thus).attr('disabled', false);
            }
        })
        .fail(function() {
            console.log("error");
        })
        .always(function() {
            console.log("complete");
        });

}



async function MEC_ADVIMP_Download_Ready(inputs, k, page, data_ids) {

    if (inputs.length == k) {
        html = '<li id="advimp-' + k + '">Finish Requested.</li>';
        jQuery('#mec-advimp-showlog').parent().show();
        jQuery('#mec-advimp-showlog').append(html);
        jQuery('#mec_advimp_download_selected_events').attr('disabled', false);
        jQuery('#mec-advimp-dialog-content-id-body').find('#mec_advimp_download_selected_eventsbottom').attr('disabled', false);
        jQuery('#mec-advimp-dialog-content-id-body').find('#mec_advimp_download_eventsbottom').attr('disabled', false);
        jQuery('#mec-advimp-dialog-content-id-body').find('#mec_advimp_download_eventstop').attr('disabled', false);
        return;
    }

    await MEC_ADVIMP_sleep(500);


    var cur = jQuery('#mec-advimp-download-status-' + data_ids[k]);
    cur.html('Waite ...');

    var selectedCategory = [];

    selectedCategorySelect2 = jQuery('#mec-advimp-import-category').select2('data');
    if (selectedCategorySelect2.length > 0) {
        for (var kcat in selectedCategorySelect2) {
            selectedCategory.push(selectedCategorySelect2[kcat]['id']);
        }
    }

    jQuery.ajax({
            url: window.MEC_ADVIMP_VARS.ajaxurl,
            type: 'POST',
            dataType: 'json',
            // async: false,
            data: {
                action: 'mec_advimp_download_single_event',
                event_id: inputs[k],
                class: page,
                category: JSON.stringify(selectedCategory),
                req_id: jQuery('#mec-advimp-dialog-content-id-body').data('req-id'),
            },
        })
        .done(function(d) {
            var html = '';

            if (d.success) {
                cur.html('Success');
                var link = '<a href="' + d.data.url + '">' + d.data.title + '</a>';
                var utitle = d.data.is_new == true ? 'Add Event: ' : 'Updated Event: ';
                html = '<li id="advimp-' + d.data.post_id + '">' + utitle + d.data.post_id + '&nbsp;' + link + '</li>';
            } else {
                cur.html('Failed');
                html = '<li id="advimp-' + d.data.post_id + '">Failed response,' + d.data + '</li>';
            }

            jQuery('#mec-advimp-showlog').parent().show();
            jQuery('#mec-advimp-showlog').append(html);

            MEC_ADVIMP_Download_Ready(inputs, k + 1, page, data_ids);
        })
        .fail(function() {
            // console.log("error");
        })
        .always(function() {
            // console.log("complete");
        });
}

function MEC_ADVIMP_Download_All(page, thus) {

    if (window.mev_advimp_all_data_events == null) {
        return;
    }

    if (window.mev_advimp_all_data_events.length == 0) {
        return;
    }

    var req = [];
    var uids = [];
    for (var k in window.mev_advimp_all_data_events) {
        req.push(window.mev_advimp_all_data_events[k]['ID']);
        uids.push(window.mev_advimp_all_data_events[k]['uid']);
    }

    jQuery(thus).attr('disabled', true);

    MEC_ADVIMP_Download_Ready(req, 0, page, uids);

}

function MEC_ADVIMP_Download_Accounts(page, thus, account) {

    var data = window.mev_advimp_all_data_accounts_events[account]['data'];
    var req = [];
    var uids = [];
    for (var k in data) {
        req.push(data[k]['ID']);
        uids.push(data[k]['uid']);
    }
    return false;


    jQuery(thus).attr('disabled', true);

    MEC_ADVIMP_Download_Ready(req, 0, page, uids);

}


function MEC_ADVIMP_Download(page, thus, selected) {


    if (selected != 'selected') {
        return MEC_ADVIMP_Download_All(page, thus);
    }


    var inputs = jQuery('input[type="checkbox"][name="event[]"]:checked').map(function() {
        return this.getAttribute("value");
    }).get();

    // clicked ids, for find the progress span element
    // on google get apis, error failed when the calendar_id=name.family@gmail.com
    // fixed error by md5 the ID and set to data-id, find and progress element by md5(ID)
    var data_ids = jQuery('input[type="checkbox"][name="event[]"]:checked').map(function() {
        return this.getAttribute("data-id");
    }).get();

    if (inputs.length == 0) {
        return;
    }

    jQuery(thus).attr('disabled', true);

    MEC_ADVIMP_Download_Ready(inputs, 0, page, data_ids);
}

function MEC_ADVIMP_Accounts_Events(default_slected, el) {
    new MEC_ADVIMP_Process({
        default_slected: default_slected,
        account_title: jQuery(el).closest('tr').find('.column-title a').text(),
    });
}

function MEC_ADVIMP_Add_Accounts_to_Sync(el) {

    new MEC_ADVIMP_Process({
        calendar_id: jQuery(el).data('calendar-id'),
        calendar_title: jQuery(el).data('calendar-title'),
        calendar_list_item: jQuery(el).data('calendar-list-item'),
    });
}

function MEC_ADVIMP_Accounts_Calendars(id) {

    // jQuery('#get_calendar_list').val('0');
    jQuery('#get_calendar_list_item').val(id);

    new MEC_ADVIMP_Process({
        default_slected: id
    });
}


function MEC_ADVIMP_Clear_Google_Query() {
    jQuery('#get_calendar_list_item').val('');
}


function MADVIMP_Show_Account(id, thus) {

    var data = jQuery.parseJSON(jQuery('#item-' + id).html());
    var html = '<h3>' + data.title + '<h3>';

    var tr = {
        'app_id': 'App ID',
        'app_secret': 'App Secret',
        'update_exists': 'Update Existing Event',
        'active': 'Active',
        'address': 'Site Address',
        'token': 'API Token',
        'client_id': 'Client ID',
        'client_secret': 'Client Secret',
        'key': 'Key',
        'secret': 'Secret'
    };

    for (var k in data.config) {

        if (k == 'need_auth' || k == 'id' || k == 'title') {
            continue;
        }

        var val = data.config[k];
        if (data.config[k] == 1) {
            val = 'yes';
        } else if (data.config[k] == 0) {
            val = 'no';
        }

        html += '<p style="font-weight: normal;">' + tr[k] + ': <strong>' + val + '</strong></p>';
    }
    jQuery('#mec-advimp-account-view-content').html(html);
    jQuery('#mec-advimp-account-view-click').click();

    return false;
}


function MEC_ADVIMP_Direct_Get_All() {
    var prs = new MEC_ADVIMP_Process();
    prs.directGetAll();
}

jQuery(document).ready(function($) {

    $('.mec-advimp-showlog').hide();

    $('.mec_date_picker').datepicker({
        changeYear: true,
        changeMonth: true,
        dateFormat: 'yy-mm-dd',
        gotoCurrent: true,
        yearRange: 'c-3:c+5',
    });


    new MEC_ADVIMP_Process();
    new MEC_ADVIMP_Settings();


});