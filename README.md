<p align="left"><a href="https://github.com/fruitstudios/craft-linkit" target="_blank"><img width="100" height="100" src="resources/img/linkit.svg" alt="Linkit"></a></p>

# Linkit plugin for Craft 3

One link field to rule them all...
This plugin adds a custom fieldtype which enables support for linking to email addresses, telephone numbers, URL's and Craft element types.

## New for Craft 3

*   Link to Users and specify a profile url rule
*   New social links
*   Completely rewritten for Craft 3
*   Easily extendable, create your own custom link types

### Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

### Installation

To install the plugin, follow these steps:

1.  Install with Composer via:

        composer require fruitstudios/linkit

2.  In the Control Panel, go to Settings → Plugins and click the “Install” button for Linkit.

## Configuring Linkit

Once installed, create a new field and choose the Linkit fieldtype. You'll then have the option of configuring what link type will be available to this field.

The following link types are available:

Basic

1.  Email Address
2.  Telephone Number
3.  URL

Social

4.  Twitter
5.  Facebook
6.  Instagram
7.  Linked In

Elements

8.  Entry
9.  Category
10. User
11. Asset

Each link type has additional option to allow further customisation. For example, the User link type allows you to set a default path...

<p align="left"><img width="450px" src="resources/img/user-settings.png" alt="Linkit"></a></p>

You can also customise the dropdown labels that appear on the field.

<p align="left"><img width="450px" src="resources/img/customise-labels.png" alt="Linkit"></a></p>

## Using Linkit

**Template Variables (Basic Use)**

Output the custom field to get a ready built html link...

    {{ entry.linkItField | raw }}

or in full...

    {{ entry.linkItField.link }} or {{ entry.linkItField.getLink() }}

Create a customised html link...

    {% set attributes = {
        title: 'Custom Title',
        target: '_self',
        class: 'my-class',
        "data-custom": 'custom-data-attribute'
    } %}
    {{ entry.linkItField.link(attributes) }}

**Template Variables (Advanced Use)**

Each Linkit field returns a Linkit model with the following tags...

    {{ entry.linkItField.url }} or {{ entry.linkItField.getUrl() }}
    {{ entry.linkItField.text }} or {{ entry.linkItField.getText() }}
    {{ entry.linkItField.type }}
    {{ entry.linkItField.typeHandle }}

    {{ entry.linkItField.target }}
    {{ entry.linkItField.targetString }}
    {{ entry.linkItField.linkAttributes }}

If your link is an element link you also have access to the following...

    {{ entry.linkItField.element }} or {{ entry.linkItField.getElement() }}

or via the specific element types...

    {{ entry.linkItField.entry }} or {{ entry.linkItField.getEntry() }}
    {{ entry.linkItField.asset }} or {{ entry.linkItField.getAsset() }}
    {{ entry.linkItField.category }} or {{ entry.linkItField.getCategory() }}
    {{ entry.linkItField.user }} or {{ entry.linkItField.getUser() }}

**Example Usage**

If you have created a field called 'link' with a User link type like so...

<p align="left"><img width="450px" src="resources/img/member-select.png" alt="Linkit"></a></p>

    {{ entry.link.getLink }}

would output `<a href="/profile/USERNAME">Visit Profile</a>` which is the default user path that is created in the setting and the user would be available at...

    {{ entry.link.user }}

## Custom Link Types

You can easily create your own link types,

Full documenation is coming soon, for now, take a look at the models folder. Each link type is a seperate model, just extend Link or ElementLink, depending on your needs, and everything will be set up for you.

Hook up the requirements and register custom link types in your plugin (or modules) init()...

    <?php

    ...

    use fruitstudios\linkit\Linkit;
    use fruitstudios\linkit\events\RegisterLinkTypesEvent;
    use fruitstudios\linkit\services\LinkitService;

    use developer\plugin\models\CustomType;

    ...

    public function init()
    {
        parent::init();

        Event::on(Linkit::class, LinkitService::EVENT_REGISTER_LINKIT_FIELD_TYPES, function (RegisterLinkTypesEvent $event) {
            $event->types[] = new CustomType();
        });

    }

If you think they are super useful and you agree we can look to add them to the core plugin for everyone to use.

## Roadmap

*   [ ] Craft 2 migration script
*   [ ] Add Product link type
*   [ ] Enable third party field type registration
*   [ ] Add docs for creating third party field types
*   [x] Restructure linkit field settings template (it's a bit busy we know!)
*   [ ] Improve link model docs

Note: This plugin may become a paid add-on when the Craft Plugin store becomes available.

Brought to you by [FRUIT](https://fruitstudios.co.uk)
