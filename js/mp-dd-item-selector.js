/**
 * Created by moridrin on 4-1-17.
 */

function mp_dd_add_new_item(type, id, values) {
    if (typeof values == 'undefined' || values == null) {
        values = [];
    }
    if (type == 'weapon') {
        getWeapon(id, values);
    } else if (type == 'armor') {
        getArmor(id, values);
    } else if (type == 'gear') {
        getGear(id, values);
    } else if (type == 'tool') {
        getTool(id, values);
    } else if (type == 'ammunition') {
        getAmmunition(id, values);
    } else if (type == 'mount') {
        getMount(id, values);
    } else if (type == 'magical_item') {
        getMagicalItem(id, values);
}

function getWeapon(id, values) {
    var container = document.getElementById("items-placeholder");

    var title = '';
    var type = 'weapon';
    var difficulty = 'Simple';
    var category = 'Melee';
    if (typeof values['title'] !== 'undefined') {
        title = values['title'];
    }
    if (typeof values['difficulty'] !== 'undefined') {
        difficulty = values['difficulty'];
    }
    if (typeof values['category'] !== 'undefined') {
        category = values['category'];
    }

    var tr = getBaseFields(id, title, type); //TODO
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getClass(id, ""));
    tr.appendChild(getStyle(id, ""));
    tr.appendChild(getEnd(id));

    container.appendChild(tr);
    for (var i in fields) {
        mp_ssv_add_new_field(fields[i]['field_type'], fields[i]['input_type'], fields[i]['id'], fields[i], allowTabs);
    }
}
function getHeaderField(id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");

    var fieldTitle = '';
    var fieldType = 'header';
    var classValue = '';
    var style = '';
    if (Object.keys(values).length > 0) {
        fieldTitle = values['title'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));

    container.appendChild(tr);
}
function getTextInputField(id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");

    var fieldTitle = '';
    var fieldType = 'input';
    var name = '';
    var required = false;
    var disabled = false;
    var defaultValue = '';
    var placeholder = '';
    var classValue = '';
    var style = '';
    if (Object.keys(values).length > 0) {
        fieldTitle = values['title'];
        name = values['name'];
        required = values['required'];
        disabled = values['disabled'];
        defaultValue = values['default_value'];
        placeholder = values['placeholder'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr = getTextInputFields(tr, id, name, required, disabled, defaultValue, placeholder, classValue, style);
    container.appendChild(tr);
}
function getSelectInputField(id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");

    var fieldTitle = '';
    var fieldType = 'input';
    var name = '';
    var options = '';
    var disabled = false;
    var classValue = '';
    var style = '';
    if (Object.keys(values).length > 0) {
        fieldTitle = values['title'];
        name = values['name'];
        options = values['options'];
        disabled = values['disabled'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr = getSelectInputFields(tr, id, name, options, disabled, classValue, style);
    container.appendChild(tr);
}
function getCheckboxInputField(id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");
    var fieldTitle = '';
    var fieldType = 'input';
    var name = '';
    var required = false;
    var disabled = false;
    var defaultChecked = '';
    var classValue = '';
    var style = '';
    if (Object.keys(values).length > 0) {
        fieldTitle = values['title'];
        name = values['name'];
        required = values['required'];
        disabled = values['disabled'];
        defaultChecked = values['default_checked'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr = getCheckboxInputFields(tr, id, name, required, disabled, defaultChecked, classValue, style);
    container.appendChild(tr);
}
function getImageInputField(id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");

    var fieldTitle = '';
    var fieldType = 'input';
    var name = '';
    var required = false;
    var classValue = '';
    var style = '';
    if (Object.keys(values).length > 0) {
        fieldTitle = values['title'];
        name = values['name'];
        required = values['required'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr = getImageInputFields(tr, id, name, required, classValue, style);
    container.appendChild(tr);
}
function getHiddenInputField(id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");

    var fieldTitle = '';
    var fieldType = 'input';
    var name = '';
    var defaultValue = '';
    var classValue = '';
    var style = '';
    if (Object.keys(values).length > 0) {
        fieldTitle = values['title'];
        name = values['name'];
        defaultValue = values['default_value'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr = getHiddenInputFields(tr, id, name, defaultValue, classValue, style);
    container.appendChild(tr);
}
function getCustomInputField(inputType, id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");

    var fieldTitle = '';
    var fieldType = 'input';
    var name = '';
    var required = false;
    var disabled = false;
    var defaultValue = '';
    var placeholder = '';
    var classValue = '';
    var style = '';
    if (Object.keys(values).length > 0) {
        fieldTitle = values['title'];
        name = values['name'];
        required = values['required'];
        disabled = values['disabled'];
        defaultValue = values['default_value'];
        placeholder = values['placeholder'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr = getCustomInputFields(tr, id, inputType, name, required, disabled, defaultValue, placeholder, classValue, style);
    container.appendChild(tr);
}
function getLabelField(id, values, allowTabs) {
    var container = document.getElementById("custom-fields-placeholder");

    var fieldType = 'label';
    var fieldTitle = '';
    var text = '';
    var classValue = '';
    var style = '';
    if (typeof values !== 'undefined') {
        fieldTitle = values['title'];
        text = values['text'];
        classValue = values['class'];
        style = values['style'];
    }

    var tr = getBaseFields(id, fieldTitle, fieldType, allowTabs);
    tr.appendChild(getText(id, text));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));

    container.appendChild(tr);
}

function getBaseFields(id, fieldTitle, fieldType, allowTabs) {
    var tr = document.createElement("tr");
    tr.setAttribute("id", id + "_tr");
    tr.appendChild(getStart(id));
    tr.appendChild(getid(id));
    tr.appendChild(getDraggable(id));
    tr.appendChild(getFieldTitle(id, fieldTitle));
    tr.appendChild(getFieldType(id, fieldType, allowTabs));
    return tr;
}
function getTextInputFields(tr, id, name, required, disabled, defaultValue, placeholder, classValue, style) {
    tr.appendChild(getInputType(id, 'text'));
    tr.appendChild(getName(id, name));
    tr.appendChild(getDisabled(id, disabled));
    tr.appendChild(getRequired(id, required));
    tr.appendChild(getDefaultValue(id, defaultValue));
    tr.appendChild(getPlaceholder(id, placeholder));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));
    return tr;
}
function getSelectInputFields(tr, id, name, options, disabled, classValue, style) {
    tr.appendChild(getInputType(id, 'select'));
    tr.appendChild(getName(id, name));
    tr.appendChild(getDisabled(id, disabled));
    tr.appendChild(getOptions(id, options));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));
    return tr;
}
function getCheckboxInputFields(tr, id, name, required, disabled, defaultChecked, classValue, style) {
    tr.appendChild(getInputType(id, 'checkbox'));
    tr.appendChild(getName(id, name));
    tr.appendChild(getDisabled(id, disabled));
    tr.appendChild(getRequired(id, required));
    tr.appendChild(getDefaultSelected(id, defaultChecked));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));
    return tr;
}
function getImageInputFields(tr, id, name, required, classValue, style) {
    tr.appendChild(getInputType(id, 'image'));
    tr.appendChild(getName(id, name));
    tr.appendChild(getPreview(id, required));
    tr.appendChild(getRequired(id, required));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));
    return tr;
}
function getHiddenInputFields(tr, id, name, defaultValue, classValue, style) {
    tr.appendChild(getInputType(id, 'hidden'));
    tr.appendChild(getName(id, name));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getDefaultValue(id, defaultValue));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));
    return tr;
}
function getCustomInputFields(tr, id, inputType, name, required, disabled, defaultValue, placeholder, classValue, style) {
    tr.appendChild(getInputType(id, inputType));
    tr.appendChild(getName(id, name));
    tr.appendChild(getDisabled(id, disabled));
    tr.appendChild(getRequired(id, required));
    tr.appendChild(getDefaultValue(id, defaultValue));
    tr.appendChild(getPlaceholder(id, placeholder));
    tr.appendChild(getClass(id, classValue));
    tr.appendChild(getStyle(id, style));
    tr.appendChild(getEnd(id));
    return tr;
}

