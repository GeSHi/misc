<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" xml:lang="en">
<head>
	<title>GeSHi Documentation</title>
	<style type="text/css">
	<!--
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
		p, ul, ol, div, blockquote, dt {
			font-size: 80%;
			line-height: 140%;
			letter-spacing: 1px;
			color: #002;
		}
		acronym {
			border-bottom: 1px dotted #303030;
			cursor: help;
		}
		blockquote {
			font-weight: bold;
		}
		pre {
			border: 1px solid #c0e6ff;
			background-color: #e0e8ef;
			color: #002;
		}
        code, tt, kbd {
			font-size: 11px;
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
		ul li {
			font-size: 12px;
		}
		ul ul li {
			font-size: 12px;
		}
		ul ul ul li {
			font-size: 12px;
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
	-->
	</style>
</head>
<body>
<h1 id="top">GeSHi Documentation</h1>
<h5 style="text-align: center;"><?php echo $version; ?></h5>
<pre>Author:          Nigel McNie, Benny Baumann
Copyright:       &copy; 2004 - 2007 Nigel McNie, 2007 - 2008 Benny Baumann
Email:           <a href="mailto:nigel@geshi.org">nigel@geshi.org</a>, <a href="mailto:BenBE@omorphia.de">BenBE@omorphia.de</a>
GeSHi Website:   <a href="http://qbnz.com/highlighter">http://qbnz.com/highlighter</a></pre>

<p>This is the documentation for <acronym>GeSHi</acronym> - Generic Syntax Highlighter.
The most modern version of this document is available on the web -
go to <a href="http://qbnz.com/highlighter/documentation.php">http://qbnz.com/highlighter/documentation.php</a> to view it.</p>

<p>Any comments, questions, confusing points? Please <a href="mailto:nigel@geshi.org">contact me</a>! I
need all the information I can get to make the use of GeSHi and
everything related to it (including this documentation) a breeze.</p>

<h2 id="contents">Contents</h2>

<?php echo $toc; ?>