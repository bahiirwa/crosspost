= Blog Crosspost =
Contributors: laurencebahiirwa
Donate link: https://omukiguy.com/
Tags: blog, crosspost, Rest, 
Requires at least: 4.9.0
Tested up to: 6.6.1
Requires PHP: 5.6
Stable tag: 0.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically add posts from another WordPress website using a shortcode like ```[blogcrosspost url="example.com"]```

== Description ==
Automatically add posts from another WordPress website using a shortcode.

== Usage ==
Add the shortcode ```[blogcrosspost url="example.com"]``` to desired post/page/widget and save to have the code working.

== Options ==
One can add some customization to the shortcode such as":

* Link to external website = ```[blogcrosspost url="example.com"]```
* Number of Posts to show  = ```[blogcrosspost number="3"]```
* Name for the Readme link = ```[blogcrosspost readmoretext="Learn More"]```
* Image Size to Display    = ```[blogcrosspost image_size="medium"]```. Options can be medium, large, thumbnail, full or any custom size. Default is full.

or use all of them in one go as:

```[blogcrosspost url="example.com" number="3" readmoretext="Learn More" image_size="medium"]```

You can also change the HTML structure using ```apply_filters( 'blogcrosspost_link', $html, $atts );```

== Screenshots ==
1. Adding the shortcode into WordPress Page
1. Sample Posts on front-end
1. Sample Posts on front-end

== Upcoming features ==
- [ ] Add a Gutenberg Block.

== Contribute/Issues/Feedback ==
If you have any feedback, just write an issue. Or fork the code and submit a PR [on Github](https://github.com/bahiirwa/blogcrosspost).

== Changelog ==

### 0.2.2
- Tested to WP 6.6.1.
- Fix image display using images from the rest API.
- Add an attribute for the image size to shortcode.

### 0.2.1
- Fix version issue.

** 0.2.0 **
- Fix Versioning issue.

** 0.2.0 **
- Bugfix for broken Image URL and Author Display Name.
- Added filters for all responses.
- Added class options.
- Bugfix for counter in loop display.

** 1.0.0 **
- Initial Release.
