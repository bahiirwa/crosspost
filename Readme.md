# Blog Crosspost
Automatically add posts from another WordPress website using a shortcode.

# Usage
Add the shortcode ```[blogcrosspost url="example.com"]``` to desired post/page/widget and save to have the code working.

## Options
One can add some customization to the shortcode such as":

* Link to external website = ```[blogcrosspost url="example.com"]```
* Number of Posts to show  = ```[blogcrosspost postnumber="3"]```
* Name for the Readme link = ```[blogcrosspost readmoretext="Learn More"]```

or use all of them in one go as:

```[blogcrosspost url="example.com" postnumber="3" readmoretext="Learn More"]```

You can also change the HTML structure using ```apply_filters( 'blogcrosspost_link', $html, $atts );```

## Screenshots
![Adding the shortcode into WordPress Page](screnshot-1.png)
![Sample Posts on front-end](screnshot-2.png)
![Sample Posts on front-end](screnshot-3.png)

## Upcoming features
- [ ] Add an admin subpage under settings page to customize the usage of the options.
- [ ] Add a customizable widget in widgets area.
- [ ] Add a Gutenberg Block.

## Contribute/Issues/Feedback
If you have any feedback, just write an issue. Or fork the code and submit a PR [on Github](https://github.com/bahiirwa/blogcrosspost).

## Changelog

### 0.1.0
- Initial Release.
