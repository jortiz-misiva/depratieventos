function mec_fluent_wrap_init(){

    if (jQuery(".mec-fluent-wrap").length < 0) {
        return;
    }
    jQuery(window).on("resize", monthlyCalendarUI);
    jQuery(document).on('load_calendar_data',function(){
        monthlyCalendarUI();
    });
    monthlyCalendarUI();
    jQuery(document).on(
        "click",
        ".mec-fluent-wrap .mec-more-events-icon",
        customScrollbar
    );
}
jQuery(document).ready(function () {

    mec_fluent_wrap_init();
});

function monthlyCalendarUI() {
    if (jQuery(".mec-fluent-monthly-wrap").length < 1) {
        return;
    }

    var widowWidth = jQuery(window).innerWidth();

    if (widowWidth <= 767) {
        var dts = jQuery("dt.mec-calendar-day");
        dts.each(function (index, dtElem) {
            var dt = jQuery(dtElem);

            if (dt.find(".mec-more-events-mobile").length > 0) {
                return;
            }

            var events = dt.children(".simple-skin-ended");

            if (events.length < 1) {
                return;
            }

            var eventsHTML = [];
            events.each(function (index, eventElem) {
                var event = jQuery(eventElem);
                var eventWrapper = event
                    .clone()
                    .empty()
                    .addClass("mec-more-event-copy");
                var eventTitleHTML = event.find(".mec-event-title")[0]
                    .outerHTML;
                var startTimeHTML =
                    '<span class="mec-event-start-time">' +
                    event.data("start-time") +
                    "</span>";
                var endTimeHTML =
                    '<span class="mec-event-end-time">' +
                    event.data("end-time") +
                    "</span>";
                eventWrapper.append(
                    '\n<div class="mec-more-events-content">\n'
                        .concat(
                            eventTitleHTML,
                            '\n<i class="mec-sl-clock"></i>\n'
                        )
                        .concat(startTimeHTML, "\n")
                        .concat(endTimeHTML, "\n</div>")
                );
                eventsHTML[index] = eventWrapper[0].outerHTML;
            });
            var moreEvents = dt.find(".mec-more-events-wrap");

            if (moreEvents.length < 1) {
                var day = dt.data("day");
                var month = dt.data("month");
                var year = dt.data("year");
                var date = new Date(year, month-1, day, 0, 0, 0, 0);
                var HeaderText = dateFormat(date, "fullDate");
                var moreEventsHTML = '\n<span class="mec-more-events-icon">...</span>\n<div class="mec-more-events-wrap mec-more-events-generated" style="display: none;">\n<div class="mec-more-events">\n<h5 class="mec-more-events-header">'.concat(
                    HeaderText,
                    '</h5>\n<div class="mec-more-events-body"></div>\n</div>\n</div>'
                );
                dt.append(moreEventsHTML);
            }

            eventsHTML.forEach(function (eventHTML) {
                dt.find(".mec-more-events-wrap")
                    .addClass("mec-more-events-mobile")
                    .end()
                    .find(".mec-more-events-body")
                    .prepend(eventHTML);
            });
        });
    } else {
        jQuery(".mec-more-events-generated")
            .siblings(".mec-more-events-icon")
            .remove()
            .end()
            .remove();
        jQuery(".mec-more-events-wrap.mec-more-events-mobile")
            .removeClass("mec-more-events-mobile")
            .find(".mec-more-event-copy")
            .remove();
    }
}

function customScrollbar() {
    if (jQuery(".mec-fluent-wrap").length < 0) {
        return;
    }

    if (jQuery().niceScroll) {
        var moreIcon = jQuery(this);
        if (
            !moreIcon
                .siblings(".mec-more-events-wrap")
                .find(".mec-more-events-body")
                .hasClass("mec-custom-scrollbar")
        ) {
            moreIcon
                .siblings(".mec-more-events-wrap")
                .find(".mec-more-events-body")
                .addClass("mec-custom-scrollbar")
                .niceScroll({
                    cursorcolor: "#C7EBFB",
                    cursorwidth: "4px",
                    cursorborderradius: "4px",
                    cursorborder: "none",
                    railoffset: {
                        left: -2,
                    },
                });
        }
        moreIcon
            .siblings(".mec-more-events-wrap")
            .find(".mec-more-events-body")
            .getNiceScroll()
            .onResize();
    }
}
