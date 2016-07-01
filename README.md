Bolt Fixtures
=============

Fixture bundle for Bolt CMS 3.0 which uses the powerful
[Alice](https://github.com/nelmio/alice) expressive fixtures library.

Supports:

- [Expressive fixtures](https://github.com/nelmio/alice/blob/2.x/doc/complete-reference.md)
- [Faker](https://github.com/fzaninotto/Faker) value generation.
- Special Bolt CMS handling for:
   - Template fields
   - Taxonomy fields
   - Relation fields (multiple and single)
   - Slug fields

Installation
------------

This package requires the [nelmio/alice](https://packagist.org/packages/nelmio/alice) package.

Because this is a Bolt extension, you will need to include this package
indepently for your project before installing this package:

```bash
$ composer require nelmio/alice ^2.1
```

Now install this extension:

```bash
$ php app/nut extensions:install dtl/bolt-fixtures @dev
```

Usage
-----

You may have multiple fixture files, f.e.

```
app/
   fixtures/
       00-pages.yml
       10-events.yml
       20-foobars.yml
```

Load them with the `dtl:fixtures:load` command:
     

```bash
$ php app/nut dtl:fixtures:load app/fixtures
Purging... pages services regions events news
Loading objects... pages.yml

............................................................  60 / 130 ( 46%)
............................................................ 120 / 130 ( 92%)
..........

Loaded 130 fixtures in 4.79 seconds
```

Example
-------

```yaml
pages:
    homepage:
        title: "My Homepage" # slug will be automatically generated
        template: homepage.twig
        templatefields:
            slider_title: This is the slider
            slider_subtitle: Some subtitle
            slider:
                - 
                  filename: agriculture-cereals-field-621.jpg
                - 
                  filename: building-frame-garage-1599.jpg
                - 
                  filename: garden-gardening-grass-589.jpg

services:
    service{1..4}: # generate 4 services
        title: <sentence(10)> # sentence with max 10 words
        summary: <paragraph()>
        body: <paragraph()>

regions:
    region1:
        title: "Weymouth"
        body: <paragraph()>
        image:
            file: california-foggy-golden-gate-bridge-2771.jpg

        # taxonomy field
        direction:  [ north ]

        # we can reference other fixtures for relations
        services: [ @service* ] 

    region{2..20}:
        title: <state()>
        body: <paragraph()>
        image:
            file: agriculture-cereals-field-621.jpg

events:
    event{1..50}:
        title: <realtext()>
        date: "2016-04-12"
        body: <realtext(500)>
        regions: @region*

news:
    news{1..50}:
        title: <realtext(50)>
        body: <realtext(500)>
        image: 
            file: "food-fruit-orange-1286.jpg"

# generate ten locations
locations:
    location{1..10}:
        title: Entry <current()>
        description: <realtext()> 
```
