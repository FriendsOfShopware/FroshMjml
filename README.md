# FroshMjml

MJML email templates for Shopware 6 MJML in a mail's HTML body is compiled to
responsive HTML at send time — pure PHP (`shyim/mjml-php`), no Node required.

## Requirements

- Shopware ~6.6.0
- PHP 8.2+

## Installation

```bash
composer require frosh/mjml
bin/console plugin:refresh
bin/console plugin:install --activate FroshMjml
```

## Usage

**Per mail template:** open a mail template, toggle *Use MJML*, and write MJML in
the editor. The default HTML stays untouched, so the toggle is reversible.

**Reusable components** (Settings → MJML Components) are referenced from Twig:

- `{{ mjml_component('name', { headline: '…' }) }}` — inline fragment
- `{% mjml 'name' %}…{% endmjml %}` — fragment with a `{{ content }}` slot
- `{% extends mjml_base('name') %}{% block content %}…{% endblock %}` — base layout

Content inside a block or slot must be valid MJML (`<mj-text>…</mj-text>` etc.);
raw text in an `<mj-column>` is dropped by MJML.
