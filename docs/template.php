<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>GeSHi Documentation <version /></title>

    	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    	<meta name="keywords" content="GeSHi, syntax, highlighter, colorizer, beautifier, code, generic, php, sql, css, html, syntax, highlighting, documentation" />
    	<meta name="description" content="GeSHi - Generic Syntax Highlighter for PHP. Highlight many languages, including PHP, CSS, HTML, SQL, Java and C for XHTML compliant output using this easy PHP Class. Every aspect of the highlighting is customisable, from colours and other styles to case-sensitivity checking and more. GeSHi - the best syntax highlighter in the world!" />

        <style type="text/css">
            html {
                background-color: #e6e6e6;
            }
            body {
                font-family: Verdana, Arial, sans-serif;
                margin: 10px;
                border: 2px solid #d0d0d0;
                background-color: #f6f6f6;
                padding: 10px;
            }
            p, ul, ol, div, blockquote, dt, dd {
                font-size: 80%;
                line-height: 140%;
                letter-spacing: 1px;
                color: #002;
            }
            dt {
                font-weight: bold;
            }
            acronym {
                border-bottom: 1px dotted #303030;
                cursor: help;
            }
            blockquote {
                font-weight: bold;
            }
            pre, .geshicode {
                border: 1px solid #c0e6ff;
                background-color: #e0e8ef;
                color: #002;
                margin:0;
                font-size: 12px;
                width:100%;
            }
            table {
                border-collapse:collapse;
            }
            .geshicode pre {
                border:none;
                background-color:inherit;
                font-weight:bold;
            }
            .geshicode .li2 td {
                background-color:#eee;
            }
            .geshicode .li1 td {
                background-color:#fff;
            }
            .geshicode td td {
                padding:0 2px;
            }
            .geshicode td, .geshicode table {
                width: 100%;
            }
            .geshicode td.ln {
                border-right:2px solid #e0e8ef;
            }
            .geshicode .head {
                text-align:center;
                font-weight:bold;
            }
            code, tt, kbd {
                font-size: 125%;
                font-weight:normal;
            }
            hr {
                height: 0;
                border: none;
                border-top: 1px dotted #404040;
                width: 75%;
            }
            var {
                color: blue; font-style: normal; font-family: monospace;
            }
            li {
                padding-top: 2px;
            }
            ul ul, ol ol, div ul, div ol {
                font-size:100%;
            }
            .note {
                border: 1px solid yellow;
                background-color: #ffc;
                color: #220;
                padding: 5px;
                margin: 1em 0 0 .75em;
            }
            .caution {
                border: 6px double red;
                background-color: #fcc;
                color: #200;
                padding: 5px;
                margin: 1em 0 0 .75em;
            }
            .caution p:first-child, .note p:first-child {
                margin-top: 0;
            }
            .caution-header {
                border: 1px solid red;
                border-width: 1px 2px 2px 1px;
                margin-top: -1.6em;
                background-color: #fcc;
                width: 10%;
                font-weight: bold;
                text-align: center;
                color: #600;
            }
            .note-header {
                border: 1px solid #ff0;
                border-width: 1px 2px 2px 1px;
                margin-top: -1.2em;
                background-color: #ffc;
                width: 10%;
                font-weight: bold;
                text-align: center;
                color: #660;
            }
            .nav {
                font-size: 70%;
            }
            .nav a {
                color: #707070;
                border: 1px solid #a0a0a0;
                border-width: 0 1px 1px 1px;
                border-top: 1px dotted #c0c0c0;
                text-decoration: none;
                padding: 1px 2px;
                background-color: #e0e0e0;
                -moz-border-radius-bottomleft: 3px;
                -moz-border-radius-bottomright: 3px;
            }
            h1, #contents {
                margin-top: 0;
                margin-bottom: 0;
                text-align: center;
                color: #404060;
            }
            #contents {
                text-align:left;
                background:none;
                border:none;
            }
            h2 {
                border-bottom: 1px dotted #b0b0b0;
                margin-top: 2em;
                border-top: 1px dotted #b0b0b0;
                background-color: #ddd;
                margin-bottom: 0;
            }
            h3 {
                margin-top: 1.6em;
                border-bottom: 1px dotted #c0c0c0;
                margin-bottom: 0;
            }
            h4 {
                border-bottom: 1px dotted #d0d0d0;
                margin-top: 1.2em;
                margin-bottom: 0;
            }
            h2, h3, h4 {
                color: #707070;
                font-weight: normal;
            }
            a {
                color: #7777ff;
            }
            sup a {
                text-decoration: none;
            }
            abbr {
                cursor: help;
            }
            .header p {
                text-align: center;
                border-bottom: 1px dotted #d0d0d0;
            }

            .header dl {
                background-color: #e0e8ef;
                color: #002;
                padding: 5px;
            }

            .header img {
                float: right;
                margin:2.5em 1em 0 0;
            }

            <?php echo $styles; ?>

        </style>
    </head>
    <body>
    <?php echo $documentation ?>
    </body>
</html>