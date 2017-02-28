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
    } else {
        getGeneralItem(id, values);
    }
}

function getWeapon(id, values) {
    var container = document.getElementById("items-placeholder");

    var title = '';
    var type = 'weapon';
    var martial = false;
    var damage = '';
    var damageType = '';
    var properties = '';
    if (typeof values['title'] !== 'undefined') {
        title = values['title'];
    }
    if (typeof values['martial'] !== 'undefined') {
        martial = values['martial'];
    }
    if (typeof values['damage'] !== 'undefined') {
        damage = values['damage'];
    }
    if (typeof values['damageType'] !== 'undefined') {
        damageType = values['damageType'];
    }
    if (typeof values['properties'] !== 'undefined') {
        properties = values['properties'];
    }

    var tr = getItemBaseFields(id, title, type);
    tr.appendChild(getCheckboxTD('item', 'martial', id, martial));
    tr.appendChild(getDamageTD(id, damage, damageType));
    tr.appendChild(getTextInputTD('item', 'properties', id, properties));
    container.appendChild(tr);
}

function getArmor(id, values) {
    var container = document.getElementById("items-placeholder");

    var title = '';
    var type = 'armor';
    var armorClass = '';
    var properties = '';
    if (typeof values['title'] !== 'undefined') {
        title = values['title'];
    }
    if (typeof values['armorClass'] !== 'undefined') {
        armorClass = values['armorClass'];
    }
    if (typeof values['properties'] !== 'undefined') {
        properties = values['properties'];
    }

    var tr = getItemBaseFields(id, title, type);
    tr.appendChild(getTextInputTD('item', 'armor_class', id, armorClass));
    tr.appendChild(getTextInputTD('item', 'properties', id, properties));
    tr.appendChild(getEmpty(id));
    container.appendChild(tr);
}

function getGeneralItem(id, values) {
    var container = document.getElementById("items-placeholder");

    var title = '';
    var type = 'general';
    var description = '';
    if (typeof values['title'] !== 'undefined') {
        title = values['title'];
    }
    if (typeof values['description'] !== 'undefined') {
        description = values['description'];
    }

    var tr = getItemBaseFields(id, title, type);
    tr.appendChild(getTextInputTD('item', 'description', id, description));
    tr.appendChild(getEmpty(id));
    tr.appendChild(getEmpty(id));
    container.appendChild(tr);
}

function getItemBaseFields(id, title, type) {
    var tr = document.createElement("tr");
    tr.setAttribute("id", id + "_tr");
    tr.appendChild(getTextInputTD('item', 'title', id, title));
    tr.appendChild(getTypeTD(id, type));
    return tr;
}

function getTypeTD(id, typeValue) {
    var type = createSelect('item', id, '_type', ["General", "Weapon", "Armor"], typeValue);
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

function getDamageTD(id, damageValue, typeValue) {
    var damage = document.createElement("input");
    damage.setAttribute("id", id + "_damage");
    damage.setAttribute("name", "item_" + id + "_damage");
    damage.setAttribute("value", damageValue);
    damage.setAttribute("style", "width: 75px");
    var type = createSelect('item', id, '_damage_type', ["Slashing", "Piercing", "Bludgeoning"], typeValue);
    var typeLabel = document.createElement("label");
    typeLabel.setAttribute("style", "white-space: nowrap;");
    typeLabel.setAttribute("for", id + "_damage");
    typeLabel.innerHTML = "Damage";
    var td = document.createElement("td");
    td.setAttribute("id", id + "_damage_td");
    td.appendChild(typeLabel);
    td.appendChild(getBR());
    td.appendChild(damage);
    td.appendChild(type);
    return td;
}

function typeChanged(id) {
    var tr = document.getElementById(id + "_tr");
    var type = document.getElementById(id + "_type").value.toLowerCase();
    removeField(document.getElementById(id + "_description_td"));
    removeField(document.getElementById(id + "_martial_td"));
    removeField(document.getElementById(id + "_damage_td"));
    removeField(document.getElementById(id + "_armor_class_td"));
    removeField(document.getElementById(id + "_properties_td"));
    removeFields(document.getElementsByClassName(id + "_empty_td"));
    switch (type) {
        case 'weapon':
            tr.appendChild(getCheckboxTD('item', 'martial', id, false));
            tr.appendChild(getDamageTD(id, '', ''));
            tr.appendChild(getTextInputTD('item', 'properties', id, ''));
            break;
        case 'armor':
            tr.appendChild(getTextInputTD('item', 'armor_class', id, ''));
            tr.appendChild(getTextInputTD('item', 'properties', id, ''));
            tr.appendChild(getEmpty(id));
            break;
        default:
            tr.appendChild(getTextInputTD('item', 'description', id, ''));
            tr.appendChild(getEmpty(id));
            tr.appendChild(getEmpty(id));
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
    str = str.replace('_', ' ');
    return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}
