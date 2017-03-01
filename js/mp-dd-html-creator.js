/**
 * Created by moridrin on 28-2-17.
 */

function getTextInputTD(group, name, id, value) {
    var field = document.createElement("input");
    field.setAttribute("id", id + "_" + name);
    field.setAttribute("name", group + "_" + id + "_" + name);
    field.setAttribute("style", "width: 100%;");
    field.setAttribute("value", value);
    var label = document.createElement("label");
    label.setAttribute("style", "white-space: nowrap;");
    label.setAttribute("for", id + "_" + name);
    label.innerHTML = toTitleCase(name);
    var td = document.createElement("td");
    td.setAttribute("style", "width: 200px;");
    td.setAttribute("id", id + "_" + name + "_td");
    td.appendChild(label);
    td.appendChild(getBR());
    td.appendChild(field);
    return td;
}

function getTextListItemTD(group, id, value) {
    var field = document.createElement("input");
    field.setAttribute("id", group + "_" + id);
    field.setAttribute("name", group + "_" + id);
    field.setAttribute("style", "width: 100%;");
    field.setAttribute("value", value);
    var td = document.createElement("td");
    td.setAttribute("style", "width: 200px;");
    td.setAttribute("id", group + "_" + id + "_td");
    td.appendChild(field);
    return td;
}

function getTextAreaTD(group, name, id, value) {
    var field = document.createElement("textarea");
    field.setAttribute("id", id + "_" + name);
    field.setAttribute("name", group + "_" + id + "_" + name);
    field.setAttribute("style", "width: 100%;");
    field.value = value;
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

function getCheckboxTD(group, name, id, checked) {
    var field = document.createElement("input");
    field.setAttribute("id", id + "_" + name);
    field.setAttribute("type", "checkbox");
    field.setAttribute("name", group + "_" + id + "_" + name);
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

function getEmpty(id) {
    var td = document.createElement("td");
    td.setAttribute("class", id + "_empty_td");
    return td;
}

function createSelect(group, id, extension, options, selected) {
    var select = document.createElement("select");
    select.setAttribute("id", id + extension);
    select.setAttribute("name", group + "_" + id + extension);

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