function getBR() {
    var br = document.createElement("div");
    br.innerHTML = '<br/>';
    return br.childNodes[0];
}
function getEmpty(id) {
    var td = document.createElement("td");
    td.setAttribute("class", id + "_empty_td");
    return td;
}
function getStart(id, isTab) {
    var start = document.createElement("input");
    start.setAttribute("type", "hidden");
    start.setAttribute("id", id + "_start");
    start.setAttribute("name", "custom_field_" + id + "_start");
    start.setAttribute("value", "start");
    var startTD = document.createElement("td");
    if (isTab) {
        startTD.setAttribute("style", "border-left: solid;");
    }
    startTD.setAttribute("id", id + "_start_td");
    startTD.appendChild(start);
    return startTD;
}
function getid(id) {
    var idElement = document.createElement("input");
    idElement.setAttribute("type", "hidden");
    idElement.setAttribute("id", id + "_id");
    idElement.setAttribute("name", "custom_field_" + id + "_id");
    idElement.setAttribute("value", id);
    var idTD = document.createElement("td");
    idTD.setAttribute("id", id + "_id_td");
    idTD.appendChild(idElement);
    return idTD;
}
function getDraggable(id) {
    var draggableIcon = document.createElement("img");
    draggableIcon.setAttribute("src", pluginBaseURL + '/general/images/icon-menu.svg');
    draggableIcon.setAttribute("style", "padding-right: 15px; margin: 10px 0;");
    var draggableIconTD = document.createElement("td");
    draggableIconTD.setAttribute("id", id + "_draggable_td");
    draggableIconTD.setAttribute("style", "vertical-align: middle; cursor: move;");
    draggableIconTD.appendChild(draggableIcon);
    return draggableIconTD;
}
function getFieldTitle(id, value) {
    var fieldTitle = document.createElement("input");
    fieldTitle.setAttribute("id", id + "_title");
    fieldTitle.setAttribute("name", "custom_field_" + id + "_title");
    fieldTitle.setAttribute("style", "width: 100%;");
    fieldTitle.setAttribute("value", value);
    var fieldTitleLabel = document.createElement("label");
    fieldTitleLabel.setAttribute("style", "white-space: nowrap;");
    fieldTitleLabel.setAttribute("for", id + "_field_title");
    fieldTitleLabel.innerHTML = "Field Title";
    var fieldTitleTD = document.createElement("td");
    fieldTitleTD.setAttribute("id", id + "_field_title_td");
    fieldTitleTD.appendChild(fieldTitleLabel);
    fieldTitleTD.appendChild(getBR());
    fieldTitleTD.appendChild(fieldTitle);
    return fieldTitleTD;
}
function getFieldType(id, value, allowTabs) {
    var options;
    if (allowTabs) {
        options = ["Tab", "Header", "Input", "Label"];
    } else {
        options = ["Header", "Input", "Label"];
    }
    var fieldType = createSelect(id, "_field_type", options, value);
    fieldType.setAttribute("style", "width: 100%;");
    fieldType.onchange = function () {
        fieldTypeChanged(id);
    };
    var fieldTypeLabel = document.createElement("label");
    fieldTypeLabel.setAttribute("style", "white-space: nowrap;");
    fieldTypeLabel.setAttribute("for", id + "_field_type");
    fieldTypeLabel.innerHTML = "Field Type";
    var fieldTypeTD = document.createElement("td");
    fieldTypeTD.setAttribute("id", id + "_field_type_td");
    fieldTypeTD.appendChild(fieldTypeLabel);
    fieldTypeTD.appendChild(getBR());
    fieldTypeTD.appendChild(fieldType);
    return fieldTypeTD;
}
function getText(id, value) {
    var fieldTitle = document.createElement("textarea");
    fieldTitle.setAttribute("id", id + "_text");
    fieldTitle.setAttribute("name", "custom_field_" + id + "_text");
    fieldTitle.setAttribute("style", "width: 100%;");
    fieldTitle.innerHTML = value;
    var fieldTitleLabel = document.createElement("label");
    fieldTitleLabel.setAttribute("style", "white-space: nowrap;");
    fieldTitleLabel.setAttribute("for", id + "_text");
    fieldTitleLabel.innerHTML = "Text";
    var fieldTitleTD = document.createElement("td");
    fieldTitleTD.setAttribute("id", id + "_text_td");
    var colspan = "6";
    fieldTitleTD.setAttribute("colspan", colspan);
    fieldTitleTD.appendChild(fieldTitleLabel);
    fieldTitleTD.appendChild(getBR());
    fieldTitleTD.appendChild(fieldTitle);
    return fieldTitleTD;
}
function getInputType(id, value) {
    var options = ["Text", "Select", "Checkbox", "Image", "Hidden", "Custom"];
    var customValue = '';
    if (["text", "select", "checkbox", "image", "hidden", "custom"].indexOf(value) == -1) {
        customValue = value;
        value = 'custom';
    }
    var inputType = createSelect(id, "_input_type", options, value);
    if (value == 'custom') {
        inputType.setAttribute("style", "width: 48%;");
    } else {
        inputType.setAttribute("style", "width: 100%;");
    }
    inputType.onchange = function () {
        inputTypeChanged(id);
    };
    var inputTypeLabel = document.createElement("label");
    inputTypeLabel.setAttribute("style", "white-space: nowrap;");
    inputTypeLabel.setAttribute("for", id + "_input_type");
    inputTypeLabel.innerHTML = "Input Type";
    var inputTypeTD = document.createElement("td");
    inputTypeTD.setAttribute("id", id + "_input_type_td");
    inputTypeTD.appendChild(inputTypeLabel);
    inputTypeTD.appendChild(getBR());
    inputTypeTD.appendChild(inputType);
    if (value == 'custom') {
        var inputTypeCustom = document.createElement("input");
        inputTypeCustom.setAttribute("id", id + "_input_type");
        inputTypeCustom.setAttribute("name", "custom_field_" + id + "_input_type");
        inputTypeCustom.setAttribute("style", "width: 50%;");
        inputTypeCustom.setAttribute("value", customValue);
        inputTypeCustom.setAttribute("required", "required");
        inputTypeTD.appendChild(inputTypeCustom);
    }
    return inputTypeTD;
}
function getName(id, value) {
    var name = document.createElement("input");
    name.setAttribute("id", id + "_name");
    name.setAttribute("name", "custom_field_" + id + "_name");
    name.setAttribute("style", "width: 100%;");
    name.setAttribute("value", value);
    name.setAttribute("required", "required");
    var nameLabel = document.createElement("label");
    nameLabel.setAttribute("style", "white-space: nowrap;");
    nameLabel.setAttribute("for", id + "_name");
    nameLabel.innerHTML = "Name";
    var nameTD = document.createElement("td");
    nameTD.setAttribute("id", id + "_name_td");
    nameTD.appendChild(nameLabel);
    nameTD.appendChild(getBR());
    nameTD.appendChild(name);
    return nameTD;
}
function getRequired(id, value) {
    var required = document.createElement("input");
    required.setAttribute("type", "checkbox");
    required.setAttribute("id", id + "_required");
    required.setAttribute("name", "custom_field_" + id + "_required");
    required.setAttribute("value", "true");
    if (value) {
        required.setAttribute("checked", "checked");
    }
    var requiredReset = document.createElement("input");
    requiredReset.setAttribute("type", "hidden");
    requiredReset.setAttribute("id", id + "_required");
    requiredReset.setAttribute("name", "custom_field_" + id + "_required");
    requiredReset.setAttribute("value", "false");
    var requiredLabel = document.createElement("label");
    requiredLabel.setAttribute("style", "white-space: nowrap;");
    requiredLabel.setAttribute("for", id + "_required");
    requiredLabel.innerHTML = "Required";
    var requiredTD = document.createElement("td");
    requiredTD.setAttribute("id", id + "_required_td");
    requiredTD.appendChild(requiredLabel);
    requiredTD.appendChild(getBR());
    requiredTD.appendChild(requiredReset);
    requiredTD.appendChild(required);
    return requiredTD;
}
function getPreview(id, value) {
    var preview = document.createElement("input");
    preview.setAttribute("type", "checkbox");
    preview.setAttribute("id", id + "_preview");
    preview.setAttribute("name", "custom_field_" + id + "_preview");
    preview.setAttribute("value", "true");
    if (value) {
        preview.setAttribute("checked", "checked");
    }
    var previewReset = document.createElement("input");
    previewReset.setAttribute("type", "hidden");
    previewReset.setAttribute("id", id + "_preview");
    previewReset.setAttribute("name", "custom_field_" + id + "_preview");
    previewReset.setAttribute("value", "false");
    var previewLabel = document.createElement("label");
    previewLabel.setAttribute("style", "white-space: nowrap;");
    previewLabel.setAttribute("for", id + "_preview");
    previewLabel.innerHTML = "Preview";
    var previewTD = document.createElement("td");
    previewTD.setAttribute("id", id + "_preview_td");
    previewTD.appendChild(previewLabel);
    previewTD.appendChild(getBR());
    previewTD.appendChild(previewReset);
    previewTD.appendChild(preview);
    return previewTD;
}
function getDisabled(id, value) {
    var disabled = document.createElement("input");
    disabled.setAttribute("type", "checkbox");
    disabled.setAttribute("id", id + "_disabled");
    disabled.setAttribute("name", "custom_field_" + id + "_disabled");
    disabled.setAttribute("value", "true");
    if (value) {
        disabled.setAttribute("checked", "checked");
    }
    var disabledReset = document.createElement("input");
    disabledReset.setAttribute("type", "hidden");
    disabledReset.setAttribute("id", id + "_disabled");
    disabledReset.setAttribute("name", "custom_field_" + id + "_disabled");
    disabledReset.setAttribute("value", "false");
    var disabledLabel = document.createElement("label");
    disabledLabel.setAttribute("style", "white-space: nowrap;");
    disabledLabel.setAttribute("for", id + "_disabled");
    disabledLabel.innerHTML = "Disabled";
    var disabledTD = document.createElement("td");
    disabledTD.setAttribute("id", id + "_disabled_td");
    disabledTD.appendChild(disabledLabel);
    disabledTD.appendChild(getBR());
    disabledTD.appendChild(disabledReset);
    disabledTD.appendChild(disabled);
    return disabledTD;
}
function getOptions(id, value) {
    var options = document.createElement("input");
    options.setAttribute("id", id + "_options");
    options.setAttribute("name", "custom_field_" + id + "_options");
    options.setAttribute("style", "width: 100%;");
    options.setAttribute("value", value);
    options.setAttribute("required", "required");
    options.setAttribute("placeholder", "Separate with ','");
    var optionsLabel = document.createElement("label");
    optionsLabel.setAttribute("style", "white-space: nowrap;");
    optionsLabel.setAttribute("for", id + "_options");
    optionsLabel.innerHTML = "Options";
    var nameTD = document.createElement("td");
    nameTD.setAttribute("id", id + "_options_td");
    nameTD.setAttribute("colspan", "3");
    nameTD.appendChild(optionsLabel);
    nameTD.appendChild(getBR());
    nameTD.appendChild(options);
    return nameTD;
}
function getDefaultValue(id, value) {
    var defaultValue = document.createElement("input");
    var show = custom_field_fields.indexOf('default') !== -1;
    if (!show) {
        defaultValue.setAttribute("type", "hidden");
    }
    defaultValue.setAttribute("id", id + "_default_value");
    defaultValue.setAttribute("name", "custom_field_" + id + "_default_value");
    defaultValue.setAttribute("style", "width: 100%;");
    defaultValue.setAttribute("value", value);
    var defaultValueTD = document.createElement("td");
    defaultValueTD.setAttribute("id", id + "_default_value_td");
    if (show) {
        var defaultValueLabel = document.createElement("label");
        defaultValueLabel.setAttribute("style", "white-space: nowrap;");
        defaultValueLabel.setAttribute("for", id + "_default_value");
        defaultValueLabel.innerHTML = "Default Value";
        defaultValueTD.appendChild(defaultValueLabel);
        defaultValueTD.appendChild(getBR());
    }
    defaultValueTD.appendChild(defaultValue);
    return defaultValueTD;
}
function getDefaultSelected(id, value) {
    var defaultSelected = document.createElement("input");
    var show = custom_field_fields.indexOf('default') !== -1;
    if (!show) {
        defaultSelected.setAttribute("type", "hidden");
    } else {
        defaultSelected.setAttribute("type", "checkbox");
    }
    defaultSelected.setAttribute("id", id + "_default_checked");
    defaultSelected.setAttribute("name", "custom_field_" + id + "_default_checked");
    defaultSelected.setAttribute("value", "true");
    if (value) {
        defaultSelected.setAttribute("checked", "checked");
    }
    var defaultSelectedReset = document.createElement("input");
    defaultSelectedReset.setAttribute("type", "hidden");
    defaultSelectedReset.setAttribute("id", id + "_default_checked");
    defaultSelectedReset.setAttribute("name", "custom_field_" + id + "_default_checked");
    defaultSelectedReset.setAttribute("value", "false");
    var requiredTD = document.createElement("td");
    requiredTD.setAttribute("id", id + "_default_checked_td");
    if (show) {
        var requiredLabel = document.createElement("label");
        requiredLabel.setAttribute("style", "white-space: nowrap;");
        requiredLabel.setAttribute("for", id + "_default_checked");
        requiredLabel.innerHTML = "Default Selected";
        requiredTD.appendChild(requiredLabel);
        requiredTD.appendChild(getBR());
    }
    requiredTD.appendChild(defaultSelectedReset);
    requiredTD.appendChild(defaultSelected);
    return requiredTD;
}
function getPlaceholder(id, value) {
    var placeholder = document.createElement("input");
    var show = custom_field_fields.indexOf('placeholder') !== -1;
    if (!show) {
        placeholder.setAttribute("type", "hidden");
    }
    placeholder.setAttribute("id", id + "_placeholder");
    placeholder.setAttribute("name", "custom_field_" + id + "_placeholder");
    placeholder.setAttribute("style", "width: 100%;");
    placeholder.setAttribute("value", value);
    var placeholderTD = document.createElement("td");
    placeholderTD.setAttribute("id", id + "_placeholder_td");
    if (show) {
        var placeholderLabel = document.createElement("label");
        placeholderLabel.setAttribute("style", "white-space: nowrap;");
        placeholderLabel.setAttribute("for", id + "_placeholder");
        placeholderLabel.innerHTML = "Placeholder";
        placeholderTD.appendChild(placeholderLabel);
        placeholderTD.appendChild(getBR());
    }
    placeholderTD.appendChild(placeholder);
    return placeholderTD;
}
function getClass(id, value) {
    var classField = document.createElement("input");
    var show = custom_field_fields.indexOf('class') !== -1;
    if (!show) {
        classField.setAttribute("type", "hidden");
    }
    classField.setAttribute("id", id + "_class");
    classField.setAttribute("name", "custom_field_" + id + "_class");
    classField.setAttribute("style", "width: 100%;");
    classField.setAttribute("value", value);
    var classTD = document.createElement("td");
    classTD.setAttribute("id", id + "_class_td");
    if (show) {
        var classLabel = document.createElement("label");
        classLabel.setAttribute("style", "white-space: nowrap;");
        classLabel.setAttribute("for", id + "_class");
        classLabel.innerHTML = "Class";
        classTD.appendChild(classLabel);
        classTD.appendChild(getBR());
    }
    classTD.appendChild(classField);
    return classTD;
}
function getStyle(id, value) {
    var style = document.createElement("input");
    var show = custom_field_fields.indexOf('style') !== -1;
    if (!show) {
        style.setAttribute("type", "hidden");
    }
    style.setAttribute("id", id + "_style");
    style.setAttribute("name", "custom_field_" + id + "_style");
    style.setAttribute("style", "width: 100%;");
    style.setAttribute("value", value);
    var styleTD = document.createElement("td");
    styleTD.setAttribute("id", id + "_style_td");
    if (show) {
        var styleLabel = document.createElement("label");
        styleLabel.setAttribute("style", "white-space: nowrap;");
        styleLabel.setAttribute("for", id + "_style");
        styleLabel.innerHTML = "Style";
        styleTD.appendChild(styleLabel);
        styleTD.appendChild(getBR());
    }
    styleTD.appendChild(style);
    return styleTD;
}
function getEnd(id, isTab) {
    var stop = document.createElement("input");
    stop.setAttribute("type", "hidden");
    stop.setAttribute("id", id + "_end");
    stop.setAttribute("name", "custom_field_" + id + "_end");
    stop.setAttribute("value", "end");
    var stopTD = document.createElement("td");
    if (isTab) {
        stopTD.setAttribute("style", "border-right: solid;");
    }
    stopTD.setAttribute("id", id + "_end_td");
    stopTD.appendChild(stop);
    return stopTD;
}

