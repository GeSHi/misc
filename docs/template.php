<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" xml:lang="en">
    <head>
        <title>GeSHi Documentation <version /></title>
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
            .geshicode pre {
                border:none;
                font-weight:bold;
            }
            .geshicode .li2 {
                background-color: #f5f5f5;
            }
            .geshicode tbody td {
                background-color:#fff;
                margin:0 5px 5px 5px;
                border:1px solid #fff;
            }
            .geshicode td.ln pre {
                background:#fff;
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
            ul ul, ol ol {
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
            h1, h2 {
                margin-top: 0;
                margin-bottom: 0;
                text-align: center;
                color: #404060;
            }
            h2 {
                text-align: left;
            }
            h3 {
                border-bottom: 1px dotted #b0b0b0;
                margin-top: 2em;
                border-top: 1px dotted #b0b0b0;
                background-color: #ddd;
                margin-bottom: 0;
            }
            h4 {
                margin-top: 1.6em;
                border-bottom: 1px dotted #c0c0c0;
                margin-bottom: 0;
            }
            h5 {
                border-bottom: 1px dotted #d0d0d0;
                margin-top: 1.2em;
                margin-bottom: 0;
            }
            h3, h4, h5 {
                color: #707070;
                font-weight: normal;
            }
            a {
                color: #7777ff;
            }
            sup a {
                text-decoration: none;
            }

            #header p {
                text-align: center;
                border-bottom: 1px dotted #d0d0d0;
            }

            #header dl {
                background-color: #e0e8ef;
                color: #002;
                padding: 5px;
            }

            <?php echo $styles; ?>

        </style>
    </head>
    <body>
    <?php echo $documentation ?>
    </body>
</html>