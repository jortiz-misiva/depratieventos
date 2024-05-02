<?php
if ( ! function_exists( 'mdsb_initialize_extension' ) ):
/**
 * Creates the extension's main class instance.
 *
 * @since 1.0.0
 */
function mdsb_initialize_extension() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/MecDiviSingleBuilder.php';
}
add_action( 'mdsb_custom_require', 'mdsb_initialize_extension' );
add_action( 'divi_extensions_init', 'mdsb_initialize_extension' );
endif;

add_action('mec-divi-single-builder-editor-css', function ($styling) {
	echo '<style>

/* MEC Module */
.et-db #et-boc .et-l .et_pb_module_inner .mec-qrcode-details, .et-db #et-boc .et-l .et_pb_module_inner .mec-frontbox-title {
    text-align: center;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-button {
    background: #fff;
    padding: 12px 34px;
    font-size: 13px;
    font-weight: 400;
    letter-spacing: 0;
    border: 1px solid #e3e3e3;
    margin-right: 10px;
    transition: .3s;
    color: #40d9f1;
    border-color: #40d9f1;
}

.et-db #et-boc .et-l .et_pb_module_inner h4 {
    font-size: 24px;
    line-height: 1.2;
    margin-bottom: 10px;
    color: #171c24;
    font-weight: 300;
}

.et-db #et-boc .et-l .et_pb_module_inner h3 {
    display: inline-block !important;
    text-transform: uppercase;
    font-size: 16px;
    font-weight: 700;
    padding-bottom: 5px;
    display: inline;
    color: #000;
    padding-left: 10px;
}

.et-db #et-boc .et-l .et_pb_module_inner dd {
    font-size: 14px;
    color: #8d8d8d;
    padding-left: 34px;
    margin-bottom: 0;
}

