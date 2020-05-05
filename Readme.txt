= Crosspost =
Contributors: laurencebahiirwa
Donate link: https://omukiguy.com/
Tags: Crosspost, Rest, 
Requires at least: 4.9.0
Tested up to: 5.4
Requires PHP: 5.6
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically add posts from another WordPress website using a shortcode like ```[crosspost url="example.com"]```

== Description ==
Automatically add posts from another WordPress website using a shortcode.

== Usage ==
Add the shortcode ```[crosspost url="example.com"]``` to desired post/page/widget and save to have the code working.

== Options ==
One can add some customization to the shortcode such as":

* Link to external website = ```[crosspost url="example.com"]```
* Number of Posts to show  = ```[crosspost postnumber="3"]```
* Name for the Readme link = ```[crosspost readmoretext="Learn More"]```

or use all of them in one go as:

```[crosspost url="example.com" postnumber="3" readmoretext="Learn More"]```

You can also change the HTML structure using ```apply_filters( 'crosspost_link', $html, $atts );```

== Screenshots ==
1. Adding the shortcode into WordPress Page
1. Sample Posts on front-end
1. Sample Posts on front-end

== Upcoming features ==
- [ ] Add an admin subpage under settings page to customize the usage of the options.
- [ ] Add a customizable widget in widgets area.
- [ ] Add a Gutenberg Block.

== Contribute/Issues/Feedback ==
If you have any feedback, just write an issue. Or fork the code and submit a PR [on Github](https://github.com/bahiirwa/crosspost).

== Changelog ==

** 1.0.0 **
- Initial Release.