function fieldTypeChanged(id) {
    var tr = document.getElementById(id + "_tr");
    var fieldType = document.getElementById(id + "_field_type").value;
    removeField(document.getElementById(id + "_text_td"));
    removeField(document.getElementById(id + "_input_type_td"));
    removeField(document.getElementById(id + "_name_td"));
    removeField(document.getElementById(id + "_preview_td"));
    removeField(document.getElementById(id + "_required_td"));
    removeField(document.getElementById(id + "_options_td"));
    removeField(document.getElementById(id + "_disabled_td"));
    removeField(document.getElementById(id + "_default_value_td"));
    removeField(document.getElementById(id + "_default_checked_td"));
    removeField(document.getElementById(id + "_placeholder_td"));
    removeField(document.getElementById(id + "_class_td"));
    removeField(document.getElementById(id + "_style_td"));
    removeField(document.getElementById(id + "_end_td"));
    removeFields(document.getElementsByClassName(id + "_empty_td"));
    if (fieldType == 'input') {
        tr.appendChild(getInputType(id, ""));
        tr.appendChild(getName(id, ""));
        tr.appendChild(getDisabled(id, ""));
        tr.appendChild(getRequired(id, ""));
        tr.appendChild(getDefaultValue(id, ""));
        tr.appendChild(getPlaceholder(id, ""));
        tr.appendChild(getClass(id, ""));
        tr.appendChild(getStyle(id, ""));
        tr.appendChild(getEnd(id));
    } else if (fieldType == 'label') {
        tr.appendChild(getText(id, ""));
        tr.appendChild(getClass(id, ""));
        tr.appendChild(getStyle(id, ""));
        tr.appendChild(getEnd(id));
    } else {
        tr.appendChild(getEmpty(id));
        tr.appendChild(getEmpty(id));
        tr.appendChild(getEmpty(id));
        tr.appendChild(getEmpty(id));
        tr.appendChild(getEmpty(id));
        tr.appendChild(getEmpty(id));
        tr.appendChild(getClass(id, ""));
        tr.appendChild(getStyle(id, ""));
        tr.appendChild(getEnd(id));
    }
}
function inputTypeChanged(id) {
    var tr = document.getElementById(id + "_tr");
    var inputType = document.getElementById(id + "_input_type").value;
    removeField(document.getElementById(id + "_input_type_td"));
    removeField(document.getElementById(id + "_name_td"));
    removeField(document.getElementById(id + "_preview_td"));
    removeField(document.getElementById(id + "_required_td"));
    removeField(document.getElementById(id + "_options_td"));
    removeField(document.getElementById(id + "_disabled_td"));
    removeField(document.getElementById(id + "_default_value_td"));
    removeField(document.getElementById(id + "_default_checked_td"));
    removeField(document.getElementById(id + "_placeholder_td"));
    removeField(document.getElementById(id + "_class_td"));
    removeField(document.getElementById(id + "_style_td"));
    removeField(document.getElementById(id + "_end_td"));
    removeFields(document.getElementsByClassName(id + "_empty_td"));
    if (inputType == 'text') {
        getTextInputFields(tr, id, "", "", "", "", "", "", "");
    } else if (inputType == 'select') {
        getSelectInputFields(tr, id, "", "", "", "", "");
    } else if (inputType == 'checkbox') {
        getCheckboxInputFields(tr, id, "", "", "", "", "", "")
    } else if (inputType == 'image') {
        getImageInputFields(tr, id, "", "", "", "");
    } else if (inputType == 'hidden') {
        getHiddenInputFields(tr, id, "", "", "", "");
    } else {
        getCustomInputFields(tr, id, "", "", "", "", "", "", "");
    }
}

function createSelect(id, fieldNameExtension, options, selected) {
    var select = document.createElement("select");
    select.setAttribute("id", id + fieldNameExtension);
    select.setAttribute("name", "custom_field_" + id + fieldNameExtension);

    for (var i = 0; i < options.length; i++) {
        var option = document.createElement("option");
        option.setAttribute("value", options[i].toLowerCase());
        if (options[i].toLowerCase() == selected) {
            option.setAttribute("selected", "selected");
        }
        option.innerHTML = options[i];
        select.appendChild(option);
    }

    return select;
}
function removeFields(fields) {
    if (fields !== null) {
        while (fields.length > 0) {
            removeField(fields[0]);
        }
    }
}
function removeField(field) {
    if (field !== null) {
        field.parentElement.removeChild(field);
    }
}
