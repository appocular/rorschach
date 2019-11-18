# Rorschach

[![](https://github.com/appocular/rorschach/workflows/Run%20checks%20and%20tests/badge.svg)](https://github.com/appocular/rorschach/actions)
[![](https://img.shields.io/codecov/c/github/appocular/rorschach.svg)](https://codecov.io/gh/appocular/rorschach)


The important env vars is:
`CIRCLE_SHA1`
`APPOCULAR_TOKEN`
`RORSCHACH_HISTORY`

## Usage

To use, add something along these lines to the Circle configuration:

``` shell
wget https://github.com/appocular/rorschach/releases/download/0.3.0/rorshach.phar
php rorschach.phar
```

## Environment variables

- `GITHUB_SHA` or `CIRCLE_SHA1`: Used to get the SHA of the commit
  being tested. These are automatically set by Github
  Actions/CircleCI.
- `APPOCULAR_TOKEN`: Repository token. Tells Appocular which
  repository these images belong to.
- `RORSCHACH_HISTORY`: Overrides the history from git. Primarily for
  testing, but might be useful in special cases. A newline separated
  list of commit SHAs. `git rev-list HEAD` produces the same history
  as Rorschach produces per default.


## Options

- `--webdriver`: URL of WebDriver. Overrides value in `rorschach.yml`.
- `--base-url`: Base URL of site. Overrides value in `rorschach.yml`.
- `--write-out`: Don't send images to Appocular, but write to the
  given directory. Primarily for testing.
- `--read-in`: Don't use WebDriver, but use images in the given
  directory, previously created by `--write-out`. Primarily for
  testing.
- `--base`: Override base URL of Appocular. Primarily for testing.
- `-q`: Quiet. Less output.

## Configuration file

In the configuration some options are defined as `name: value` pairs,
these are labeled as `<named hash>` in the following documentation.
The naming of things makes it possible to override defaults and remove
items. For instance, if one has a `remove` in the `defaults` section
like so:

``` yaml
  remove:
    cookiepopup: '#CybotCookiebotDialog'
    ads: '.ugly-banner'
```

It is possible to remove that for a specific checkpoint like so:

``` yaml
  remove:
    cookiepopup: ~
```

in order to make a single checkpoint that shows the cookie popup.
Nulling an item doesn't affect other items, the `ads` key in the
example will still be used.

### Toplevel configuration items

`webdriver_url: <URL>`

The address of the Selenium WebDriver instance to use. Can be
overridden from the commandline.

`base_url: <URL>`

Base URL of the site to checkpoint. All checkpoint paths are relative
to this.

`workers: <integer>`

Number of parallel workers to use. More workers means higher resource
usage, on the other hand, it speeds up the whole process as rorschach
can process other checkpoints while waiting for timeouts or loading.

`variations: <hash>`

Variations applied to all checkpoints. Currently only `browser_size`
can be specified. Care should be taken as the number of screenshots
taken will be the cartesian product of the checkpoints and variations.

`defaults: <hash>`

Defaults for all checkpoints. All checkpoint arguments except `path`
is allowed.

`checkpoints: <hash>`

The checkpoints to screenshot. Can be specified in both a short and
detailed style. Short style is simply `<name>: <path>` mappings, where
all arguments are taken from defaults. Detailed style is `<name>:
<hash>`, where the hash contains the arguments and needs to at least
have a `path` key. See [Checkpoint arguments](#checkpoint-arguments)
for details.

### Variations

`browser_size: <hash>`

Creates a set of checkpoints for each browser size. It can either be
an array of `<width>x<height>`s, or a hash of `name:
<width>x<height>`. The name is what is supplied as the `browser_size`
meta data for appocular, and defaults to `<width>x<height>` when no
name is given.

### Checkpoint arguments

`path: <string>`

The path to checkpoint, will be appended to `base_url`.

`remove: <named hash>`

Named hash of selectors of elements to completely remove before taking
the screenshot.

`browser_size: <integer>x<integer>`

Width and height of browser. Defaults to 1920x1080. It is ignored in
defaults and checkpoints if the `browser_size` variation is in use.

`wait: <number>`

Seconds to wait after loading before taking the screenshot. In most
cases Selenium should have waited for the document loaded event, but
in some cases it might be helpful to wait a set time.

`wait_script: <string>`

A JavaScript used for waiting. It is injected into the page, and
rorschach will wait until it returns true.

`stitch_delay: <number>`

Seconds to wait after moving the viewport before taking another
screenshot while stitching. This can be handy if scrolling triggers
animations that needs to settle down.

`css: <string>`

CSS to inject into the page. This can be used to deal with CSS that
interacts badly with the stitching together of screenshots or getting
a stable screenshot in general. For example:

``` yaml
    killAnimation: |
      * {
        animation: none !important;
      }
```

Will kill animations, which could otherwise cause screenshots to be
different in each run, or:

``` yaml
    stopHeader: |
      .header {
        position: static;
      }
```

To make a `position: sticky` header stay in place when stitching.



### Example `rorschach.yml`

``` yaml
webdriver_url: http://webdriver.appocular.docker:4444/wd/hub
base_url: http://reload.dk/
workers: 2
variations:
  browser_size:
    - '1200x800'
    - '375x667'
defaults:
  remove:
    cookiepopup: '#CybotCookiebotDialog'
  browser_size: 1280x800
  wait: 1
  wait_script: |
    return (Array.from(document.images).find(function (image) {
      return !image.complete
    }) === undefined)
  stitch_delay: 1
  css:
    killPulse: |
      * {
        animation: none !important;
      }
    stopHeader: |
      .header {
        position: static;
      }
checkpoints:
  # Short syntax, name: path.
  Frontpage: /
  # Remember to quote any values the YAML parser might misunderstand.
  'Om os': /om-os
  # Detailed syntax.
  'Blog page':
    path: /blog/artikler/drop-projekterne
    css:
      hideAnotherThing: |
        #magic { display: none; }
  'Skills': /kompetencer/react
```

[Test image](https://unsplash.com/photos/XYpxR9J-U54), photo by [Jason Leung](https://unsplash.com/@ninjason) on [Unsplash](https://unsplash.com/)
