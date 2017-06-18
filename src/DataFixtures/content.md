# An exhibit of Markdown

This note demonstrates some of what [Markdown][1] is capable of doing.

## Basic formatting

Paragraphs can be written like so. A paragraph is the basic block of Markdown. A paragraph is what text will turn into when there is no reason it should become anything else.

Paragraphs must be separated by a blank line. Basic formatting of *italics* and **bold** is supported. This *can be **nested** like* so.

## Lists

### Ordered list

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

1. Item 1
2. A second item
3. Number 3
4. Ⅳ

*Note: the fourth item uses the Unicode character for [Roman numeral four][2].*

### Unordered list

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

* An item
* Another item
* Yet another item
* And there's more...

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

## Paragraph modifiers

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

### Quote

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

> Here is a quote. What this is should be self explanatory. Quotes are automatically indented when they are used.

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

## Headings

There are six levels of headings. They correspond with the six levels of HTML headings. You've probably noticed them already in the page. Each level down uses one more hash character.

### Headings *can* also contain **formatting**

### They can even contain `inline code`

Of course, demonstrating what headings look like messes up the structure of the page.

I don't recommend using more than three or four levels of headings here, because, when you're smallest heading isn't too small, and you're largest heading isn't too big, and you want each size up to look noticeably larger and more important, there there are only so many sizes that you can use.

## URLs

URLs can be made in a handful of ways:

* A named link to [MarkItDown][3]. The easiest way to do these is to select what you want to make a link and hit `Ctrl+L`.
* Another named link to [MarkItDown](http://www.markitdown.net/)
* Sometimes you just want a URL like <http://www.markitdown.net/>.

## Horizontal rule

A horizontal rule is a line that goes across the middle of the page.

---

It's sometimes handy for breaking things up.

## Images

Markdown can also contain images. I'll need to add something here sometime.

<center>

<figure>
    <img src="https://pbs.twimg.com/media/DBRPMmMXcAAwdtK.jpg:large">
    <figcaption></figcaption>
</figure>

</center>

## Videos

<center>

<div class="video">
   <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEmmanuelMacron%2Fvideos%2F1973801359519107%2F&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>
</div>

</center>

## Twitter

<center>
<blockquote class="twitter-tweet" data-lang="fr"><p lang="fr" dir="ltr">À tout de suite sur TF1. <a href="https://twitter.com/hashtag/MacronPr%C3%A9sident?src=hash">#MacronPrésident</a> <a href="https://t.co/lZALwnfZ8Y">pic.twitter.com/lZALwnfZ8Y</a></p>&mdash; Emmanuel Macron (@EmmanuelMacron) <a href="https://twitter.com/EmmanuelMacron/status/854030102673444864">17 avril 2017</a></blockquote> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
</center>

## Finally

There's actually a lot more to Markdown than this. See the official [introduction][4] and [syntax][5] for more information. However, be aware that this is not using the official implementation, and this might work subtly differently in some of the little things.


  [1]: http://daringfireball.net/projects/markdown/
  [2]: http://www.fileformat.info/info/unicode/char/2163/index.htm
  [3]: http://www.markitdown.net/
  [4]: http://daringfireball.net/projects/markdown/basics
  [5]: http://daringfireball.net/projects/markdown/syntax