/* Hourly Schedule */
.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content {
    border-left: 4px solid #f0f0f0;
    padding-top: 10px;
    margin-top: 30px;
    margin-left: 25px;
    margin-bottom: 20px;
    color: #8a8a8a;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content dl {
    padding-left: 24px;
    font-size: 12px !important;
    position: relative;
    margin-bottom: 35px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content dl:before {
    content: \'\';
    display: block;
    position: absolute;
    left: 0;
    top: 4px;
    width: 20px;
    height: 0;
    border-top: 4px solid #f0f0f0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content dl dt {
    margin: 0 0 10px;
    line-height: 1.16;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content dl dt.mec-schedule-title {
    font-size: 13px;
    color: #5a5a5a;
    font-weight: 700;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content dl dt.mec-schedule-description {
    display: block;
    font-weight: 300;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content .mec-schedule-speakers {
    display: block;
    background: #f7f7f7;
    padding: 10px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content h6 {
    font-size: 13px;
    color: #5a5a5a;
    font-weight: 700;
    display: inline-block;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-schedule-content a {
    font-weight: 400;
    color: #5a5a5a;
    transition: all 0.1s ease;
}

/* Breadcrumbs */
.et-db #et-boc .et-l .et_pb_module_inner .mec-breadcrumbs {
    border-radius: 2px;
    padding: 9px 15px 6px;
    font-size: 11px;
    color: #8d8d8d;
    letter-spacing: 0;
    text-transform: none;
    font-weight: 500;
    margin: 0;
    border: 1px solid #e6e6e6;
    box-shadow: 0 2px 0 0 rgba(0, 0, 0, .025);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-breadcrumbs-modern {
    margin: auto 0 33px 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-breadcrumbs a {
    color: #000;
    padding-left: 4px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-breadcrumbs a:hover {
    text-decoration: underline;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-breadcrumbs i {
    font-size: 8px !important;
    margin: 0 0 0 4px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-breadcrumbs .container {
    padding-left: 20px;
}

/* Countdown */
.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown {
    color: #c9c9c9;
    text-align: center;
    margin-bottom: 30px;
    padding: 20px 30px;
    background: #fff;
    border: 1px solid #e6e6e6;
    box-shadow: 0 2px 0 0 rgba(0, 0, 0, 0.016);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w {
    text-align: center;
    font-size: 36px;
    margin: 0 auto;
    padding: 40px 0 0;
    position: relative;
    display: table;
    table-layout: fixed;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .icon-w {
    font-size: 24px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .label-w {
    font-size: 15px;
    font-weight: 300;
    letter-spacing: 1px;
    text-transform: uppercase;
    position: relative;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .block-w {
    display: table-cell;
    margin: 0 20px 10px;
    position: relative;
    height: 70px;
    width: 190px;
    font-size: 72px;
    transition: all .3s ease-in-out;
    line-height: 1.2;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .block-w.done-w {
    border: 0 none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .block-w li {
    font-size: 50px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w span {
    padding: 24px 0 20px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .div-d {
    display: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .countdown-message {
    display: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .block-w i {
    display: none;
}

.et-db #et-boc .et-l .et_pb_module_inner #countdown {
    list-style: none;
    margin-bottom: 0;
    margin-top: 0;
    margin-left: 0;
    padding-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .mec-end-counts h3 {
    display: inherit;
    text-align: center;
    font-size: 16px;
    right: 50%;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-countdown-details .countdown-w .clockdiv li p {
    margin-top: 23px;
}

@media (min-width: 481px) and (max-width: 768px) {
    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w {
        padding: 0;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .label-w {
        font-size: 12px;
        letter-spacing: 0;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w span {
        font-size: 34px;
    }
}

@media (min-width: 320px) and (max-width: 480px) {
    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .label-w {
        font-size: 10px;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w span {
        font-size: 28px;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .mec-countdown-details .countdown-w .clockdiv li p {
        margin-top: 16px;
    }
}

@media (max-width: 320px) {
    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w .label-w {
        font-size: 9px;
        letter-spacing: 0;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown .countdown-w span {
        font-size: 22px;
    }
}

/* event countdown */
.et-db #et-boc .et-l .et_pb_module_inner .btn-wrapper {
    text-align: center;
}

.et-db #et-boc .et-l .et_pb_module_inner .countdown-wrapper .btn-wrapper {
    padding-top: 10px;
    padding-right: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .countdown-wrapper h5.countdown-message {
    letter-spacing: 5px;
    font-weight: 500;
    font-size: 18px;
}

.et-db #et-boc .et-l .et_pb_module_inner .countdown-wrapper p {
    color: #888;
}

.et-db #et-boc .et-l .et_pb_module_inner .countdown-wrapper a.button.black {
    float: right;
    margin-right: 0;
}

/* event countdown-clock */
.et-db #et-boc .et-l .et_pb_module_inner .threedaydigits .days .flip-clock-label {
    right: -100px;
}

@media only screen and (min-width: 320px) and (max-width: 767px) {
    .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul {
        width: 29px !important;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div div.inn {
        font-size: 25px !important;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-divider .flip-clock-label {
        left: 0px;
        font-weight: 300;
    }

    .et-db #et-boc .et-l .et_pb_module_inner span.flip-clock-divider {
        width: 12px;
    }
}

@media only screen and (min-width: 320px) and (max-width: 480px) {
    .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul {
        width: 29px !important;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div div.inn {
        font-size: 25px !important;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-divider .flip-clock-label {
        display: none;
    }

    .et-db #et-boc .et-l .et_pb_module_inner span.flip-clock-divider:first-child {
        width: 0px;
    }

    .et-db #et-boc .et-l .et_pb_module_inner span.flip-clock-divider {
        width: 20px;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-countdown {
        margin-left: 0;
        padding: 15px 18%;
    }
}

@media screen and (min-width: 960px) and (max-width:1200px) {
    .et-db #et-boc .et-l .et_pb_module_inner .threedaydigits ul {
        height: 50px;
        width: 47px;
    }
}

@media screen and (min-width: 480px) and (max-width:768px) {
    .et-db #et-boc .et-l .et_pb_module_inner .threedaydigits ul {
        height: 48px;
        width: 26px !important;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .threedaydigits .flip-clock-label {
        font-size: 8px;
        left: -8px;
    }
}

@media screen and (min-width: 320px) and (max-width:480px) {
    .et-db #et-boc .et-l .et_pb_module_inner .threedaydigits ul {
        height: 48px;
        width: 22px !important;
    }
}

/* reset */
.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    -ms-box-sizing: border-box;
    -o-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-backface-visibility: hidden;
    -moz-backface-visibility: hidden;
    -ms-backface-visibility: hidden;
    -o-backface-visibility: hidden;
    backface-visibility: hidden;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper a {
    cursor: pointer;
    text-decoration: none;
    color: #ccc;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper a:hover {
    color: #fff;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul {
    list-style: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper.clearfix:before, .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper.clearfix:after {
    content: " ";
    display: table;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper.clearfix:after {
    clear: both;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper.clearfix {
    *zoom: 1; }

/* main */
.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper {
    font: normal 11px "helvetica neue", "helvetica", sans-serif;
    -webkit-user-select: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-meridium {
    background: none !important;
    box-shadow: 0 0 0 !important;
    font-size: 36px !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-meridium a {
    color: #313333;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper {
    text-align: center;
    position: relative;
    display: inline-block;
    padding-bottom: 10px;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper:before, .et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper:after {
    content: " ";
    /* 1 */
    display: table;
    /* 2 */
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper:after {
    clear: both;
}

/* skeleton */
.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul {
    position: relative;
    float: left;
    margin: 2px;
    width: 50px;
    height: 50px;
    font-size: 80px;
    font-weight: bold;
    line-height: 87px;
    border-radius: 3px;
    background: rgba(0, 0, 0, 0.21);
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li {
    z-index: 1;
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    line-height: 54px;
    text-decoration: none !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li:first-child {
    z-index: 2;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a {
    display: block;
    height: 100%;
    -webkit-perspective: 200px;
    -moz-perspective: 200px;
    perspective: 200px;
    margin: 0 !important;
    overflow: visible !important;
    cursor: default !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div {
    z-index: 1;
    position: absolute;
    left: 0;
    width: 100%;
    height: 50%;
    font-size: 80px;
    overflow: hidden;
    outline: 1px solid transparent;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div .shadow {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 2;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div.up {
    -webkit-transform-origin: 50% 100%;
    -moz-transform-origin: 50% 100%;
    -ms-transform-origin: 50% 100%;
    -o-transform-origin: 50% 100%;
    transform-origin: 50% 100%;
    top: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div.up:after {
    content: "";
    position: absolute;
    top: 24px;
    left: 0;
    z-index: 5;
    width: 100%;
    height: 3px;
    background-color: rgba(0, 0, 0, 0.12);
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div.down {
    -webkit-transform-origin: 50% 0;
    -moz-transform-origin: 50% 0;
    -ms-transform-origin: 50% 0;
    -o-transform-origin: 50% 0;
    transform-origin: 50% 0;
    bottom: 0;
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div div.inn {
    position: absolute;
    left: 0;
    z-index: 1;
    width: 100%;
    height: 200%;
    color: #fff;
    text-shadow: 0 0 2px rgba(0, 0, 0, 0.25);
    text-align: center;
    background-color: #40d9f1;
    border-radius: 3px;
    font-size: 48px;
    line-height: normal;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div.up div.inn {
    top: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li a div.down div.inn {
    bottom: 0;
}

/* play */
.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-before {
    z-index: 3;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper .flip {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.17);
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-active {
    -webkit-animation: asd 0.5s 0.5s linear both;
    -moz-animation: asd 0.5s 0.5s linear both;
    animation: asd 0.5s 0.5s linear both;
    z-index: 5;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-divider {
    float: left;
    display: inline-block;
    position: relative;
    width: 18px;
    height: 62px;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-divider:first-child {
    width: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-dot {
    display: none;
    background: #323434;
    width: 10px;
    height: 10px;
    position: absolute;
    border-radius: 50%;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    left: 5px;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-divider .flip-clock-label {
    position: absolute;
    bottom: -1.5em;
    right: -71px;
    color: #101010;
    font-weight: bold;
    text-shadow: none;
    text-transform: uppercase;
}

.et-db #et-boc .et-l .et_pb_module_inner .blox.dark .flip-clock-divider .flip-clock-label {
    color: #8a8a8a;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-divider.seconds .flip-clock-label {
    right: -82px;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-dot.top {
    top: 30px;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-dot.bottom {
    bottom: 30px;
}

@-webkit-keyframes asd {
    0% {
        z-index: 2;
    }

    20% {
        z-index: 4;
    }

    100% {
        z-index: 4;
    }
}

@-moz-keyframes asd {
    0% {
        z-index: 2;
    }

    20% {
        z-index: 4;
    }

    100% {
        z-index: 4;
    }
}

@-o-keyframes asd {
    0% {
        z-index: 2;
    }

    20% {
        z-index: 4;
    }

    100% {
        z-index: 4;
    }
}

@keyframes asd {
    0% {
        z-index: 2;
    }

    20% {
        z-index: 4;
    }

    100% {
        z-index: 4;
    }
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-active .down {
    z-index: 2;
    -webkit-animation: turn 0.5s 0.5s linear both;
    -moz-animation: turn 0.5s 0.5s linear both;
    animation: turn 0.5s 0.5s linear both;
}

@-webkit-keyframes turn {
    0% {
        -webkit-transform: rotatex(90deg);
    }

    100% {
        -webkit-transform: rotatex(0deg);
    }
}

@-moz-keyframes turn {
    0% {
        -moz-transform: rotatex(90deg);
    }

    100% {
        -moz-transform: rotatex(0deg);
    }
}

@-o-keyframes turn {
    0% {
        -o-transform: rotatex(90deg);
    }

    100% {
        -o-transform: rotatex(0deg);
    }
}

@keyframes turn {
    0% {
        transform: rotatex(90deg);
    }

    100% {
        transform: rotatex(0deg);
    }
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-before .up {
    z-index: 2;
    -webkit-animation: turn2 0.5s linear both;
    -moz-animation: turn2 0.5s linear both;
    animation: turn2 0.5s linear both;
}

@-webkit-keyframes turn2 {
    0% {
        -webkit-transform: rotatex(0deg);
    }

    100% {
        -webkit-transform: rotatex(-90deg);
    }
}

@-moz-keyframes turn2 {
    0% {
        -moz-transform: rotatex(0deg);
    }

    100% {
        -moz-transform: rotatex(-90deg);
    }
}

@-o-keyframes turn2 {
    0% {
        -o-transform: rotatex(0deg);
    }

    100% {
        -o-transform: rotatex(-90deg);
    }
}

@keyframes turn2 {
    0% {
        transform: rotatex(0deg);
    }

    100% {
        transform: rotatex(-90deg);
    }
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul li.flip-clock-active {
    z-index: 3;
}

/* shadow */
.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-before .up .shadow {
    background: -moz-linear-gradient(top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(0, 0, 0, 0.1)), color-stop(100%, rgba(64, 64, 64, 0.68)));
    background: linear, top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%;
    background: -o-linear-gradient(top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%);
    background: -ms-linear-gradient(top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%);
    background: linear, to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%;
    -webkit-animation: show 0.5s linear both;
    -moz-animation: show 0.5s linear both;
    animation: show 0.5s linear both;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-active .up .shadow {
    background: -moz-linear-gradient(top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(0, 0, 0, 0.1)), color-stop(100%, rgba(64, 64, 64, 0.68)));
    background: linear, top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%;
    background: -o-linear-gradient(top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%);
    background: -ms-linear-gradient(top, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%);
    background: linear, to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(64, 64, 64, 0.68) 100%;
    -webkit-animation: hide 0.5s 0.3s linear both;
    -moz-animation: hide 0.5s 0.3s linear both;
    animation: hide 0.5s 0.3s linear both;
}

/*down*/
.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-before .down .shadow {
    background: -moz-linear-gradient(top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(64, 64, 64, 0.68)), color-stop(100%, rgba(0, 0, 0, 0.1)));
    background: linear, top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%;
    background: -o-linear-gradient(top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%);
    background: -ms-linear-gradient(top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%);
    background: linear, to bottom, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%;
    -webkit-animation: show 0.5s linear both;
    -moz-animation: show 0.5s linear both;
    animation: show 0.5s linear both;
}

.et-db #et-boc .et-l .et_pb_module_inner .flip-clock-wrapper ul.play li.flip-clock-active .down .shadow {
    background: -moz-linear-gradient(top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(64, 64, 64, 0.68)), color-stop(100%, rgba(0, 0, 0, 0.1)));
    background: linear, top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%;
    background: -o-linear-gradient(top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%);
    background: -ms-linear-gradient(top, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%);
    background: linear, to bottom, rgba(64, 64, 64, 0.68) 0%, rgba(0, 0, 0, 0.1) 100%;
    -webkit-animation: hide 0.5s 0.3s linear both;
    -moz-animation: hide 0.5s 0.3s linear both;
    animation: hide 0.5s 0.2s linear both;
}

@-webkit-keyframes show {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@-moz-keyframes show {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@-o-keyframes show {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@keyframes show {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@-webkit-keyframes hide {
    0% {
        opacity: 1;
    }

    100% {
        opacity: 0;
    }
}

@-moz-keyframes hide {
    0% {
        opacity: 1;
    }

    100% {
        opacity: 0;
    }
}

@-o-keyframes hide {
    0% {
        opacity: 1;
    }

    100% {
        opacity: 0;
    }
}

@keyframes hide {
    0% {
        opacity: 1;
    }

    100% {
        opacity: 0;
    }
}

/* Tags */
.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-tags {
    margin-top: 20px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-tags a {
    display: inline-block;
    color: #444;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 500;
    padding: 3px 7px;
    border: 1px solid #ddd;
    border-radius: 2px;
    background: #fff;
    margin: 1px 3px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-tags a:hover {
    text-decoration: underline;
    background: #f9f9f9;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-local-time-details li {
    list-style: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-local-time-details {
    background: #f7f7f7;
    padding: 12px 14px 8px;
    margin-bottom: 12px;
    vertical-align: baseline;
    position: relative;
    border: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-local-time-details ul {
    margin: 0;
    padding-left: 35px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-local-time-details h3 {
    border: none;
    padding-left: 15px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-local-time-details h3:before {
    display: none
}

.et-db #et-boc .et-l .et_pb_module_inner i.mec-sl-speedometer {
    display: none;
}

/* Booking */
.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking, .et-db #et-boc .et-l .et_pb_module_inner .mec-frontbox {
    margin-bottom: 30px;
    padding: 20px 30px;
    background: #fff;
    border: 1px solid #e6e6e6;
    box-shadow: 0 2px 0 0 rgba(0,0,0,.016);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking {
    padding-bottom: 30px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking ul {
    list-style: none;
    margin-left: 0;
    padding-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-booking .mec-book-bfixed-fields-container {
    padding-left: 15px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking ul li {
    padding: 0;
    list-style: none;
    margin-top: 40px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking h4 {
    margin-bottom: 20px;
    font-size: 23px;
    font-weight: bold;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking li h4 {
    font-size: 19px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking button {
    border-radius: 0;
    margin-bottom: 6px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking button {
    min-width: 155px;
    margin-top: 5px;
    margin-left: 10px;
    border-radius: 2px;
    box-shadow: 0 2px 1px rgba(0, 0, 0, .08);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking button.mec-book-form-back-button {
    background: #c4cace;
    float: left;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking button.mec-book-form-back-button:hover {
    background: #000;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking button.mec-book-form-next-button {
    float: left;
    margin-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-next-event-details a {
    text-align: center;
    display: block;
    background: #fff;
    padding: 6px 0;
    font-size: 11px;
    font-weight: 400;
    letter-spacing: 0;
    border: 1px solid #e3e3e3;
    transition: .3s;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-next-event-details li {
    list-style: none;
    margin-top: 20px;
}

.et-db #et-boc .et-l .et_pb_module_inner button#mec-book-form-back-btn-step-3 {
    float: none;
}

/* Booking Occurrences list */
.et-db #et-boc .et-l .et_pb_module_inner .mec-next-occ-booking, .et-db #et-boc .et-l .et_pb_module_inner .mec-next-occ-booking-p {
    padding-left: 15px;
}

/* Registration */
.et-db #et-boc .et-l .et_pb_module_inner .mec-book-username-password-wrapper {
    padding: 0 15px;
}

/* Pay Buttons Position */
.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-click-pay {
    max-width: 350px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-click-pay #mec_woo_add_to_cart_btn_r, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-click-pay button.mec-book-form-next-button {
    float: right;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-click-pay #mec_woo_add_to_cart_btn_r:hover, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-click-pay button.mec-book-form-next-button:hover {
    background: #000;
}

/* Free booking with coupon */
.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-click-next {
    float: right;
    position: relative;
    width: calc(100% - 186px);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-book-form-coupon button {
    margin-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-book-form-gateway-checkout button {
    margin-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-book-form-gateway-checkout button {
    margin-right: 20px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-book-form-price, .et-db #et-boc .et-l .et_pb_module_inner .mec-book-form-gateways, .et-db #et-boc .et-l .et_pb_module_inner form.mec-click-next, .et-db #et-boc .et-l .et_pb_module_inner .mec-book-first, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-tickets-list {
    padding-left: 0;
    padding-right: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner label.mec-fill-attendees {
    margin-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details p {
    font-weight: 300;
    margin: 20px 0 0 0;
    color: #8d8d8d
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details li {
    list-style: none;
    display: block;
    margin-top: 15px
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details li .mec-attendee-avatar {
    display: inline-block
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details li .mec-attendee-profile-link {
    display: inline-block;
    vertical-align: top;
    margin-left: 10px
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details ul {
    margin-bottom: 0
}

.et-db #et-boc .et-l .et_pb_module_inner  .mec-attendee-profile-link a {
    color: #8d8d8d;
    display: block
}

.et-db #et-boc .et-l .et_pb_module_inner  .mec-attendee-profile-link span {
    display: inline-block;
    color: #000;
    vertical-align: middle;
    cursor: pointer
}

.et-db #et-boc .et-l .et_pb_module_inner  span.mec-attendee-profile-ticket-number {
    border-radius: 50px;
    width: 20px;
    height: 20px;
    font-size: 12px;
    text-align: center;
    color: #fff;
    margin-right: 4px;
    line-height: 20px;
    background-color: #40d9f1;
}

.et-db #et-boc .et-l .et_pb_module_inner  span.mec-attendee-profile-ticket-number {
    line-height: 19px
}

.et-db #et-boc .et-l .et_pb_module_inner  .mec-attendee-profile-link span i {
    vertical-align: middle;
    font-size: 9px;
    font-weight: 700;
    margin-left: 5px
}

.et-db #et-boc .et-l .et_pb_module_inner  .mec-attendees-toggle {
    border: 1px solid #e6e6e6;
    background: #fafafa;
    padding: 15px 15px 0;
    border-radius: 3px;
    margin: 12px 0 20px 52px;
    position: relative;
    font-size: 13px;
    box-shadow: 0 3px 1px 0 rgba(0,0,0,.02)
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details .mec-attendees-toggle:after,
.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details .mec-attendees-toggle:before {
    content: \'\';
    display: block;
    position: absolute;
    left: 50px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 10px
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details .mec-attendees-toggle:after {
    top: -20px;
    border-color: transparent transparent #fafafa transparent
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details .mec-attendees-toggle:before {
    top: -21px;
    border-color: transparent transparent #e1e1e1 transparent
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details .mec-attendees-toggle .mec-attendees-item {
    padding-bottom: 15px
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendees-list-details .mec-attendee-avatar img {
    border-radius: 3px
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendee-avatar-sec {
    float: left;
    width: 50px;
    margin-right: 12px
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-attendee-profile-name-sec,
.et-db #et-boc .et-l .et_pb_module_inner .mec-attendee-profile-ticket-sec {
    float: left;
    width: calc(100% - 62px);
    margin-top: 3px
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking #mec-book-form-btn-step-1, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking #mec-book-form-back-btn-step-2, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking #mec-book-form-back-btn-step-3 {
    margin-left: 0px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-booking-form-container .col-md-12 {
    padding-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-wrap-checkout.row {
    margin: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-wrap-checkout .mec-book-form-gateways .mec-book-form-gateway-label {
    padding-left: 3px;
}

.et-db #et-boc .et-l .et_pb_module_inner p.mec-gateway-comment {
    margin-top: 20px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-event-ticket-available {
    display: block;
    margin-bottom: 20px;
    margin-top: -17px;
    font-size: 11px;
    color: #8a8a8a;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-book-price-total {
    display: inline-block;
    margin-bottom: 10px;
    font-size: 26px;
    color: #39c36e;
    font-weight: 700;
    padding: 10px 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking form {
    margin: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking label, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking h5 span {
    color: #424242;
    font-size: 12px;
    font-weight: 300;
    letter-spacing: 0;
    margin: 3px 0;
    display: block;
    clear: none;
    padding: 5px 1em 3px 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking h5 span {
    display: inline-block;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking h5 span.mec-ticket-variation-name {
    padding-right: 5px;
    text-transform: capitalize;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input::-webkit-input-placeholder {
    color: #aaa;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input:-moz-placeholder {
    color: #aaa;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=text], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=date], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=number], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=email], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=password], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=tel], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking textarea, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking select {
    display: block;
    background: #fcfcfc;
    min-height: 42px;
    min-width: 180px;
    font-size: 13px;
    border: 1px solid #e0e0e0;
    padding: 13px 10px;
    width: 330px;
    margin-bottom: 20px;
    box-shadow: inset 0px 2px 4px rgba(0, 0, 0, 0.051);
    clear: both;
}

.et-db #et-boc .et-l .et_pb_module_inner .wbmec-mandatory {
    padding-left: 5px;
    font-size: 14px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-red-notification input, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-red-notification textarea, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-red-notification select {
    border: 1px solid #ff3c3c !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-red-notification input[type="radio"], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec-red-notification input[type="checkbox"] {
    outline: 1px solid #ff3c3c !important;
}

@media only screen and (max-width: 479px) {
    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=text], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=date], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=number], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=email], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=password], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=tel], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking textarea, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking select {
        width: 100%;
    }
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=number]:focus, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=email]:focus, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=password]:focus, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=tel]:focus, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking textarea:focus, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking select:focus {
    border: 1px solid #aaa;
    color: #444;
    background: #fff;
    -moz-box-shadow: 0 0 3px rgba(0, 0, 0, .2);
    -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, .2);
    box-shadow: 0 0 3px rgba(0, 0, 0, .2);
    outline: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=checkbox], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=radio] {
    margin-right: 6px;
    margin-top: 5px;
    min-height: 20px;
    clear: none;
    margin: 0px 0 0 2px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=radio]:before {
    content: "";
    display: inline-block;
    background: #fff;
    border-radius: 18px;
    width: 18px;
    height: 18px;
    margin: -1px 0 0 -3px;
    cursor: pointer;
    border: 2px solid #e1e7ed;
    box-shadow: 0 2px 15px -3px rgba(69, 77, 89, .32);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=radio]:checked:before {
    border: 7px solid #008aff;
    background: #fff;
    box-shadow: 0 3px 16px -3px #008aff;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=radio] {
    min-height: 0;
    margin: 0;
    margin-right: 6px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=checkbox] {
    float: left;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking .mec_book_first_for_all {
    display: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking ul.mec-book-price-details {
    list-style: none;
    border: 1px solid #eee;
    padding: 0;
    overflow: hidden;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking ul.mec-book-price-details li {
    font-size: 15px;
    color: #a9a9a9;
    list-style: none;
    padding: 13px 18px;
    margin: 0;
    float: left;
    border-right: 1px solid #eee;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking ul.mec-book-price-details li:last-child {
    border-right: none;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking ul.mec-book-price-details li span.mec-book-price-detail-amount {
    font-weight: 700;
    font-size: 21px;
    color: #222;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking label.wn-checkbox-label {
    height: 14px;
    width: 14px;
    background-color: transparent;
    border: 1px solid #d4d4d4;
    position: relative;
    display: inline-block;
    -moz-transition: border-color ease 0.2s;
    -o-transition: border-color ease 0.2s;
    -webkit-transition: border-color ease 0.2s;
    transition: border-color ease 0.2s;
    cursor: pointer;
    box-shadow: 0 2px 16px -2px rgba(0, 0, 0, 0.2);
    vertical-align: middle;
    margin-right: 3px;
    margin-top: -2px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=checkbox]:checked+.wn-checkbox-label {
    border-color: #008aff;
    box-shadow: 0 2px 14px -3px #008aff;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking label.wn-checkbox-label:before, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking label.wn-checkbox-label:after {
    position: absolute;
    height: 0;
    width: 1px;
    background-color: #008aff;
    display: inline-block;
    -moz-transform-origin: left top;
    -ms-transform-origin: left top;
    -o-transform-origin: left top;
    -webkit-transform-origin: left top;
    transform-origin: left top;
    content: \'\';
    -webkit-transition: opacity ease .5;
    -moz-transition: opacity ease .5;
    transition: opacity ease .5;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking label.wn-checkbox-label:before {
    top: 8px;
    left: 7px;
    box-shadow: 0 0 0 2px #fff;
    -moz-transform: rotate(-145deg);
    -ms-transform: rotate(-145deg);
    -o-transform: rotate(-145deg);
    -webkit-transform: rotate(-145deg);
    transform: rotate(-145deg);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=checkbox]:checked+.wn-checkbox-label::before {
    height: 12px;
    -moz-animation: dothatopcheck 0.16s ease 0s forwards;
    -o-animation: dothatopcheck 0.16s ease 0s forwards;
    -webkit-animation: dothatopcheck 0.16s ease 0s forwards;
    animation: dothatopcheck 0.16s ease 0s forwards;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking label.wn-checkbox-label:after {
    top: 6px;
    left: 3px;
    -moz-transform: rotate(-45deg);
    -ms-transform: rotate(-45deg);
    -o-transform: rotate(-45deg);
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[type=checkbox]:checked+.wn-checkbox-label::after {
    -moz-animation: dothabottomcheck 0.08s ease 0s forwards;
    -o-animation: dothabottomcheck 0.08s ease 0s forwards;
    -webkit-animation: dothabottomcheck 0.08s ease 0s forwards;
    animation: dothabottomcheck 0.08s ease 0s forwards;
    height: 4px;
}

.et-db #et-boc .et-l .et_pb_module_inner a.button:after, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking button[type=submit]:after {
    display: none;
    font-family: \'simple-line-icons\';
    content: "\e098";
    margin-left: 4px;
    -webkit-animation: rotating 1.2s linear infinite;
    -moz-animation: rotating 1.2s linear infinite;
    -ms-animation: rotating 1.2s linear infinite;
    -o-animation: rotating 1.2s linear infinite;
    animation: rotating 1.2s linear infinite;
}

.et-db #et-boc .et-l .et_pb_module_inner a.button.loading:after, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking button[type=submit].loading:after {
    display: inline-block;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module {
    display: block;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting .mec-export-details ul {
    display: table;
    width: 100%;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting .mec-export-details ul li {
    display: table-cell;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting .mec-export-details ul li:last-child {
    text-align: right;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting .mec-export-details ul li a:hover {
    color: #fff;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting .mec-export-details ul {
    padding-left: 0;
    margin: 15px 5px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting {
    padding-left: 0;
    margin: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-ticket-price {
    margin-left: 10px;
    font-size: 13px;
    font-weight: 300;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-book-reg-field-checkbox label, .et-db #et-boc .et-l .et_pb_module_inner .mec-book-reg-field-radio label {
    line-height: 1.36;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-book-reg-field-checkbox input[type=checkbox], .et-db #et-boc .et-l .et_pb_module_inner .mec-book-reg-field-radio input[type=radio] {
    float: left;
    margin-right: 5px !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-ticket-available-spots .mec-event-ticket-description, .et-db #et-boc .et-l .et_pb_module_inner .mec-ticket-available-spots .mec-event-ticket-price {
    font-size: 11px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-book-ticket-container .wbmec-mandatory, .et-db #et-boc .et-l .et_pb_module_inner .mec-book-ticket-container .mec-reg-mandatory:nth-child(2) label:after, .et-db #et-boc .et-l .et_pb_module_inner .mec-book-ticket-container .mec-reg-mandatory:nth-child(3) label:after {
    content: "";
    color: red;
    width: 50px;
    height: 50px;
    font-size: 14px;
    padding-left: 5px;
}

@media only screen and (max-width: 767px) {
    .et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting .mec-export-details ul li {
        width: 100%;
        min-height: 40px;
        margin-bottom: 15px;
        text-align: center;
        float: none;
        display: block;
    }

    .et-db #et-boc .et-l .et_pb_module_inner .mec-event-export-module.mec-frontbox .mec-event-exporting .mec-export-details ul li a {
        width: 100%;
        padding-left: 0;
        padding-right: 0;
        text-align: center;
        display: block;
        font-size: 12px;
    }
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group {
    margin-bottom: 0;
}

@media only screen and (max-width: 767px) {
    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking {
        margin-bottom: 30px;
    }
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta h3, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dt {
    text-transform: uppercase;
    font-size: 16px;
    font-weight: bold;
    padding-bottom: 5px;
    display: inline;
    color: #000;
    padding-left: 10px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta h6 {
    text-transform: uppercase;
    font-size: 13px;
    padding-bottom: 5px;
    display: inline;
    color: #40d9f1;
    padding-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta .mec-events-event-categories a {
    font-size: 14px;
    color: #8d8d8d;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta .mec-location dd.author {
    color: #3c3b3b;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd {
    margin: 0;
    padding-left: 35px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd.mec-events-event-categories {
    min-height: 35px;
    line-height: 35px;
}

/*.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd.mec-events-event-categories:before { font-family: \'FontAwesome\'; color: #40d9f1;font-size: 16px; content: "\f105"; padding: 10px; padding-left: 0; }*/
.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd.mec-events-event-categories:first-of-type {
    padding-top: 5px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd.mec-events-event-categories:last-of-type {
    border-bottom: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd a {
    color: #8d8d8d;
    transition: all .20s ease;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd a i:before {
    font-size: 16px !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dd a i {
    margin-right: 8px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta dl {
    margin-bottom: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta .mec-events-event-cost {
    font-size: 18px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta .mec-events-address {
    color: #a9a9a9;
    margin-bottom: 3px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta .mec-events-meta-group-venue .author {
    margin-bottom: 0;
    color: #8d8d8d;
    font-size: 13px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-event-image {
    margin-bottom: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner h2.mec-single-event-title {
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 33px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-booking-button {
    border-bottom: none;
    letter-spacing: 0.5px;
    line-height: 48px;
    height: 76px;
    transition: all 0.5s ease;
    color: #fff;
    padding: 16px;
    display: block;
    text-align: center;
    font-size: 16px;
    border-radius: 2px;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
	background-color: #40d9f1;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-booking-button:hover {
    background-color: #101010 !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-tags a {
    display: inline-block;
    color: #444;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 500;
    padding: 3px 7px;
    border: 1px solid #ddd;
    border-radius: 2px;
    background: #fff;
    margin: 1px 3px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-tags:before {
    font-size: 24px;
    color: #303030;
    margin-right: 5px;
    content: "\f02c";
    font-family: fontawesome;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-tags {
    padding-top: 13px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-sharing {
    margin: 30px 0 10px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-street-address, .et-db #et-boc .et-l .et_pb_module_inner .mec-region.mec-events-abbr {
    font-style: normal;
    font-size: 13px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-date:before, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-time:before, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group.mec-events-meta-group-venue:before {
    color: #40d9f1
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social {
    text-align: center;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social h3 {
    text-transform: uppercase;
    font-size: 15px;
    font-weight: bold;
    padding-bottom: 5px;
    color: #313131;
    border-bottom: 4px solid #ebebeb;
    width: 100%;
    display: block;
    padding-bottom: 10px;
    position: relative
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-social-single:before {
    padding: 13px 35px;
    border-bottom: 4px solid #40d9f1;
    font-size: 6px;
    content: "";
    text-align: center;
    position: absolute;
    bottom: -4px;
    margin-left: 39px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social .event-sharing {
    margin-top: 30px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social ul {
    list-style: none;
    margin-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social li.mec-event-social-icon {
    display: inline-block;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social li.mec-event-social-icon a {
    display: inline-block;
    color: #fff;
    width: 40px;
    height: 40px;
    padding: 9px;
    font-size: 16px;
    margin-right: 5px;
    margin-bottom: 5px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.facebook {
    background: #3b5996;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.facebook:hover {
    background: #28385c;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.twitter {
    background: #00acee;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.twitter:hover {
    background: #0087bd;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.vimeo {
    background: #0dadd6;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.vimeo:hover {
    background: #0a85a3;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.dribble {
    background: #d53e68;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.dribble:hover {
    background: #bf4c78;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.youtube {
    background: #cb322c;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.youtube:hover {
    background: #992622;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.pinterest {
    background: #cb2027;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.pinterest:hover {
    background: #99181d;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.google {
    background: #c3391c;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.google:hover {
    background: #99181f;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.linkedin {
    background: #0073b2;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.linkedin:hover {
    background: #005380;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.email {
    background: #ff5d5e;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.email:hover {
    background: #CC4949;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.vk {
    background: #5b88Bd;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.vk:hover {
    background: #3d608a;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.tumblr {
    background: #34465d;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.tumblr:hover {
    background: #273649;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.telegram {
    background: #0088CC;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.telegram:hover {
    background: rgb(16, 118, 190);
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.whatsapp {
    background: #25D366;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.whatsapp:hover {
    background: #23ac55;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.flipboard {
    background: #e12828;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.flipboard:hover {
    background: #af1e1e;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.pocket {
    background: #ef4056;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.pocket:hover {
    background: #8d1717;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.reddit {
    background: #ff5700;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.reddit:hover {
    background: #c94909;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.telegram svg, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.flipboard svg {
    height: 16px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social li.mec-event-social-icon a svg {
    display: unset;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.rss {
    background: #f29a1d;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.rss:hover {
    background: #cc7400;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.instagram {
    background: #457399;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.instagram:hover {
    background: #2e4d66;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.linkedin {
    background: #457399;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.linkedin:hover {
    background: #2e4d66;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.other-social {
    background: #ff5d5e;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social a.other-social:hover {
    background: #cc4949;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-social {
    text-align: center;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-frontbox-title, .et-db #et-boc .et-l .et_pb_module_inner .mec-wrap-checkout h4, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking form>h4 {
    text-transform: uppercase;
    font-size: 15px;
    font-weight: bold;
    color: #313131;
    border-bottom: 4px solid #ebebeb;
    width: 100%;
    display: block;
    padding-bottom: 10px;
    position: relative;
    text-align: center;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-frontbox-title:before, .et-db #et-boc .et-l .et_pb_module_inner .mec-wrap-checkout h4:before, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking form>h4:before {
    padding: 1px 35px;
    border-bottom: 4px solid #40d9f1;
    font-size: 6px;
    content: "";
    text-align: center;
    position: absolute;
    bottom: -4px;
    margin-left: -35px;
    left: 50%;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[data-stripe="exp-month"], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[data-stripe="exp-year"] {
    width: 47% !important;
    margin-right: 12px;
    margin-top: 5px;
    display: inline-block !important;
}

@media(max-width: 768px) {
    .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[data-stripe="exp-month"], .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group-booking input[data-stripe="exp-year"] {
        width: 100% !important;
        margin-right: 0;
        display: block !important;
    }
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta i:before {
    font-size: 20px;
    vertical-align: middle;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-organizer i:before, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-additional-organizers i:before {
    font-size: 14px;
    vertical-align: baseline;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-day-time-slot .mec-events-content {
    float: left;
    width: 33%;
    padding: 0 15px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-day-time-slot .mec-events-event-image {
    padding-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner #mec-events-content .mec-events-abbr {
    color: #8d8d8d;
    font-size: 14px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-content {
    margin-bottom: 30px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-organizer-url a {
    word-wrap: break-word;
}

.et-db #et-boc .et-l .et_pb_module_inner #headline {
    margin: 0 0 10px;
}

.et-db #et-boc .et-l .et_pb_module_inner #headline h2 {
    padding: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group.mec-events-meta-group-gmap .mec-events-venue-map {
    margin-top: 0;
    padding: 8px;
    border: 1px solid #e5e5e5;
    border-radius: 7px;
}

.et-db #et-boc .et-l .et_pb_module_inner #mec-events-gmap-0 {
    height: 325px !important;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-day-time-slot .mec-events-event-meta {
    width: 33%;
    float: left;
    padding: 40px;
    height: auto;
    margin: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-day-time-slot .mec-events-content.description.entry-summary {
    font-size: 15px;
    font-weight: 300;
    color: #8d8d8d;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-day-time-slot .type-mec_events h2 {
    font-size: 28px;
    padding-bottom: 20px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-day .mec-events-day-time-slot .type-mec_events {
    margin: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-day .mec-events-day-time-slot h5 {
    background-color: #8d8d8d;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta .mec-single-event-organizer .mec-events-single-section-title, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-meta .mec-single-event-additional-organizers .mec-events-single-section-title, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-date h3 {
    padding-left: 0;
    margin: 10px;
    display: inline-block;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-date h3 {
    width: 100%;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-event-image {
    border: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-venue-map {
    padding: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-date, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-time, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-location, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-category, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-label, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-organizer, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-additional-organizers, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-date, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-cost, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-website, .et-db #et-boc .et-l .et_pb_module_inner .mec-event-more-info {
    background: #f7f7f7;
    padding: 12px 14px 8px;
    margin-bottom: 12px;
    vertical-align: baseline;
    position: relative;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-organizer dd, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-additional-organizers dd, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-date dd {
    padding-left: 0;
    margin-bottom: 10px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-organizer dd span, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-additional-organizers dd span, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-date dd span {
    display: block;
    padding-left: 12px;
    color: #8d8d8d;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-organizer i, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-additional-organizers i, .et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-date i {
    margin-right: 10px;
    margin-left: 12px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-events-meta-group.mec-events-meta-group-venue dl {
    margin-bottom: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner address.mec-events-address {
    line-height: 19px;
    font-style: normal;
    font-size: 12px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-event-content dt {
    margin-top: 5px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-additional-organizers .mec-single-event-additional-organizer {
    margin-bottom: 15px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e4e4e4;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-additional-organizers .mec-single-event-additional-organizer:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border: none;
}

/* Speaker Widget */
.et-db #et-boc .et-l .et_pb_module_inner .mec-speakers-details ul {
    padding: 0;
    margin-left: 0;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-speakers-details ul li {
    list-style: none;
    background: #f7f7f7;
    padding: 5px 5px 18px 5px;
    margin-top: 14px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-speakers-details ul li a {
    -webkit-transition: .2s all ease;
    transition: .2s all ease;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-speakers-details ul li .mec-speaker-avatar a img {
    float: left;
    border-radius: 50%;
    transition: .2s all ease;
    border: 2px solid transparent;
    width: 68px;
    height: 68px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-speakers-details ul li .mec-speaker-avatar a:hover img {
    border-color: #40d9f1
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-speakers-details ul li .mec-speaker-name {
    display: inline-block;
    margin-top: 10px;
    font-size: 15px;
    line-height: 1.8;
    text-transform: capitalize;
    font-weight: 700;
    padding-left: 8px;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-speakers-details ul li .mec-speaker-job-title {
    display: block;
    font-size: 12px;
    margin-top: -1px;
    padding-left: 75px;
    color: #888;
}

/* Location */
.et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-location img, .et-db #et-boc .et-l .et_pb_module_inner .mec-single-event-organizer img {
    margin-bottom: 10px;
    width: 100%;
}

.et-db #et-boc .et-l .et_pb_module_inner .mec-qrcode-details {
    text-align: center;
}
	</style>';
});