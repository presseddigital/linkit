Garnish.LinkitField = Garnish.Base.extend({
	defaults: {
		id: null,
		name: null
	},

	id: null,
	name: null,
	$field: null,

	$typeSelect: null,
	currentType: null,

	$settingsInputs: null,

	init: function(settings) {
		this.setSettings(settings, this.defaults);

		this.id = settings.id || null;
		this.name = settings.name || null;
		this.$field = $("#" + settings.id + "-field");

		this.$typeSelect = this.$field.find("#" + settings.id + "-type");
		this.currentType = this.$typeSelect.val();
		this.addListener(this.$typeSelect, "change", "onChangeType");

		this.$settingsInputs = this.$field.find(".linkit--settings");
	},

	onChangeType: function(e) {
		var $select = $(e.currentTarget);

		this.type = $select.val();
		if (this.type === "") {
			this.$settingsInputs.addClass("hidden");
		} else {
			this.$settingsInputs.removeClass("hidden");
		}
	}
});

var elementSelectInit = Craft.BaseElementSelectInput.prototype.init;

Craft.BaseElementSelectInput.prototype.init = function(settings) {
    elementSelectInit.apply(this, arguments);

    if (!settings.id.includes("presseddigital-linkit")) {
        return;
    }

    var siteIdField = $("#"+settings.id).parent().find("input[type=hidden][id$='ElementSiteId']");

    if (siteIdField.length < 1) {
        return
    }

    siteIdField = siteIdField[0]

    this.on("selectElements", function(e) {
        siteIdField.value = e.elements[0].siteId
    });

    this.on("removeElements", function(e) {
        siteIdField.value = ''
    });
}
