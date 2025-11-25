# An exhibit of Markdown

This note demonstrates some of what [Markdown][1] is capable of doing.

## Basic formatting

Paragraphs can be written like so. A paragraph is the basic block of Markdown. A paragraph is what text will turn into when there is no reason it should become anything else.

Paragraphs must be separated by a blank line. Basic formatting of _italics_ and **bold** is supported. This _can be **nested** like_ so.

## Lists

### Ordered list

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

1. Item 1
2. A second item
3. Number 3
4. Ⅳ

_Note: the fourth item uses the Unicode character for [Roman numeral four][2]._

### Unordered list

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

- An item
- Another item
- Yet another item
- And there's more...

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

## Paragraph modifiers

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

### Quote

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

> Here is a quote. What this is should be self explanatory. Quotes are automatically indented when they are used.

Lorem ipsum dolor sit amet, consectetur adipisicing elit.

## Headings

There are six levels of headings. They correspond with the six levels of HTML headings. You've probably noticed them already in the page. Each level down uses one more hash character.

### Headings _can_ also contain **formatting**

### They can even contain `inline code`

Of course, demonstrating what headings look like messes up the structure of the page.

I don't recommend using more than three or four levels of headings here, because, when you're smallest heading isn't too small, and you're largest heading isn't too big, and you want each size up to look noticeably larger and more important, there there are only so many sizes that you can use.

## URLs

URLs can be made in a handful of ways:

- A named link to [MarkItDown][3]. The easiest way to do these is to select what you want to make a link and hit `Ctrl+L`.
- Another named link to [MarkItDown](http://www.markitdown.net/)
- Sometimes you just want a URL like <http://www.markitdown.net/>.

## Horizontal rule

A horizontal rule is a line that goes across the middle of the page.

---

It's sometimes handy for breaking things up.

## Image

Markdown can also contain images. I'll need to add something here sometime.

<figure class="image">
    <img src="https://pbs.twimg.com/media/DBRPMmMXcAAwdtK.jpg:large">
    <figcaption></figcaption>
</figure>

## Tweet simple

<figure class="tweet">
<blockquote class="twitter-tweet" data-lang="en"><p lang="fr" dir="ltr">Nos engagements nationaux sont considérables. D&#39;ici 2025, nous engagerons 2% de notre PIB en matière de défense.</p>&mdash; Emmanuel Macron (@EmmanuelMacron) <a href="https://twitter.com/EmmanuelMacron/status/877938286077001729">June 22, 2017</a></blockquote>
</figure>

## Tweet vidéo

<figure class="tweet">
<blockquote class="twitter-tweet" data-lang="en"><p lang="fr" dir="ltr">La patrie de l’innovation, de la recherche, du futur, ce sera la France. <a href="https://twitter.com/hashtag/MacronAngers?src=hash">#MacronAngers</a> <a href="https://t.co/ygRINqQrMb">pic.twitter.com/ygRINqQrMb</a></p>&mdash; Emmanuel Macron (@EmmanuelMacron) <a href="https://twitter.com/EmmanuelMacron/status/836661517990920193">February 28, 2017</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
</figure>

## Tweet image

<figure class="tweet">
<blockquote class="twitter-tweet" data-lang="en"><p lang="fr" dir="ltr">Le Conseil européen a traité de nombreux sujets avec une conviction : l&#39;Europe est notre meilleure protection pour faire face à nos défis. <a href="https://t.co/XEkvlDTHOP">pic.twitter.com/XEkvlDTHOP</a></p>&mdash; Emmanuel Macron (@EmmanuelMacron) <a href="https://twitter.com/EmmanuelMacron/status/878258345043308544">June 23, 2017</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
</figure>

## Facebook post

<figure class="facebook">
<iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2FEmmanuelMacron%2Fposts%2F1986040634961846&width=500" width="500" height="504" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
</figure>

## Facebook vidéo

<figure class="facebook">
<iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEmmanuelMacron%2Fvideos%2F1973801359519107%2F&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>
</figure>

## Youtube

<figure class="youtube">
  <iframe width="560" height="315" src="https://www.youtube.com/embed/6L_abJPsfmQ" frameborder="0" allowfullscreen></iframe>
</figure>

## Finally

There's actually a lot more to Markdown than this. See the official [introduction][4] and [syntax][5] for more information. However, be aware that this is not using the official implementation, and this might work subtly differently in some of the little things.

[1]: http://daringfireball.net/projects/markdown/
[2]: http://www.fileformat.info/info/unicode/char/2163/index.htm
[3]: http://www.markitdown.net/
[4]: http://daringfireball.net/projects/markdown/basics
[5]: http://daringfireball.net/projects/markdown/syntax
