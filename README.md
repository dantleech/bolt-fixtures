Bolt Fixtures
=============

Fixture bundle for Bolt CMS 3.0 which uses the powerful
[Alice](https://github.com/nelmio/alice) expressive fixtures library.

Supports:

- [Expressive fixtures](https://github.com/nelmio/alice/blob/2.x/doc/complete-reference.md)
- [Faker](https://github.com/fzaninotto/Faker) value generation.
- Setting taxonomy and relation fields.

Example
-------

```yaml
# generate ten locations
locations:
    location{1..10}:
        title: Entry <current()>
        description: <realtext()> 

user:
    user{1..20}:
        firstName: <firstName()>
        lastName: <lastName()>

pages:
    news{1..10}:
        title: <realtext()>
        slug: "hello"
        body: "@location1"
        locations: [ @location1 ]
        groups: [ one, two ]
    foobar{1..10}:
        title: <realtext()>
        slug: "hello"
        body: "@location1"
```

