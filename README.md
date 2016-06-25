Bolt Fixtures
=============

Simple fixture loader for bolt.

- Load content fixutres from YAML files.
- Reference properties of other fixtures.

Example
-------

```yaml
pages: # the contenttype
    homepage: # reference for this fixture

        # scalar values automatically assumed to be values.
        title: "The Awesome Homepage"
        body: |
            This is the body of the awesome homepage.

        # references must be an array with type "reference"
        place:
            type: reference
            contenttype: places
            reference: place1
            property: id # use the Symfony PropertyAccessor to access any field on the referenced content.

places:
    place1:
        title: "Space"
        body: "Space is the place"
```

