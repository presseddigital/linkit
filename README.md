<p align="left"><a href="https://github.com/fruitstudios/craft-linkit" target="_blank"><img width="100" height="100" src="resources/img/linkit.svg" alt="Linkit"></a></p>

# Link It plugin for Craft 3

One link field to rule them all...
This plugin adds a custom fieldtype which enables support for linking to email addresses, telephone numbers, URL's and Craft element types.


### Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

### Installation

To install the plugin, follow these steps:


1. Install with Composer via:

        composer require fruitstudios/linkit

2. In the Control Panel, go to Settings → Plugins and click the “Install” button for LinkIt.


## Configuring LinkIt

Once installed, create a new field and choose the LinkIt fieldtype. You'll then have the option of configuring what link type will be available to this field.

The following link type are available:

1. Email Address
2. Telephone Number
3. URL
4. Entry
5. Category
6. User
7. Asset

Each link type has additional option to allow further customisation. For example, the User link type allows you to set a default path...

<p align="left"><img width="450px" src="resources/img/user-settings.png" alt="Linkit"></a></p>

You can also customise the dropdown labels that appear on the field. 

<p align="left"><img width="450px" src="resources/img/customise-labels.png" alt="Linkit"></a></p>

## Using LinkIt

**Template Variables (Basic Use)**

Output the custom field to get a ready built html link...

    {{ entry.linkItField }}

or in full...

    {{ entry.linkItField.htmlLink }} or {{ entry.linkItField.getHtmlLink() }}

Create a customised html link...

    {% set attributes = {
        title: 'Custom Title',
        target: '_self',
        class: 'my-class',
        "data-custom": 'custom-data-attribute'
    } %}
    {{ entry.linkItField.htmlLink(attributes) }}


**Template Variables (Advanced Use)**

Each LinkIt field returns a LinkIt model with the following available...

    {{ entry.linkItField.type }}
    {{ entry.linkItField.target }}
    {{ entry.linkItField.url }} or {{ entry.linkItField.getUrl() }}
    {{ entry.linkItField.text }} or {{ entry.linkItField.getText() }}

If your link is an element link (asset, entry, category) you also have access to the following...

    {{ entry.linkItField.element }} or {{ entry.linkItField.getElement() }}

or via the specific element types...

    {{ entry.linkItField.entry }} or {{ entry.linkItField.getEntry() }}
    {{ entry.linkItField.asset }} or {{ entry.linkItField.getAsset() }}
    {{ entry.linkItField.category }} or {{ entry.linkItField.getCategory() }}
    
    
**Example Usage**

If you have created a field called 'link' with a User link type like so...
<p align="left"><img width="450px" src="resources/img/member-select.png" alt="Linkit"></a></p>

    {{ entry.link.user }}
    
would output `<a href="/profile/USERNAME">Visit Profile</a>` which is the default user path that is created in the settings.





Brought to you by [FRUIT](https://fruitstudios.co.uk)

