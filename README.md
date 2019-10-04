# Rorschach

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

- `--webdriver`: URL of WebDriver. Overrides `rorschach.yml`.
- `--base-url`: Base URL of site. Overrides `rorschach.yml`.
- `--write-out`: Don't send images to Appocular, but write to the
  given directory. Primarily for testing.
- `--read-in`: Don't use WebDriver, but use images in the given
  directory, previously created by `--write-out`. Primarily for
  testing.
- `--base`: Override base URL of Appocular. Primarily for testing.
- `-q`: Quiet. Less output.

## Config file

`rorschach.yml` example:

``` yaml
# URL of WebDriver.
webdriver_url: http://webdriver.appocular.docker:4444/wd/hub
# Base URL of site to snapshot.
base_url: http://reload.dk/
# Default values for steps.
defaults:
  hide:
    # CSS selectors to "display: none" before taking screenshot.
    cookiepopup: '#CybotCookiebotDialog'
  # Selfevident..
  browser_height: 800
  browser_width: 1280
  # Wait one second after loading the page before taking screenshot,
  # to allow things to load.
  wait: 1
  # Wait one second after moving the viewport before taking the
  # screenshot, when stitching full page screenshots together. This
  # allows animations to settle.
  stitch_delay: 1
# Pages to screenshot. Will be renamed "checkpoints" at some time.
steps:
  # Short syntax, name: path.
  Frontpage: /
  # Remember to quote any values the YAML parser might misunderstand.
  'Om os': /om-os
  # Detailed syntax.
  'Blog page':
    # Path is obvously required.
    path: /blog/artikler/drop-projekterne
    # These will override defaults.
    browser_height: 1334
    browser_width: 765
  'Skills': /kompetencer/react
```

[Test image](https://unsplash.com/photos/XYpxR9J-U54), photo by [Jason Leung](https://unsplash.com/@ninjason) on [Unsplash](https://unsplash.com/)
