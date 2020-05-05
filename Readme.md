# Crosspost
Automatically add posts from another WordPress website using a shortcode.

# Usage
Add the shortcode ```[crosspost url="example.com"]``` to desired post/page/widget and save to have the code working.

## Options
One can add some customization to the shortcode such as":

* Link to external website = ```[crosspost url="example.com"]```
* Number of Posts to show  = ```[crosspost postnumber="3"]```
* Name for the Readme link = ```[crosspost readmoretext="Learn More"]```

or use all of them in one go as:

```[crosspost url="example.com" postnumber="3" readmoretext="Learn More"]```

You can also change the HTML structure using ```apply_filters( 'crosspost_link', $html, $atts );```

## Screenshots
![Adding the shortcode into WordPress Page](screnshot-1.png)
![Sample Posts on front-end](screnshot-2.png)
![Sample Posts on front-end](screnshot-3.png)

## Upcoming features
- [ ] Add an admin subpage under settings page to customize the usage of the options.
- [ ] Add a customizable widget in widgets area.
- [ ] Add a Gutenberg Block.

## Contribute/Issues/Feedback
If you have any feedback, just write an issue. Or fork the code and submit a PR [on Github](https://github.com/bahiirwa/crosspost).

## Changelog

### 0.1.0
- Initial Release.
