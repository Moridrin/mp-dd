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
}

function getWeapon(id, values) {
    var container = document.getElementById("items-placeholder");

    var title = '';
    var type = 'weapon';
    var martial = false;
    var dice = '';
    var damageType = '';
    var properties = '';
    if (typeof values['title'] !== 'undefined') {
        title = values['title'];
    }
    if (typeof values['martial'] !== 'undefined') {
        martial = values['martial'];
    }
    if (typeof values['dice'] !== 'undefined') {
        dice = values['dice'];
    }
    if (typeof values['damage_type'] !== 'undefined') {
        damageType = values['damage_type'];
    }
    if (typeof values['properties'] !== 'undefined') {
        properties = values['properties'];
    }

    var tr = getBaseFields(id, title, type);
    tr.appendChild(getCheckboxTD('martial', id, martial));
    tr.appendChild(getDamageTD(id, dice, damageType));
    tr.appendChild(getTextInputTD('properties', id, properties));
    container.appendChild(tr);
}

function getBaseFields(id, title, type) {
    var tr = document.createElement("tr");
    tr.setAttribute("id", id + "_tr");
    tr.appendChild(getTextInputTD('title', id, title));
    tr.appendChild(getTypeTD(id, type));
    return tr;
}

function getTextInputTD(name, id, value) {
    var field = document.createElement("input");
    field.setAttribute("id", id + "_" + name);
    field.setAttribute("name", "item_" + id + "_" + name);
    field.setAttribute("style", "width: 100%;");
    field.setAttribute("value", value);
    var label = document.createElement("label");
    label.setAttribute("style", "white-space: nowrap;");
    label.setAttribute("for", id + "_" + name);
    label.innerHTML = toTitleCase(name);
    var td = document.createElement("td");
    td.setAttribute("id", id + "_" + name + "_td");
    td.appendChild(label);
    td.appendChild(getBR());
    td.appendChild(field);
    return td;
}

function getCheckboxTD(name, id, checked) {
    var field = document.createElement("input");
    field.setAttribute("id", id + "_" + name);
    field.setAttribute("type", "checkbox");
    field.setAttribute("name", "item_" + id + "_" + name);
    if (checked) {
        field.setAttribute("checked", "checked");
    }
    var label = document.createElement("label");
    label.setAttribute("style", "white-space: nowrap;");
    label.setAttribute("for", id + "_" + name);
    label.innerHTML = toTitleCase(name);
    var td = document.createElement("td");
    td.setAttribute("id", id + "_" + name + "_td");
    td.appendChild(label);
    td.appendChild(getBR());
    td.appendChild(field);
    return td;
}

function getBR() {
    var br = document.createElement("div");
    br.innerHTML = '<br/>';
    return br.childNodes[0];
}

function getEmpty(fieldID) {
    var td = document.createElement("td");
    td.setAttribute("class", fieldID + "_empty_td");
    return td;
}

function createSelect(id, extension, options, selected) {
    var select = document.createElement("select");
    select.setAttribute("id", id + extension);
    select.setAttribute("name", "item_" + id + extension);

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

function getTypeTD(id, typeValue) {
    var type = createSelect(id, '_type', ["Weapon", "Armor", "Gear", "Tool", "Ammunition", "Mount", "Magical Item"], typeValue);
    type.onchange = function () {
        typeChanged(id);
    };
    var typeLabel = document.createElement("label");
    typeLabel.setAttribute("style", "white-space: nowrap;");
    typeLabel.setAttribute("for", id + "_type");
    typeLabel.innerHTML = "Type";
    var td = document.createElement("td");
    td.setAttribute("id", id + "_type_td");
    td.appendChild(typeLabel);
    td.appendChild(getBR());
    td.appendChild(type);
    return td;
}

function getDamageTD(id, diceValue, typeValue) {
    var dice = document.createElement("input");
    dice.setAttribute("id", id + "_dice");
    dice.setAttribute("name", "item_" + id + "_dice");
    dice.setAttribute("value", diceValue);
    dice.setAttribute("style", "width: 75px");
    var type = createSelect(id, '_damage_type', ["Slashing", "Piercing", "Bludgeoning"], typeValue);
    var typeLabel = document.createElement("label");
    typeLabel.setAttribute("style", "white-space: nowrap;");
    typeLabel.setAttribute("for", id + "_dice");
    typeLabel.innerHTML = "Damage";
    var td = document.createElement("td");
    td.setAttribute("id", id + "_damage_td");
    td.appendChild(typeLabel);
    td.appendChild(getBR());
    td.appendChild(dice);
    td.appendChild(type);
    return td;
}

function typeChanged(id) {
    var tr = document.getElementById(id + "_tr");
    var type = document.getElementById(id + "_type").value.toLowerCase();
    removeField(document.getElementById(id + "_martial_td"));
    removeField(document.getElementById(id + "_damage_td"));
    removeField(document.getElementById(id + "_properties_td"));
    // removeFields(document.getElementsByClassName(id + "_empty_td"));
    switch (type) {
        case 'weapon':
            tr.appendChild(getCheckboxTD('martial', id, false));
            tr.appendChild(getDamageTD(id, '', ''));
            tr.appendChild(getTextInputTD('properties', id, ''));
            break;
        case 'armor':
            break;
        case 'gear':
            break;
        case 'tool':
            break;
        case 'ammunition':
            break;
        case 'mount':
            break;
        case 'magical_item':
            break;
    }
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

function toTitleCase(str) {
    return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}
