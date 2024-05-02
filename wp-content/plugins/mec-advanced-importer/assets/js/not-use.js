class MEC_ADVIMP_Meetup extends MEC_ADVIMP_Main {

    constructor() {

        const el = {
            url: null,
            btn: null,
            btnClick: jQuery('.mec-advimp-action'),
            getAllEventBtn: jQuery('#mec-advimp-getallevent'),
            importBySelect: jQuery('#mec-advimp-importby-inp'),
            importBySelectVal: 'my',
            batchSection: jQuery('#mec-advimp-import-batch'),
            // batch: window.MEC_ADVIMP_VARS.Facebook.batch,
            scheduledInp: jQuery('#mec-advimp-import-type-inp'),
            scheduledSection: jQuery('#mec-advimp-import-type-scheduled'),
            scheduledTypeInp: jQuery('#mec-advimp-import-type-scheduled-inp'),
            statusInp: jQuery('#mec-advimp-import-status'),
            next: true

        };

        super(el);
        this.el = el;
        this.addListeners();
        return this.el;
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
                            action: 'meetup_check_auth'
                        },
                    })
                    .done(function(data) {

                        if (data.success == true) {
                            that.successShow(data.data);
                            that.el.getAllEventBtn.attr('disabled', false);
                        } else {
                            that.errorShow(data.data);
                            that.el.getAllEventBtn.attr('disabled', true);
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


    getall(saved_req) {
        const that = this;
        that.progress('show');
        let req = {};

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
                action: 'facebook_get_events',
                reqid: that.reqid()
            };

            that.checkrequest();
        }

        jQuery.ajax({
                url: window.MEC_ADVIMP_VARS.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: req,
            })
            .done(function(data) {
                console.log("success", data);

                if (data.success == true && data.data.next == true) {
                    req['request_next'] = data.data.post_id;
                    that.getall(req);
                } else if (data.success == true && data.data.next == false) {
                    req['request_next'] = null;
                } else {
                    req['request_next'] = null;
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


    addListeners() {

        var that = this;

        this.el.btnClick.click(function(event) {

            const action = jQuery(this).attr('data-action');
            that.el.url = jQuery(this).attr('data-url');
            that.el.btn = jQuery(this);
            switch (action) {
                case 'auth':
                    that.auth();
                    break;
                    // case 'getall':
                    //     that.getall(null);
                    //     break;
            }
        });


        this.el.importBySelect.change(function(event) {
            const selected = jQuery(this).val();

            if (that.el.batch.indexOf(selected) !== -1) {
                that.el.batchSection.show();
            } else {
                that.el.batchSection.hide();
            }

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
    }
}


class MEC_ADVIMP_Google extends MEC_ADVIMP_Main {

    constructor() {

        const el = {
            url: null,
            btn: null,
            btnClick: jQuery('.mec-advimp-action'),
            getAllEventBtn: jQuery('#mec-advimp-getallevent'),
            importBySelect: jQuery('#mec-advimp-importby-inp'),
            importBySelectVal: 'my',
            batchSection: jQuery('#mec-advimp-import-batch'),
            // batch: window.MEC_ADVIMP_VARS.Facebook.batch,
            scheduledInp: jQuery('#mec-advimp-import-type-inp'),
            scheduledSection: jQuery('#mec-advimp-import-type-scheduled'),
            scheduledTypeInp: jQuery('#mec-advimp-import-type-scheduled-inp'),
            statusInp: jQuery('#mec-advimp-import-status'),
            next: true,
            authid: null,
            section: 'google'

        };

        super(el);
        this.el = el;
        this.addListeners();
        return this.el;
    }

    getall(saved_req) {
        const that = this;
        that.progress('show');
        let req = {};

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
                action: 'facebook_get_events',
                reqid: that.reqid()
            };

            that.checkrequest();
        }

        jQuery.ajax({
                url: window.MEC_ADVIMP_VARS.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: req,
            })
            .done(function(data) {
                console.log("success", data);

                if (data.success == true && data.data.next == true) {
                    req['request_next'] = data.data.post_id;
                    that.getall(req);
                } else if (data.success == true && data.data.next == false) {
                    req['request_next'] = null;
                } else {
                    req['request_next'] = null;
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
                    that.getall(null);
                    break;
            }
        });


        this.el.importBySelect.change(function(event) {
            const selected = jQuery(this).val();

            if (that.el.batch.indexOf(selected) !== -1) {
                that.el.batchSection.show();
            } else {
                that.el.batchSection.hide();
            }

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
    }
}


class MEC_ADVIMP_Eventbrite extends MEC_ADVIMP_Main {

    constructor() {

        const el = {
            url: null,
            btn: null,
            btnClick: jQuery('.mec-advimp-action'),
            getAllEventBtn: jQuery('#mec-advimp-getallevent'),
            importBySelect: jQuery('#mec-advimp-importby-inp'),
            importBySelectVal: 'my',
            batchSection: jQuery('#mec-advimp-import-batch'),
            batch: window.MEC_ADVIMP_VARS.Facebook.batch,
            scheduledInp: jQuery('#mec-advimp-import-type-inp'),
            scheduledSection: jQuery('#mec-advimp-import-type-scheduled'),
            scheduledTypeInp: jQuery('#mec-advimp-import-type-scheduled-inp'),
            statusInp: jQuery('#mec-advimp-import-status'),
            next: true

        };

        super(el);
        this.el = el;
        this.addListeners();
        return this.el;
    }


    getall(saved_req) {
        const that = this;
        that.progress('show');
        let req = {};

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
                action: 'eventbrite_get_events',
                reqid: that.reqid()
            };

            that.checkrequest();
        }

        jQuery.ajax({
                url: window.MEC_ADVIMP_VARS.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: req,
            })
            .done(function(data) {
                console.log("success", data);

                if (data.success == true && data.data.next == true) {
                    req['request_next'] = data.data.post_id;
                    that.getall(req);
                } else if (data.success == true && data.data.next == false) {
                    req['request_next'] = null;
                } else {
                    req['request_next'] = null;
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


    addListeners() {

        var that = this;

        this.el.btnClick.click(function(event) {

            const action = jQuery(this).attr('data-action');
            that.el.btn = jQuery(this);
            switch (action) {

                case 'getall':
                    that.getall(null);
                    break;
            }
        });


        this.el.importBySelect.change(function(event) {
            const selected = jQuery(this).val();

            if (that.el.batch.indexOf(selected) !== -1) {
                that.el.batchSection.show();
            } else {
                that.el.batchSection.hide();
            }

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
    }
}