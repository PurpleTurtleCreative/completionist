# Official Documentation

This GitHub Pages-powered site was created to document Purple Turtle Creative softwares.

*Check the site footer for copyright information.*

## Local Development

To make edits to this site, you'll need to use [**Ruby 2.7.X**](https://formulae.brew.sh/formula/ruby@2.7) until [Issue #752](https://github.com/github/pages-gem/issues/752) is resolved in the `github-pages` gem.

I'm on Mac OSX, using [Homebrew](https://brew.sh/). Add the appropriate version of Ruby to your path like such:

```bash
export PATH="/usr/local/opt/ruby@2.7/bin:/usr/local/lib/ruby/gems/ruby@2.7/bin:$PATH"
```

You'll also need [Bundler](https://bundler.io/) by running `gem install bundler`.

Use the following commands to build or serve the site. Use `--verbose` if you're having issues or want more detailed output.

```bash
# Install gem dependencies
bundle
# Build only, output to /_site
bundle exec jekyll build
# Build and watch files, served at http://127.0.0.1:4000/
bundle exec jekyll serve
```

